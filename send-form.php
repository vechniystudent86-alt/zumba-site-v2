<?php
/**
 * Обработчик форм заявки на тренировку
 * 1. Отправляет заявку в CRM систему
 * 2. Отправляет уведомление в Telegram
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/form_errors.log');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешён']);
    exit;
}

try {
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

    // Получаем данные формы
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $program = trim($_POST['program'] ?? 'classic');
    $message = trim($_POST['message'] ?? '');

    // Валидация
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        throw new Exception('Введите корректное имя (2-100 символов)');
    }
    
    $phoneClean = preg_replace('/[^\d+]/', '', $phone);
    if (!preg_match('/^\+?7?\d{10}$/', $phoneClean)) {
        throw new Exception('Введите корректный номер телефона');
    }
    
    if (!in_array($program, ['classic', 'gold'])) {
        throw new Exception('Неверная программа тренировок');
    }

    // Отправляем в CRM
    $crmData = [
        'name' => $name,
        'phone' => $phone,
        'program' => $program,
        'message' => $message,
        'source' => 'website'
    ];

    $ch = curl_init($crmUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($crmData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $crmResponse = curl_exec($ch);
    $crmHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $crmError = curl_error($ch);
    curl_close($ch);

    if ($crmHttpCode !== 201 && $crmHttpCode !== 200) {
        error_log("CRM Error: HTTP $crmHttpCode - $crmError - Response: $crmResponse");
        throw new Exception('Ошибка сохранения заявки. Попробуйте позвонить нам.');
    }

    $crmResult = json_decode($crmResponse, true);
    if (!$crmResult || isset($crmResult['detail'])) {
        error_log("CRM Response Error: " . json_encode($crmResult));
        throw new Exception('Ошибка обработки заявки');
    }

    // Отправляем уведомление в Telegram (если настроено)
    if ($telegramToken && $telegramChatId) {
        $tgMessage = sprintf(
            "🔔 *Новая заявка с сайта!*\n\n" .
            "👤 *Имя:* %s\n" .
            "📱 *Телефон:* %s\n" .
            "💃 *Программа:* %s\n" .
            "%s\n\n" .
            "🌐 https://zumba-spb.ru",
            $name,
            $phone,
            $program === 'classic' ? 'Zumba Classic' : 'Zumba Gold',
            $message ? "💬 *Сообщение:* " . $message . "\n" : ""
        );

        $tgUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";
        $tgData = [
            'chat_id' => $telegramChatId,
            'text' => $tgMessage,
            'parse_mode' => 'MarkdownV2'
        ];

        $ch = curl_init($tgUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tgData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    // Успех
    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.'
    ]);

} catch (Exception $e) {
    error_log("Form Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
