-- Миграция: Создание таблицы settings для хранения настроек бота
-- Дата: 2026-03-14
-- Описание: Таблица для хранения цен, контактов и других настроек Telegram-бота

CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Индекс для быстрого поиска по ключу
CREATE INDEX IF NOT EXISTS idx_settings_key ON settings(key);

-- Начальные значения (цены по умолчанию)
INSERT INTO settings (key, value) VALUES
    ('price_8_lessons_name', '8 занятий'),
    ('price_8_lessons_price', '4800₽'),
    ('price_6_lessons_name', '6 занятий'),
    ('price_6_lessons_price', '3900₽'),
    ('price_4_lessons_name', '4 занятия'),
    ('price_4_lessons_price', '2800₽'),
    ('price_single_name', 'Разовое посещение'),
    ('price_single_price', '750₽'),
    ('price_trial_name', 'Пробная тренировка'),
    ('price_trial_price', '500₽'),
    ('contact_phone', '+7 (921) 892-51-57'),
    ('contact_address', 'Санкт-Петербург, ул. Маршала Захарова, 20Д (Зумба у залива)')
ON CONFLICT (key) DO NOTHING;

-- Комментарий
COMMENT ON TABLE settings IS 'Настройки Telegram-бота: цены, контакты, параметры';
