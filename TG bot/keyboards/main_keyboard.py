from aiogram.types import ReplyKeyboardMarkup, KeyboardButton, InlineKeyboardMarkup, InlineKeyboardButton

def get_contact_keyboard() -> ReplyKeyboardMarkup:
    """Клавиатура для запроса контакта при регистрации."""
    button = KeyboardButton(text="📱 Отправить номер телефона", request_contact=True)
    return ReplyKeyboardMarkup(keyboard=[[button]], resize_keyboard=True, one_time_keyboard=True)

def get_main_menu() -> InlineKeyboardMarkup:
    """Главное меню бота."""
    keyboard = [
        [InlineKeyboardButton(text="📅 Расписание", callback_data="menu_schedule")],
        [InlineKeyboardButton(text="💪 Записаться на тренировку", callback_data="menu_book")],
        [InlineKeyboardButton(text="👤 Мои записи", callback_data="menu_my_bookings")],
        [InlineKeyboardButton(text="🌐 Наш сайт", url="https://zumba-spb.ru")]
    ]
    return InlineKeyboardMarkup(inline_keyboard=keyboard)

def get_programs_keyboard() -> InlineKeyboardMarkup:
    """Выбор программы для записи."""
    keyboard = [
        [InlineKeyboardButton(text="💃 Zumba Classic", callback_data="program_classic")],
        [InlineKeyboardButton(text="🌟 Zumba Gold", callback_data="program_gold")],
        [InlineKeyboardButton(text="🔙 Назад", callback_data="menu_main")]
    ]
    return InlineKeyboardMarkup(inline_keyboard=keyboard)
