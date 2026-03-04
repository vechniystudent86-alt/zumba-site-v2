<?php
/**
 * Обработчик форм заявки
 * Версия: 2.1.0 (с исправлениями безопасности)
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
 * Экранирование спецсимволов для Telegram MarkdownV2
 */
function escapeMarkdownV2(string $text): string {
    return preg_replace('/([_*\[\]()~>#+\-=|{}.!\\\\])/', '\\\\$1', $text);
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
 * @param int $limit Максимум запросов
 * @param int $window Период в секундах
 * @return array [allowed: bool, remaining: int, reset: int]
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
 * Отправка запроса через cURL с обработкой ошибок
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
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS
        ]);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            throw new Exception("cURL error ($errno): $error");
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        return [
            'success' => true,
            'http_code' => $httpCode,
            'response' => $response
        ];
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
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Метод не разрешён']);
        exit;
    }
    
    // Проверка CSRF токена
    $csrfToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Неверный CSRF токен. Обновите страницу и попробуйте снова.']);
        exit;
    }
    
    // Проверка rate limiting
    $rateLimit = checkRateLimit(5, 300); // 5 запросов за 5 минут
    if (!$rateLimit['allowed']) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Слишком много запросов. Попробуйте через ' . ceil($rateLimit['reset'] / 60) . ' мин.',
            'retry_after' => $rateLimit['reset']
        ]);
        exit;
    }
    
    // Конфигурация
    $crmUrl = file_exists(__DIR__ . '/config/crm_url.txt') 
        ? trim(file_get_contents(__DIR__ . '/config/crm_url.txt')) 
        : 'http://localhost:8000/api/leads';
    $telegramToken = file_exists(__DIR__ . '/config/telegram_token.txt') 
        ? trim(file_get_contents(__DIR__ . '/config/telegram_token.txt')) 
        : '';
    $telegramChatId = file_exists(__DIR__ . '/config/telegram_chat_id.txt') 
        ? trim(file_get_contents(__DIR__ . '/config/telegram_chat_id.txt')) 
        : '';

    // Получаем и валидируем данные формы
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $program = trim($_POST['program'] ?? 'classic');
    $message = trim($_POST['message'] ?? '');

    // Валидация имени
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        throw new Exception('Введите корректное имя (2-100 символов)');
    }
    
    // Валидация имени: только буквы, пробелы, дефисы
    if (!preg_match('/^[\p{Cyrillic}\p{Latin}\s\-]+$/u', $name)) {
        throw new Exception('Имя должно содержать только буквы');
    }

    // Валидация телефона
    $phoneClean = preg_replace('/[^\d+]/', '', $phone);
    // Поддержка форматов: +7XXXXXXXXXX, 7XXXXXXXXXX, 8XXXXXXXXXX
    if (!preg_match('/^(\+7|7|8)\d{10}$/', $phoneClean)) {
        throw new Exception('Введите корректный номер телефона (например, +79991234567)');
    }
    
    // Нормализация телефона к +7
    if (strpos($phoneClean, '8') === 0) {
        $phoneClean = '+7' . substr($phoneClean, 1);
    } elseif (strpos($phoneClean, '7') === 0 && strpos($phoneClean, '+') !== 0) {
        $phoneClean = '+' . $phoneClean;
    }

    // Валидация программы
    if (!in_array($program, ['classic', 'gold'])) {
        throw new Exception('Неверная программа тренировок');
    }

    // Валидация сообщения (если есть)
    if ($message !== '' && mb_strlen($message) > 500) {
        throw new Exception('Сообщение слишком длинное (макс. 500 символов)');
    }

    // Данные для CRM
    $crmData = [
        'name' => $name,
        'phone' => $phoneClean,
        'program' => $program,
        'message' => $message,
        'source' => 'website',
        'timestamp' => date('c')
    ];

    // Отправка в CRM
    $crmResult = curlRequest($crmUrl, $crmData, 10);
    
    if (!$crmResult['success']) {
        error_log("CRM cURL Error: " . $crmResult['response'] ?? 'No response');
        throw new Exception('Ошибка соединения с сервером. Попробуйте позже.');
    }
    
    if ($crmResult['http_code'] !== 201 && $crmResult['http_code'] !== 200) {
        error_log("CRM Error: HTTP {$crmResult['http_code']} - Response: {$crmResult['response']}");
        throw new Exception('Ошибка сохранения заявки. Попробуйте позвонить нам.');
    }

    $crmDecoded = json_decode($crmResult['response'], true);
    if (!$crmDecoded || isset($crmDecoded['detail'])) {
        error_log("CRM Response Error: " . json_encode($crmDecoded));
        throw new Exception('Ошибка обработки заявки');
    }

    // Отправка уведомления в Telegram
    if ($telegramToken && $telegramChatId) {
        $programName = $program === 'classic' ? 'Zumba Classic' : 'Zumba Gold';
        
        $tgMessage = sprintf(
            "🔔 *Новая заявка с сайта!*\n\n" .
            "👤 *Имя:* %s\n" .
            "📱 *Телефон:* %s\n" .
            "💃 *Программа:* %s\n" .
            "%s\n" .
            "🌐 https://zumba-spb.ru",
            escapeMarkdownV2($name),
            escapeMarkdownV2($phoneClean),
            escapeMarkdownV2($programName),
            $message ? "💬 *Сообщение:* " . escapeMarkdownV2($message) . "\n" : ""
        );

        $tgUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
        $tgData = [
            'chat_id' => $telegramChatId,
            'text' => $tgMessage,
            'parse_mode' => 'MarkdownV2'
        ];

        $tgResult = curlRequest($tgUrl, $tgData, 5);
        
        if (!$tgResult['success']) {
            error_log("Telegram cURL Error: " . ($tgResult['response'] ?? 'No response'));
        } else {
            $tgDecoded = json_decode($tgResult['response'], true);
            if (!$tgDecoded || !($tgDecoded['ok'] ?? false)) {
                error_log("Telegram API Error: " . json_encode($tgDecoded));
            }
        }
    }

    // Логирование успешной заявки
    error_log("Form Success: name=$name, phone=$phoneClean, program=$program");

    // Ротация CSRF токена после успешной отправки
    unset($_SESSION['csrf_token']);

    // Успешный ответ
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.',
        'csrf_token' => generateCsrfToken() // Новый токен для следующей отправки
    ]);

} catch (Exception $e) {
    error_log("Form Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'csrf_token' => generateCsrfToken()
    ]);
}
