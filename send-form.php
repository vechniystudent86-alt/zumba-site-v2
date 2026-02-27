<?php
/**
 * Обработчик форм заявки на тренировку
 * Отправляет данные в Telegram бот
 *
 * @author Zumba SPb
 * @version 2.0
 */

// Включаем логирование ошибок (ошибки не отображаются, но записываются в лог)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/form_errors.log');

// Устанавливаем заголовки
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Разрешаем только POST запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешён']);
    exit;
}

// Конфигурация (в продакшене использовать .env файл!)
// Для быстрой настройки токены можно указать здесь, но лучше вынести в config.php
$telegramTokenFile = __DIR__ . '/config/telegram_token.txt';
$telegramChatIdFile = __DIR__ . '/config/telegram_chat_id.txt';

if (file_exists($telegramTokenFile)) {
    define('TELEGRAM_BOT_TOKEN', trim(file_get_contents($telegramTokenFile)));
} else {
    // Резервный вариант (удалить после создания config файлов!)
    define('TELEGRAM_BOT_TOKEN', '8544616219:AAFiKfXks86HrqVbvuk77NvSARnBLlrHmaQ');
}

if (file_exists($telegramChatIdFile)) {
    define('TELEGRAM_CHAT_ID', trim(file_get_contents($telegramChatIdFile)));
} else {
    // Резервный вариант (удалить после создания config файлов!)
    define('TELEGRAM_CHAT_ID', '293171586');
}

define('SITE_URL', 'https://zumba-spb.ru');

// Rate limiting - защита от спама
class RateLimiter {
    private $file = '/tmp/form_rate_limit.txt';
    private $maxRequests = 5;
    private $timeWindow = 300; // 5 минут
    
    public function isAllowed($identifier) {
        $data = $this->readData();
        $now = time();
        
        // Очищаем старые записи
        foreach ($data as $ip => $timestamps) {
            $data[$ip] = array_filter($timestamps, fn($t) => $now - $t < $this->timeWindow);
            if (empty($data[$ip])) {
                unset($data[$ip]);
            }
        }
        
        // Проверяем лимит
        $requests = count($data[$identifier] ?? []);
        if ($requests >= $this->maxRequests) {
            $this->saveData($data);
            return false;
        }
        
        // Добавляем текущий запрос
        $data[$identifier][] = $now;
        $this->saveData($data);
        return true;
    }
    
    private function readData() {
        if (!file_exists($this->file)) {
            return [];
        }
        $content = file_get_contents($this->file);
        return json_decode($content, true) ?: [];
    }
    
    private function saveData($data) {
        file_put_contents($this->file, json_encode($data));
    }
}

// Функция экранирования для MarkdownV2
function escapeMarkdownV2($text) {
    $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($chars as $char) {
        $text = str_replace($char, '\\' . $char, $text);
    }
    return $text;
}

// Валидация телефона
function validatePhone($phone) {
    // Удаляем всё кроме цифр и +
    $clean = preg_replace('/[^\d+]/', '', $phone);
    // Строгая проверка российского номера (+7 или 8, затем 10 цифр)
    return preg_match('/^\+7\d{10}$/', $clean) || preg_match('/^8\d{10}$/', $clean);
}

// Генерация CSRF токена
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Проверка CSRF токена
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Получаем IP адрес
function getClientIP() {
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return trim($ip);
            }
        }
    }
    return '0.0.0.0';
}

// Основная логика
try {
    // Получаем идентификатор для rate limiting
    $clientIP = getClientIP();
    $rateLimiter = new RateLimiter();

    if (!$rateLimiter->isAllowed($clientIP)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'Слишком много запросов. Попробуйте позже.']);
        exit;
    }

    // CSRF защита
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        throw new Exception('Неверный CSRF токен. Обновите страницу и попробуйте снова.');
    }

    // Получаем и валидируем данные
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $program = trim($_POST['program'] ?? 'classic');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация имени
    if (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
        throw new Exception('Введите корректное имя (2-50 символов)');
    }
    
    // Валидация телефона
    if (!validatePhone($phone)) {
        throw new Exception('Введите корректный номер телефона');
    }
    
    // Валидация программы
    $validPrograms = ['classic', 'gold'];
    if (!in_array($program, $validPrograms)) {
        throw new Exception('Неверная программа тренировок');
    }

    // Проверка согласия
    $privacy = $_POST['privacy'] ?? false;
    if (!$privacy) {
        throw new Exception('Необходимо согласие на обработку персональных данных');
    }

    // Словарь программ
    $programNames = [
        'classic' => 'Zumba Classic',
        'gold' => 'Zumba Gold'
    ];
    
    // Формируем сообщение для Telegram
    $formattedMessage = sprintf(
        "🔔 *Новая заявка на тренировку!*\n\n" .
        "👤 *Имя:* %s\n" .
        "📱 *Телефон:* %s\n" .
        "💃 *Программа:* %s\n" .
        "💬 *Сообщение:* %s\n\n" .
        "🌐 Сайт: %s\n" .
        "📅 %s\n" .
        "🔗 IP: %s",
        escapeMarkdownV2($name),
        escapeMarkdownV2($phone),
        $programNames[$program],
        $message ? escapeMarkdownV2($message) : 'Нет сообщения',
        SITE_URL,
        date('d.m.Y H:i'),
        $clientIP
    );
    
    // Отправляем в Telegram
    $telegramUrl = sprintf(
        'https://api.telegram.org/bot%s/sendMessage',
        TELEGRAM_BOT_TOKEN
    );
    
    $postData = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $formattedMessage,
        'parse_mode' => 'MarkdownV2',
        'link_preview_options' => json_encode(['is_disabled' => true])
    ];
    
    $ch = curl_init($telegramUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Ошибка Telegram API: ' . ($error ?: 'Неизвестная ошибка'));
    }
    
    $result = json_decode($response, true);
    if (!$result || !($result['ok'] ?? false)) {
        throw new Exception('Ошибка при отправке в Telegram');
    }
    
    // Успех
    // Генерируем новый CSRF токен для следующего запроса (rotation)
    $newCsrfToken = generateCsrfToken();
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена',
        'csrf_token' => $newCsrfToken
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
