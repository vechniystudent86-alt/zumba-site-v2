<?php
session_start();

$passFile = __DIR__ . '/../config/admin_pass.txt';
$contentFile = __DIR__ . '/../data/content.json';

// Авторизация
$adminPass = file_exists($passFile) ? trim(file_get_contents($passFile)) : 'zumba2024';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $adminPass) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный пароль!";
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
            <?php if (!empty($error)): ?><div class="error"><?= $error ?></div><?php endif; ?>
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

// --- Обработка сохранения ---
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    // Валидация CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = '<div class="alert error">Ошибка безопасности (CSRF).</div>';
    } else {
        // Собираем данные
        $newContent = [
            'prices' => [
                '8_lessons' => ['name' => $_POST['price_8_name'], 'price' => $_POST['price_8_val']],
                '6_lessons' => ['name' => $_POST['price_6_name'], 'price' => $_POST['price_6_val']],
                '4_lessons' => ['name' => $_POST['price_4_name'], 'price' => $_POST['price_4_val']],
                'single' => ['name' => $_POST['price_1_name'], 'price' => $_POST['price_1_val']],
                'trial' => ['name' => $_POST['price_trial_name'], 'price' => $_POST['price_trial_val']],
            ],
            'schedule' => [],
            'contact' => [
                'phone' => $_POST['contact_phone'],
                'phone_raw' => preg_replace('/[^\d+]/', '', $_POST['contact_phone']),
                'address' => $_POST['contact_address']
            ]
        ];

        // Расписание (динамическое)
        if (isset($_POST['schedule_day']) && is_array($_POST['schedule_day'])) {
            for ($i = 0; $i < count($_POST['schedule_day']); $i++) {
                $day = trim($_POST['schedule_day'][$i]);
                $time = trim($_POST['schedule_time'][$i]);
                if ($day !== '' || $time !== '') {
                    $newContent['schedule'][] = ['day' => $day, 'time_and_program' => $time];
                }
            }
        }

        // Сохранение
        if (file_put_contents($contentFile, json_json_encode($newContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
            $message = '<div class="alert success">Изменения успешно сохранены!</div>';
        } else {
            $message = '<div class="alert error">Ошибка сохранения файла. Проверьте права на запись.</div>';
        }
    }
}

// Генерация токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Чтение текущих данных
$content = file_exists($contentFile) ? json_decode(file_get_contents($contentFile), true) : [];
$prices = $content['prices'] ?? [];
$schedule = $content['schedule'] ?? [];
$contact = $content['contact'] ?? [];

?>
<!DOCTYPE html>
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
        .row { display: flex; gap: 15px; margin-bottom: 10px; align-items: center;}
        .col { flex: 1; }
        button.save-btn { background-color: #FF2D75; color: white; border: none; padding: 12px 25px; font-size: 16px; border-radius: 4px; cursor: pointer; width: 100%; margin-top: 20px; }
        button.save-btn:hover { background-color: #e61e5f; }
        button.add-btn { background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        button.del-btn { background-color: #f44336; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

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
</body>
</html>
