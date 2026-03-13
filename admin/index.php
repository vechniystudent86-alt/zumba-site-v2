<?php
/**
 * Zumba Admin Panel - Исправленная версия
 * 
 * Улучшения:
 * - Настройка session_save_path() для Docker
 * - Детальное логирование ошибок PDO
 * - Исправленная генерация CSRF токена
 * - Валидация и санитизация входных данных
 * - Логирование в файл /var/log/admin.log
 */

// ============================================================================
// 1. НАСТРОЙКА СЕССИЙ ДЛЯ DOCKER
// ============================================================================
$dockerSessionPath = '/tmp/sessions';
if (!is_dir($dockerSessionPath)) {
    @mkdir($dockerSessionPath, 0777, true);
}
session_save_path($dockerSessionPath);
session_start();

// ============================================================================
// 2. НАСТРОЙКА ЛОГИРОВАНИЯ
// ============================================================================
define('LOG_FILE', '/var/log/admin.log');
define('LOG_FILE_ALT', __DIR__ . '/../logs/admin.log');

/**
 * Функция логирования с поддержкой разных уровней
 * @param string $level Уровень лога (INFO, WARNING, ERROR, DEBUG)
 * @param string $message Сообщение
 * @param array $context Дополнительный контекст
 */
function logMessage(string $level, string $message, array $context = []): void {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $logLine = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
    
    // Пробуем записать в основной лог
    $logPath = LOG_FILE;
    if (!is_writable(dirname($logPath))) {
        // Альтернативный путь в директории проекта
        $logPath = LOG_FILE_ALT;
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
    }
    
    @file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
    
    // Также логируем в error_log PHP для отладки
    error_log("[Zumba Admin] [$level] $message");
}

// ============================================================================
// КОНФИГУРАЦИЯ
// ============================================================================
$passFile = __DIR__ . '/../config/admin_pass.txt';
$contentFile = __DIR__ . '/../data/content.json';

// Инициализация логирования при старте
logMessage('INFO', 'Admin panel accessed', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);

// ============================================================================
// 3. ИСПРАВЛЕННАЯ ГЕНЕРАЦИЯ CSRF ТОКЕНА
// ============================================================================
/**
 * Получает или создает CSRF токен для сессии
 * Токен генерируется только один раз за сессию
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        logMessage('DEBUG', 'CSRF token generated for session');
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверяет валидность CSRF токена
 * @param string $token Токен для проверки
 * @return bool
 */
