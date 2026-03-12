<?php
/**
 * Обработчик форм заявки
 * Версия: 3.0.0 (Локальное сохранение CSV + Telegram Bot с кнопками)
 */

// Заголовки безопасности
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Инициализация сессии для CSRF и rate limiting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Проверка CSRF токена
 */
function validateCsrfToken(string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Генерация CSRF токена
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Rate limiting: проверка лимита запросов
 */
function checkRateLimit(int $limit = 5, int $window = 300): array {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'rate_limit_' . md5($ip);
    
    $now = time();
    $requests = $_SESSION[$key] ?? [];
    
    // Удаляем старые запросы за пределами окна
    $requests = array_filter($requests, fn($time) => $now - $time < $window);
    
    $allowed = count($requests) < $limit;
    $remaining = max(0, $limit - count($requests));
    $reset = $requests ? min($requests) + $window - $now : $window;
    
    if ($allowed) {
        $requests[] = $now;
        $_SESSION[$key] = array_values($requests);
    }
    
    return [
        'allowed' => $allowed,
        'remaining' => $remaining,
        'reset' => max(0, $reset)
    ];
}

/**
 * Отправка запроса через cURL
 */
function curlRequest(string $url, array $data, int $timeout = 10): array {
    $ch = curl_init($url);
    
    try {
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            return ['success' => false, 'error' => $error];
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return ['success' => true, 'http_code' => $httpCode, 'response' => $response];
    } finally {
        curl_close($ch);
    }
}

// ============================================================================
// ОСНОВНАЯ ЛОГИКА
// ============================================================================

try {
    // Обработка OPTIONS запроса для CSRF токена
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Max-Age: 86400');
        http_response_code(204);
        echo json_encode(['csrf_token' => generateCsrfToken()]);
        exit;
    }
    
    // Проверка метода
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не разрешён');
    }
    
    // Проверка CSRF токена
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        throw new Exception('Неверный токен безопасности. Обновите страницу и попробуйте снова.');
    }
    
    // Проверка rate limiting
    $rateLimit = checkRateLimit(5, 300); // 5 запросов за 5 минут
    if (!$rateLimit['allowed']) {
        throw new Exception('Слишком много запросов. Попробуйте через ' . ceil($rateLimit['reset'] / 60) . ' мин.');
    }

    // Получаем и валидируем данные формы
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $program = trim($_POST['program'] ?? 'classic');
    $message = trim($_POST['message'] ?? '');

    // Валидация имени
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        throw new Exception('Введите корректное имя (от 2 символов)');
    }

    // Валидация телефона (оставляем только цифры и плюс)
    $phoneClean = preg_replace('/[^\d+]/', '', $phone);
    if (!preg_match('/^(\+7|7|8)\d{10}$/', $phoneClean)) {
        throw new Exception('Введите корректный номер телефона');
    }
    
    // Нормализация телефона к +7 для ссылок WhatsApp/Telegram
    $phoneNormalized = $phoneClean;
    if (strpos($phoneNormalized, '8') === 0) {
        $phoneNormalized = '+7' . substr($phoneNormalized, 1);
    } elseif (strpos($phoneNormalized, '7') === 0) {
        $phoneNormalized = '+' . $phoneNormalized;
    }
    $phoneForLink = str_replace('+', '', $phoneNormalized); // 79991234567

    // Валидация программы
    if (!in_array($program, ['classic', 'gold'])) {
        $program = 'classic';
    }
    $programName = $program === 'classic' ? '💃 Zumba Classic' : '🌟 Zumba Gold';

    // 1. СОХРАНЕНИЕ В ЛОКАЛЬНЫЙ CSV ФАЙЛ (РЕЗЕРВНАЯ КОПИЯ)
    $csvFile = __DIR__ . '/data/leads.csv';
    $isNewFile = !file_exists($csvFile);
    
    $fp = fopen($csvFile, 'a');
    if ($fp) {
        // Добавляем BOM для правильного отображения кириллицы в Excel
        if ($isNewFile) {
            fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($fp, ['Дата', 'Имя', 'Телефон', 'Программа', 'Сообщение'], ';');
        }
        $date = date('Y-m-d H:i:s');
        fputcsv($fp, [$date, $name, $phoneNormalized, $programName, $message], ';');
        fclose($fp);
    } else {
        error_log("Не удалось открыть файл для записи: $csvFile");
    }

    // 2. ОТПРАВКА В TELEGRAM
    $telegramToken = file_exists(__DIR__ . '/config/telegram_token.txt') 
        ? trim(preg_replace('/\s+/', '', file_get_contents(__DIR__ . '/config/telegram_token.txt')))
        : '';
    $telegramChatId = file_exists(__DIR__ . '/config/telegram_chat_id.txt') 
        ? trim(preg_replace('/\s+/', '', file_get_contents(__DIR__ . '/config/telegram_chat_id.txt')))
        : '';

    if (!empty($telegramToken) && !empty($telegramChatId)) {
        
        // Формируем текст сообщения (HTML разметка)
        $tgText = "🔥 <b>Новая заявка на тренировку!</b>\n\n";
        $tgText .= "👤 <b>Имя:</b> " . htmlspecialchars($name) . "\n";
        $tgText .= "📱 <b>Телефон:</b> " . $phoneNormalized . "\n";
        $tgText .= "🎯 <b>Программа:</b> " . $programName . "\n";
        if (!empty($message)) {
            $tgText .= "💬 <b>Комментарий:</b>\n<i>" . htmlspecialchars($message) . "</i>\n";
        }
        
        // Формируем Inline-кнопки
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🟢 WhatsApp', 'url' => "https://wa.me/{$phoneForLink}"],
                    ['text' => '📞 Позвонить', 'url' => "tel:{$phoneNormalized}"]
                ]
            ]
        ];

        $tgData = [
            'chat_id' => $telegramChatId,
            'text' => $tgText,
            'parse_mode' => 'HTML',
            'reply_markup' => $keyboard
        ];

        $tgUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
        
        // Отправляем запрос, но не прерываем скрипт, если Telegram недоступен
        // (заявка уже сохранена в CSV)
        curlRequest($tgUrl, $tgData, 3); 
    }

    // Ротация CSRF токена после успешной отправки
    unset($_SESSION['csrf_token']);

    // Успешный ответ клиенту
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена!',
        'csrf_token' => generateCsrfToken()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'csrf_token' => generateCsrfToken()
    ]);
}