function validateCsrfToken(string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================================================
// 4. ФУНКЦИИ ВАЛИДАЦИИ И САНИТИЗАЦИИ
// ============================================================================
/**
 * Санитизация строковых данных
 * @param mixed $data Входные данные
 * @return string Очищенная строка
 */
function sanitizeString($data): string {
    if (!is_scalar($data)) {
        return '';
    }
    $data = trim((string)$data);
    // Удаляем потенциально опасные теги, но оставляем базовые символы
    $data = strip_tags($data);
    // Экранируем HTML спецсимволы для безопасного отображения
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Санитизация для сохранения в JSON (без HTML экранирования)
 * @param mixed $data Входные данные
 * @return string Очищенная строка
 */
function sanitizeForStorage($data): string {
    if (!is_scalar($data)) {
        return '';
    }
    $data = trim((string)$data);
    // Удаляем только теги, сохраняя спецсимволы для JSON
    return strip_tags($data);
}

/**
 * Валидация телефонного номера
 * @param string $phone Номер для валидации
 * @return bool
 */
function validatePhone(string $phone): bool {
    // Удаляем все кроме цифр и +
    $clean = preg_replace('/[^\d+]/', '', $phone);
    // Проверяем минимальную длину (с учетом + и кода страны)
    return strlen($clean) >= 10;
}

/**
 * Валидация дня недели
 * @param string $day День недели
 * @return bool
 */
function validateDayOfWeek(string $day): bool {
    $validDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс', 
                  'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье',
                  'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    return in_array($day, $validDays, true);
}

/**
 * Валидация времени (формат ЧЧ:ММ)
 * @param string $timeStr Строка времени
 * @return string|null Валидное время или null
 */
function validateTime(string $timeStr): ?string {
    if (preg_match('/(\d{1,2}):(\d{2})/', $timeStr, $matches)) {
        $hours = isset($matches[1]) ? (int)$matches[1] : 0;
        $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
        if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
            return sprintf('%02d:%02d:00', $hours, $minutes);
        }
    }
    return null;
}

// ============================================================================
// АВТОРИЗАЦИЯ
// ============================================================================
$adminPass = file_exists($passFile) ? trim(file_get_contents($passFile)) : 'zumba2024';

if (isset($_GET['logout'])) {
    logMessage('INFO', 'Admin logout', ['session_id' => session_id()]);
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $attemptedPass = trim($_POST['password']);
    logMessage('INFO', 'Login attempt', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    
    if ($attemptedPass === $adminPass) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        logMessage('INFO', 'Login successful', ['session_id' => session_id()]);
        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный пароль!";
        logMessage('WARNING', 'Login failed - wrong password', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    }
}

// Проверка сессии
if (empty($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход в панель управления</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 320px; }
            h2 { margin-top: 0; text-align: center; color: #333; }
            input[type="password"] { width: 100%; padding: 10px; margin: 10px 0 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
            button { width: 100%; padding: 10px; background-color: #FF2D75; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
            button:hover { background-color: #e61e5f; }
            .error { color: red; text-align: center; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Вход</h2>
            <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Пароль" required autofocus>
                <button type="submit">Войти</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================================
// ОБРАБОТКА СОХРАНЕНИЯ
// ============================================================================
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    logMessage('INFO', 'Action requested', ['action' => $_POST['action']]);
    
    // Валидация CSRF
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert error">Ошибка безопасности (CSRF).</div>';
        logMessage('ERROR', 'CSRF validation failed', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } else {
        if ($_POST['action'] === 'save') {
            try {
                // 5. ВАЛИДАЦИЯ И САНИТИЗАЦИЯ ВХОДНЫХ ДАННЫХ
                $pricesData = [
                    '8_lessons' => [
                        'name' => sanitizeForStorage($_POST['price_8_name'] ?? '8 занятий'),
                        'price' => sanitizeForStorage($_POST['price_8_val'] ?? '4800₽')
                    ],
                    '6_lessons' => [
                        'name' => sanitizeForStorage($_POST['price_6_name'] ?? '6 занятий'),
                        'price' => sanitizeForStorage($_POST['price_6_val'] ?? '3900₽')
                    ],
                    '4_lessons' => [
                        'name' => sanitizeForStorage($_POST['price_4_name'] ?? '4 занятия'),
                        'price' => sanitizeForStorage($_POST['price_4_val'] ?? '2800₽')
                    ],
                    'single' => [
                        'name' => sanitizeForStorage($_POST['price_1_name'] ?? 'Разовое посещение'),
                        'price' => sanitizeForStorage($_POST['price_1_val'] ?? '750₽')
                    ],
                    'trial' => [
                        'name' => sanitizeForStorage($_POST['price_trial_name'] ?? 'Пробная тренировка'),
                        'price' => sanitizeForStorage($_POST['price_trial_val'] ?? '500₽')
                    ],
                ];

                // Валидация цен (проверка на пустые значения)
                foreach ($pricesData as $key => $priceInfo) {
                    if (empty($priceInfo['name']) || empty($priceInfo['price'])) {
                        logMessage('WARNING', "Empty price data for $key");
                    }
                }

                // Обработка расписания
                $scheduleData = [];
                if (isset($_POST['schedule_day']) && is_array($_POST['schedule_day'])) {
                    $scheduleTimes = $_POST['schedule_time'] ?? [];
                    
                    for ($i = 0; $i < count($_POST['schedule_day']); $i++) {
                        $day = trim($_POST['schedule_day'][$i] ?? '');
                        $time = trim($scheduleTimes[$i] ?? '');
                        
                        // Пропускаем полностью пустые строки
                        if ($day === '' && $time === '') {
                            continue;
                        }
                        
                        // Валидация дня недели (предупреждение, но не блокировка)
                        if ($day !== '' && !validateDayOfWeek($day)) {
                            logMessage('WARNING', 'Invalid day of week', ['day' => $day]);
                        }
                        
                        // Валидация времени
                        if ($time !== '' && validateTime($time) === null) {
                            logMessage('WARNING', 'Invalid time format', ['time' => $time]);
                        }
                        
                        $scheduleData[] = [
                            'day' => sanitizeForStorage($day),
                            'time_and_program' => sanitizeForStorage($time)
                        ];
                    }
                }

                // Валидация контактов
                $phoneRaw = $_POST['contact_phone'] ?? '';
                if (!validatePhone($phoneRaw)) {
                    logMessage('WARNING', 'Invalid phone number format', ['phone' => $phoneRaw]);
                }
                
                $contactData = [
                    'phone' => sanitizeForStorage($phoneRaw),
                    'phone_raw' => preg_replace('/[^\d+]/', '', $phoneRaw),
                    'address' => sanitizeForStorage($_POST['contact_address'] ?? '')
                ];

                // Собираем все данные
                $newContent = [
                    'prices' => $pricesData,
                    'schedule' => $scheduleData,
                    'contact' => $contactData
                ];

                logMessage('DEBUG', 'Data prepared for saving', ['content' => $newContent]);

                // Сохранение в JSON
                $jsonContent = json_encode($newContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                if ($jsonContent === false) {
                    throw new Exception('JSON encode error: ' . json_last_error_msg());
                }
                
                $bytesWritten = file_put_contents($contentFile, $jsonContent);
                if ($bytesWritten === false) {
                    throw new Exception('Failed to write content file');
                }
                
                logMessage('INFO', 'Content saved successfully', ['bytes' => $bytesWritten]);
                $message = '<div class="alert success">Изменения успешно сохранены!</div>';

                // Синхронизация с базой данных бота
                $syncResult = syncScheduleWithDatabase($scheduleData);
                if ($syncResult['success']) {
                    $message .= '<div class="alert success">✅ Расписание синхронизировано с ботом!</div>';
                    logMessage('INFO', 'Database sync successful', ['records' => $syncResult['records_count'] ?? 0]);
                } else {
                    $message .= '<div class="alert error">⚠️ Ошибка синхронизации с ботом: ' . htmlspecialchars($syncResult['error']) . '</div>';
                    logMessage('ERROR', 'Database sync failed', ['error' => $syncResult['error']]);
                }
                
            } catch (Exception $e) {
                $message = '<div class="alert error">Ошибка сохранения: ' . htmlspecialchars($e->getMessage()) . '</div>';
                logMessage('ERROR', 'Save operation failed', ['exception' => $e->getMessage()]);
            }
            
        } elseif ($_POST['action'] === 'change_password') {
            try {
                $newPassword = trim($_POST['new_password'] ?? '');
                
                // Валидация пароля
                if (strlen($newPassword) < 5) {
                    $message = '<div class="alert error">Пароль должен содержать минимум 5 символов.</div>';
                    logMessage('WARNING', 'Password change failed - too short', ['length' => strlen($newPassword)]);
                } else {
                    // Дополнительная валидация - проверка на простые пароли
                    if (in_array(strtolower($newPassword), ['password', '12345', 'admin', 'zumba'], true)) {
                        $message = '<div class="alert error">Пароль слишком простой. Используйте более сложный пароль.</div>';
                        logMessage('WARNING', 'Password change failed - too simple');
                    } else {
                        $bytesWritten = file_put_contents($passFile, $newPassword);
                        if ($bytesWritten === false) {
                            throw new Exception('Failed to write password file');
                        }
                        
                        logMessage('INFO', 'Password changed successfully');
                        $message = '<div class="alert success">Пароль успешно изменен!</div>';
                        
                        // Инвалидация текущего токена при смене пароля
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                }
            } catch (Exception $e) {
                $message = '<div class="alert error">Ошибка смены пароля: ' . htmlspecialchars($e->getMessage()) . '</div>';
                logMessage('ERROR', 'Password change failed', ['exception' => $e->getMessage()]);
            }
        }
    }
}

// Генерация токена (исправлено - токеном управляет функция getCsrfToken)
$csrfToken = getCsrfToken();

// Чтение текущих данных
$content = [];
if (file_exists($contentFile)) {
    $fileContent = file_get_contents($contentFile);
    $content = json_decode($fileContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage('ERROR', 'JSON decode error', ['error' => json_last_error_msg()]);
        $message = '<div class="alert error">Ошибка чтения данных. Файл поврежден.</div>';
        $content = [];
    }
}

$prices = $content['prices'] ?? [];
$schedule = $content['schedule'] ?? [];
$contact = $content['contact'] ?? [];
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление сайтом Zumba</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 0; }
        .header { background-color: #1a1a1a; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .logout { color: #FF2D75; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 30px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h2 { border-bottom: 2px solid #FF2D75; padding-bottom: 10px; margin-top: 30px; }
        h2:first-child { margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .row { display: flex; gap: 15px; margin-bottom: 10px; align-items: center;}
        .col { flex: 1; }
        button.save-btn { background-color: #FF2D75; color: white; border: none; padding: 12px 25px; font-size: 16px; border-radius: 4px; cursor: pointer; width: 100%; margin-top: 20px; }
        button.save-btn:hover { background-color: #e61e5f; }
        button.add-btn { background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        button.del-btn { background-color: #f44336; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .session-info { font-size: 12px; color: #888; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>⚙️ Панель управления Zumba-SPb</h1>
        <a href="?logout=1" class="logout">Выйти</a>
    </div>

    <div class="container">
        <?= $message ?>

        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <h2>💰 Цены на абонементы</h2>

            <div class="row">
                <div class="col"><label>Название (8 занятий)</label><input type="text" name="price_8_name" value="<?= htmlspecialchars($prices['8_lessons']['name'] ?? '8 занятий') ?>"></div>
                <div class="col"><label>Цена</label><input type="text" name="price_8_val" value="<?= htmlspecialchars($prices['8_lessons']['price'] ?? '4800₽') ?>"></div>
            </div>
            <div class="row">
                <div class="col"><label>Название (6 занятий)</label><input type="text" name="price_6_name" value="<?= htmlspecialchars($prices['6_lessons']['name'] ?? '6 занятий') ?>"></div>
                <div class="col"><label>Цена</label><input type="text" name="price_6_val" value="<?= htmlspecialchars($prices['6_lessons']['price'] ?? '3900₽') ?>"></div>
            </div>
            <div class="row">
                <div class="col"><label>Название (4 занятия)</label><input type="text" name="price_4_name" value="<?= htmlspecialchars($prices['4_lessons']['name'] ?? '4 занятия') ?>"></div>
                <div class="col"><label>Цена</label><input type="text" name="price_4_val" value="<?= htmlspecialchars($prices['4_lessons']['price'] ?? '2800₽') ?>"></div>
            </div>
            <div class="row">
                <div class="col"><label>Название (Разовое)</label><input type="text" name="price_1_name" value="<?= htmlspecialchars($prices['single']['name'] ?? 'Разовое посещение') ?>"></div>
                <div class="col"><label>Цена</label><input type="text" name="price_1_val" value="<?= htmlspecialchars($prices['single']['price'] ?? '750₽') ?>"></div>
            </div>
            <div class="row">
                <div class="col"><label>Название (Пробное)</label><input type="text" name="price_trial_name" value="<?= htmlspecialchars($prices['trial']['name'] ?? 'Пробная тренировка') ?>"></div>
                <div class="col"><label>Цена</label><input type="text" name="price_trial_val" value="<?= htmlspecialchars($prices['trial']['price'] ?? '500₽') ?>"></div>
            </div>

            <h2>📅 Расписание</h2>
            <div id="schedule-container">
                <?php if (empty($schedule)): ?>
                    <div class="row schedule-item">
                        <div class="col" style="flex: 0.3;"><input type="text" name="schedule_day[]" placeholder="День (например, Пн)"></div>
                        <div class="col"><input type="text" name="schedule_time[]" placeholder="Время и программа (например, 19:45 - Zumba fitness)"></div>
                        <button type="button" class="del-btn" onclick="this.parentElement.remove()">X</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($schedule as $item): ?>
                        <div class="row schedule-item">
                            <div class="col" style="flex: 0.3;"><input type="text" name="schedule_day[]" value="<?= htmlspecialchars($item['day'] ?? '') ?>" placeholder="День (например, Пн)"></div>
                            <div class="col"><input type="text" name="schedule_time[]" value="<?= htmlspecialchars($item['time_and_program'] ?? '') ?>" placeholder="Время и программа (например, 19:45 - Zumba fitness)"></div>
                            <button type="button" class="del-btn" onclick="this.parentElement.remove()">X</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="add-btn" onclick="addSchedule()">+ Добавить день</button>

            <h2>📞 Контакты</h2>
            <div class="form-group">
                <label>Телефон (для отображения)</label>
                <input type="text" name="contact_phone" value="<?= htmlspecialchars($contact['phone'] ?? '+7 (921) 892-51-57') ?>">
            </div>
            <div class="form-group">
                <label>Адрес</label>
                <input type="text" name="contact_address" value="<?= htmlspecialchars($contact['address'] ?? 'Санкт-Петербург, ул. Маршала Захарова, 20Д (Зумба у залива)') ?>">
            </div>

            <button type="submit" class="save-btn">💾 Сохранить изменения</button>
            
            <div class="session-info">
                Сессия активна с: <?= date('d.m.Y H:i', $_SESSION['login_time'] ?? time()) ?>
            </div>
        </form>

        <hr style="margin: 40px 0; border: none; border-top: 1px solid #eee;">

        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <h2>🔐 Смена пароля</h2>
            <div class="row">
                <div class="col" style="flex: 0.7;">
                    <input type="password" name="new_password" placeholder="Новый пароль (минимум 5 символов)" required minlength="5">
                </div>
                <div class="col" style="flex: 0.3;">
                    <button type="submit" class="save-btn" style="margin-top: 0; background-color: #333;">Обновить пароль</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function addSchedule() {
            const container = document.getElementById('schedule-container');
            const row = document.createElement('div');
            row.className = 'row schedule-item';
            row.innerHTML = `
                <div class="col" style="flex: 0.3;"><input type="text" name="schedule_day[]" placeholder="День"></div>
                <div class="col"><input type="text" name="schedule_time[]" placeholder="Время и программа"></div>
                <button type="button" class="del-btn" onclick="this.parentElement.remove()">X</button>
            `;
            container.appendChild(row);
        }
    </script>

    <?php
    /**
     * Синхронизация расписания из JSON в базу данных PostgreSQL
     * Используется для связи админки с Telegram-ботом
     * 
     * @param array $schedule Массив расписания
     * @return array Результат операции ['success' => bool, 'error' => string, 'records_count' => int]
     */
    function syncScheduleWithDatabase($schedule) {
        $logPrefix = '[Zumba Admin DB Sync]';
        
        try {
            logMessage('INFO', "$logPrefix Starting database sync", ['records_count' => count($schedule)]);
            
            // Проверяем наличие файла с конфигом БД
            $dbConfigPath = __DIR__ . '/../config/database.php';

            if (!file_exists($dbConfigPath)) {
                logMessage('DEBUG', "$logPrefix Database config file not found, using environment variables");
                // Пробуем получить из переменных окружения
                $dbUrl = getenv('DATABASE_URL');
                if (!$dbUrl) {
                    // Используем дефолтное значение как в bot/database.py
                    $dbUrl = 'postgresql+asyncpg://zumba_user:zumba_pass@db:5432/zumba_db';
                    logMessage('DEBUG', "$logPrefix Using default DATABASE_URL");
                }
            } else {
                logMessage('DEBUG', "$logPrefix Loading database config from file");
                $dbUrl = require $dbConfigPath;
            }

            // Парсим DATABASE_URL
            // Формат: postgresql+asyncpg://user:pass@host:port/dbname
            $parsed = parse_url(str_replace('postgresql+asyncpg://', 'postgresql://', $dbUrl));
            
            if ($parsed === false) {
                throw new Exception('Failed to parse DATABASE_URL');
            }

            $user = $parsed['user'] ?? 'zumba_user';
            $pass = $parsed['pass'] ?? 'zumba_pass';
            $host = $parsed['host'] ?? 'db';
            $port = $parsed['port'] ?? '5432';
            $dbname = ltrim($parsed['path'] ?? '/zumba_db', '/');

            logMessage('DEBUG', "$logPrefix Database connection params", [
                'host' => $host,
                'port' => $port,
                'dbname' => $dbname,
                'user' => $user
            ]);

            // ========================================================================
            // 2. УЛУЧШЕННАЯ ОБРАБОТКА ОШИБОК PDO
            // ========================================================================
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            
            try {
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]);
                logMessage('INFO', "$logPrefix Database connection established");
            } catch (PDOException $connError) {
                // Детальное логирование ошибки подключения
                $errorDetails = [
                    'code' => $connError->getCode(),
                    'message' => $connError->getMessage(),
                    'dsn' => $dsn,
                    'host' => $host
                ];
                logMessage('ERROR', "$logPrefix Database connection failed", $errorDetails);
                throw new Exception('Не удалось подключиться к базе данных. Проверьте настройки подключения.');
            }

            // Проверка подключения перед синхронизацией
            try {
                $pdo->query('SELECT 1');
                logMessage('DEBUG', "$logPrefix Database connection verified");
            } catch (PDOException $pingError) {
                logMessage('ERROR', "$logPrefix Database ping failed", ['error' => $pingError->getMessage()]);
                throw new Exception('Потеряно соединение с базой данных.');
            }

            // Маппинг дней недели
            $dayMap = [
                'Пн' => 1, 'Понедельник' => 1, 'Mon' => 1,
                'Вт' => 2, 'Вторник' => 2, 'Tue' => 2,
                'Ср' => 3, 'Среда' => 3, 'Wed' => 3,
                'Чт' => 4, 'Четверг' => 4, 'Thu' => 4,
                'Пт' => 5, 'Пятница' => 5, 'Fri' => 5,
                'Сб' => 6, 'Суббота' => 6, 'Sat' => 6,
                'Вс' => 7, 'Воскресенье' => 7, 'Sun' => 7
            ];

            // Маппинг программ
            $programMap = [
                'classic' => 'classic', 'zumba' => 'classic', 'fitness' => 'classic',
                'gold' => 'gold', 'zumba gold' => 'gold'
            ];

            // Очищаем текущее расписание (помечаем как неактивные, чтобы не нарушать FK)
            logMessage('DEBUG', "$logPrefix Deactivating existing schedule");
            $pdo->exec('UPDATE schedule SET is_active = false WHERE is_active = true');

            // Вставляем новое расписание
            $stmt = $pdo->prepare('
                INSERT INTO schedule (day_of_week, time, program, is_active, capacity)
                VALUES (:day, :time, :program, true, 20)
            ');

            $insertedCount = 0;
            $skippedCount = 0;

            foreach ($schedule as $index => $item) {
                $day = $item['day'] ?? '';
                $timeStr = $item['time_and_program'] ?? '';

                // Определяем день недели
                $dayOfWeek = $dayMap[$day] ?? null;
                if (!$dayOfWeek) {
                    logMessage('WARNING', "$logPrefix Skipping record $index - invalid day", ['day' => $day]);
                    $skippedCount++;
                    continue; // Пропускаем некорректные дни
                }

                // Извлекаем время из строки (например, "19:45 - Zumba fitness")
                $time = validateTime($timeStr);
                if ($time === null) {
                    logMessage('WARNING', "$logPrefix Skipping record $index - invalid time", ['time_str' => $timeStr]);
                    $skippedCount++;
                    continue; // Пропускаем некорректное время
                }

                // Определяем программу
                $timeStrLower = mb_strtolower($timeStr);
                $program = 'classic'; // по умолчанию
                foreach ($programMap as $key => $value) {
                    if (strpos($timeStrLower, $key) !== false) {
                        $program = $value;
                        break;
                    }
                }

                try {
                    $stmt->execute([
                        'day' => $dayOfWeek,
                        'time' => $time,
                        'program' => $program
                    ]);
                    $insertedCount++;
                    logMessage('DEBUG', "$logPrefix Inserted schedule record", [
                        'day' => $dayOfWeek,
                        'time' => $time,
                        'program' => $program
                    ]);
                } catch (PDOException $insertError) {
                    logMessage('ERROR', "$logPrefix Failed to insert record $index", [
                        'error' => $insertError->getMessage(),
                        'data' => ['day' => $dayOfWeek, 'time' => $time, 'program' => $program]
                    ]);
                    // Продолжаем обработку остальных записей
                }
            }

            logMessage('INFO', "$logPrefix Sync completed", [
                'total' => count($schedule),
                'inserted' => $insertedCount,
                'skipped' => $skippedCount
            ]);

            return [
                'success' => true, 
                'message' => "Синхронизировано записей: $insertedCount",
                'records_count' => $insertedCount
            ];

        } catch (PDOException $e) {
            // Детальное логирование всех PDO ошибок
            $errorDetails = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ];
            logMessage('ERROR', "$logPrefix PDO Exception", $errorDetails);
            
            return [
                'success' => false, 
                'error' => 'Ошибка базы данных: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            logMessage('ERROR', "$logPrefix General Exception", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false, 
                'error' => 'Ошибка: ' . $e->getMessage()
            ];
        }
    }
    ?>
</body>
</html>
