import asyncio
import json
import logging
import os
import uuid
import datetime
from contextlib import asynccontextmanager
from typing import Optional, List

from fastapi import FastAPI, Request, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from aiogram import Bot, Dispatcher, F, types
from aiogram.filters import CommandStart, Command
from aiogram.fsm.context import FSMContext
from aiogram.fsm.state import State, StatesGroup
from aiogram.types import (
    Message, ReplyKeyboardMarkup, KeyboardButton, 
    InlineKeyboardMarkup, InlineKeyboardButton, CallbackQuery
)
from sqlalchemy import select, update, func
from sqlalchemy.ext.asyncio import AsyncSession
from apscheduler.schedulers.asyncio import AsyncIOScheduler

import database
from database import User, Booking, Schedule, BookingStatus, BookingSource, ProgramType, AsyncSessionLocal

# Настройка логирования
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Загрузка конфигов
TOKEN_PATH = os.getenv("TELEGRAM_TOKEN_FILE", "/app/config/telegram_token.txt")
ADMIN_ID_PATH = os.getenv("ADMIN_ID_FILE", "/app/config/telegram_chat_id.txt")

def get_file_content(path):
    if not os.path.exists(path):
        return ""
    with open(path, "r", encoding="utf-8") as f:
        return f.read().strip()

BOT_TOKEN = get_file_content(TOKEN_PATH)

# Загружаем список администраторов (один ID на строку)
raw_admins = get_file_content(ADMIN_ID_PATH).split("\n")
ADMIN_IDS = [id.strip() for id in raw_admins if id.strip()]

def is_user_admin(user_id: int) -> bool:
    return str(user_id) in ADMIN_IDS

# Инициализация бота и FastAPI
bot = Bot(token=BOT_TOKEN)
dp = Dispatcher()
app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], 
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

scheduler = AsyncIOScheduler()

# --- Состояния FSM ---
class BookingFlow(StatesGroup):
    waiting_for_program = State()
    waiting_for_day = State()
    waiting_for_name = State()
    waiting_for_phone = State()

# --- Клавиатуры ---
def get_main_kb(is_admin_user=False):
    buttons = [
        [KeyboardButton(text="📅 Расписание"), KeyboardButton(text="📝 Записаться")],
        [KeyboardButton(text="💰 Цены"), KeyboardButton(text="📍 Адрес")],
        [KeyboardButton(text="❓ Вопрос")]
    ]
    if is_admin_user:
        buttons.append([KeyboardButton(text="👩‍🏫 Панель тренера")])
    return ReplyKeyboardMarkup(keyboard=buttons, resize_keyboard=True)

# --- Утилиты ---

async def notify_admin_new_booking(booking: Booking, user_obj: Optional[User] = None):
    if not ADMIN_IDS:
        return
    
    source_text = "Сайт" if booking.source == BookingSource.WEBSITE else "Бот"
    prog_map = {
        ProgramType.CLASSIC: "Zumba Classic", 
        ProgramType.GOLD: "Zumba Gold", 
        ProgramType.TRIAL: "Пробная", 
        ProgramType.SINGLE: "Разовая"
    }
    
    text = (
        f"🆕 <b>Новая заявка на тренировку!</b>\n\n"
        f"👤 <b>Имя:</b> {booking.client_name or (user_obj.name if user_obj else 'Не указано')}\n"
        f"📞 <b>Телефон:</b> {booking.client_phone or (user_obj.phone if user_obj else 'Не указан')}\n"
        f"💃 <b>Программа:</b> {prog_map.get(booking.program)}\n"
        f"📅 <b>Дата:</b> {booking.date.strftime('%d.%m')}\n"
        f"⏰ <b>Время:</b> {booking.date.strftime('%H:%M')}\n\n"
        f"Источник: {source_text}"
    )
    
    kb = InlineKeyboardMarkup(inline_keyboard=[
        [
            InlineKeyboardButton(text="✅ Подтвердить", callback_data=f"adm_conf_{booking.id}"),
            InlineKeyboardButton(text="❌ Отменить", callback_data=f"adm_canc_{booking.id}")
        ],
        [InlineKeyboardButton(text="✏ Связаться", url=f"tg://user?id={user_obj.telegram_id}" if user_obj else f"tel:{booking.client_phone}")]
    ])
    
    for admin_id in ADMIN_IDS:
        try:
            await bot.send_message(admin_id, text, reply_markup=kb, parse_mode="HTML")
        except Exception as e:
            logger.error(f"Failed to notify admin {admin_id}: {e}")

# --- Обработчики Бота ---

@dp.message(CommandStart())
async def cmd_start(message: Message, state: FSMContext):
    args = message.text.split()
    admin_status = is_user_admin(message.from_user.id)
    
    async with AsyncSessionLocal() as session:
        user_obj = await database.get_user_by_tg_id(session, message.from_user.id)
        if not user_obj:
            user_obj = User(
                telegram_id=message.from_user.id,
                name=message.from_user.first_name,
                username=message.from_user.username
            )
            session.add(user_obj)
            await session.commit()
        
        if len(args) > 1 and args[1].startswith("booking_"):
            code = args[1]
            stmt = select(Booking).where(Booking.deep_link_code == code)
            result = await session.execute(stmt)
            booking = result.scalars().first()
            
            if booking and booking.status == BookingStatus.PENDING_TELEGRAM:
                booking.user_id = user_obj.id
                booking.status = BookingStatus.PENDING
                await session.commit()
                
                await message.answer(
                    f"✅ Спасибо, {user_obj.name}! Ваша заявка привязана к Telegram.\n"
                    "Теперь тренер увидит ее и подтвердит. Я пришлю уведомление сюда.",
                    reply_markup=get_main_kb(admin_status)
                )
                await notify_admin_new_booking(booking, user_obj)
                return

    welcome_text = (
        f"👋 Привет, {message.from_user.first_name}! Я бот записи на тренировки <b>Zumba SPb</b>.\n\n"
        "Выберите действие ниже 👇"
    )
    await message.answer(welcome_text, reply_markup=get_main_kb(admin_status), parse_mode="HTML")

@dp.message(F.text == "📅 Расписание")
async def show_schedule(message: Message):
    async with AsyncSessionLocal() as session:
        stmt = select(Schedule).where(Schedule.is_active == True).order_by(Schedule.day_of_week, Schedule.time)
        result = await session.execute(stmt)
        schedule_items = result.scalars().all()
        
    if not schedule_items:
        await message.answer("Расписание пока не заполнено. Пожалуйста, загляните позже!")
        return
        
    days = {1: "Понедельник", 2: "Вторник", 3: "Среда", 4: "Четверг", 5: "Пятница", 6: "Суббота", 7: "Воскресенье"}
    prog_map = {ProgramType.CLASSIC: "Zumba", ProgramType.GOLD: "Zumba Gold", ProgramType.TRIAL: "Пробная", ProgramType.SINGLE: "Разовая"}
    
    text = "<b>📅 Расписание тренировок:</b>\n\n"
    for item in schedule_items:
        text += f"▪️ {days[item.day_of_week]} — {item.time.strftime('%H:%M')} ({prog_map.get(item.program)})\n"
    
    kb = InlineKeyboardMarkup(inline_keyboard=[[InlineKeyboardButton(text="📝 Записаться", callback_data="start_booking")]])
    await message.answer(text, reply_markup=kb, parse_mode="HTML")

@dp.message(F.text == "📝 Записаться")
@dp.callback_query(F.data == "start_booking")
async def start_booking_flow(event: [Message, CallbackQuery], state: FSMContext):
    if isinstance(event, CallbackQuery):
        await event.answer()
        message = event.message
    else:
        message = event
        
    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="🔥 Zumba Classic", callback_data="prog_classic")],
        [InlineKeyboardButton(text="💛 Zumba Gold", callback_data="prog_gold")],
        [InlineKeyboardButton(text="🌟 Пробная", callback_data="prog_trial")],
        [InlineKeyboardButton(text="🎟 Разовая", callback_data="prog_single")]
    ])
    await message.answer("💃 Выберите программу тренировки:", reply_markup=kb)
    await state.set_state(BookingFlow.waiting_for_program)

@dp.callback_query(BookingFlow.waiting_for_program, F.data.startswith("prog_"))
async def process_program(callback: CallbackQuery, state: FSMContext):
    program_str = callback.data.split("_")[-1]
    await state.update_data(program=program_str)
    
    async with AsyncSessionLocal() as session:
        stmt = select(Schedule).where(Schedule.is_active == True).order_by(Schedule.day_of_week, Schedule.time)
        result = await session.execute(stmt)
        schedule_items = result.scalars().all()
    
    if not schedule_items:
        await callback.message.edit_text("Извините, сейчас нет доступных тренировок.")
        await state.clear()
        return

    days = {1: "Пн", 2: "Вт", 3: "Ср", 4: "Чт", 5: "Пт", 6: "Сб", 7: "Вс"}
    buttons = []
    for item in schedule_items:
        btn_text = f"{days[item.day_of_week]} {item.time.strftime('%H:%M')}"
        buttons.append([InlineKeyboardButton(text=btn_text, callback_data=f"sched_{item.id}")])
    
    await callback.message.edit_text("📅 Выберите день и время:", reply_markup=InlineKeyboardMarkup(inline_keyboard=buttons))
    await state.set_state(BookingFlow.waiting_for_day)

@dp.callback_query(BookingFlow.waiting_for_day, F.data.startswith("sched_"))
async def process_day(callback: CallbackQuery, state: FSMContext):
    sched_id = int(callback.data.split("_")[-1])
    await state.update_data(schedule_id=sched_id)
    
    async with AsyncSessionLocal() as session:
        user_obj = await database.get_user_by_tg_id(session, callback.from_user.id)
    
    if user_obj and user_obj.name and user_obj.phone:
        await state.update_data(name=user_obj.name, phone=user_obj.phone)
        await finalize_booking(callback.message, state)
    else:
        await callback.message.edit_text("👤 Введите ваше имя:")
        await state.set_state(BookingFlow.waiting_for_name)

@dp.message(BookingFlow.waiting_for_name)
async def process_name(message: Message, state: FSMContext):
    await state.update_data(name=message.text)
    await message.answer("📱 Введите ваш номер телефона (или нажмите кнопку ниже):", 
                         reply_markup=ReplyKeyboardMarkup(keyboard=[[KeyboardButton(text="📱 Отправить номер", request_contact=True)]], resize_keyboard=True, one_time_keyboard=True))
    await state.set_state(BookingFlow.waiting_for_phone)

@dp.message(BookingFlow.waiting_for_phone)
async def process_phone(message: Message, state: FSMContext):
    phone = message.contact.phone_number if message.contact else message.text
    await state.update_data(phone=phone)
    await finalize_booking(message, state)

async def finalize_booking(message: Message, state: FSMContext):
    data = await state.get_data()
    async with AsyncSessionLocal() as session:
        user_obj = await database.get_user_by_tg_id(session, message.chat.id)
        if user_obj:
            user_obj.name = data['name']
            user_obj.phone = data['phone']
        
        stmt = select(Schedule).where(Schedule.id == data['schedule_id'])
        res = await session.execute(stmt)
        sched = res.scalars().first()
        
        today = datetime.datetime.now()
        days_ahead = sched.day_of_week - (today.weekday() + 1)
        if days_ahead < 0:
            days_ahead += 7
        target_date = today + datetime.timedelta(days=days_ahead)
        target_datetime = datetime.datetime.combine(target_date.date(), sched.time)
        
        new_booking = Booking(
            user_id=user_obj.id,
            schedule_id=sched.id,
            program=ProgramType(data['program']),
            date=target_datetime,
            status=BookingStatus.PENDING,
            source=BookingSource.BOT,
            client_name=data['name'],
            client_phone=data['phone']
        )
        session.add(new_booking)
        await session.commit()
        await session.refresh(new_booking)
        
    await state.clear()
    admin_status = is_user_admin(message.chat.id)
    await message.answer("Спасибо! 🎉\n\nВаша заявка отправлена тренеру. Скоро придет подтверждение.", reply_markup=get_main_kb(admin_status))
    await notify_admin_new_booking(new_booking, user_obj)

@dp.message(F.text == "💰 Цены")
async def show_prices(message: Message):
    text = (
        "<b>💰 Стоимость занятий:</b>\n\n"
        "▪️ Пробное занятие — <b>Бесплатно</b>\n"
        "▪️ Разовое занятие — <b>800₽</b>\n"
        "▪️ Абонемент 4 занятия — <b>2800₽</b>\n"
        "▪️ Абонемент 8 занятий — <b>4800₽</b>\n"
    )
    await message.answer(text, parse_mode="HTML")

@dp.message(F.text == "📍 Адрес")
async def show_address(message: Message):
    text = (
        "<b>📍 Наш адрес:</b>\n\n"
        "Санкт-Петербург, ул. Маршала Захарова, 20Д\n"
        "Студия «Zumba у Залива»\n\n"
        "🗺 <a href='https://yandex.ru/maps/org/zumba_u_zaliva/99077668985'>Открыть в Яндекс Картах</a>"
    )
    await message.answer(text, parse_mode="HTML", disable_web_page_preview=True)

@dp.message(F.text == "❓ Вопрос")
async def ask_question(message: Message):
    await message.answer("Напишите ваш вопрос тренеру напрямую: @Zumbayzalyva")

# --- Админ-панель ---

@dp.message(F.text == "👩‍🏫 Панель тренера")
@dp.message(Command("admin"))
async def show_admin_panel(message: Message):
    if not is_user_admin(message.from_user.id):
        return

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="📋 Сегодняшние записи", callback_data="adm_today")],
        [InlineKeyboardButton(text="📊 Статистика", callback_data="adm_stats")]
    ])
    await message.answer("👩‍🏫 <b>Панель тренера</b>", reply_markup=kb, parse_mode="HTML")

# --- Команда "стат" для администраторов ---

@dp.message(Command("stat"))
@dp.message(F.text.lower() == "стат")
async def show_stats(message: Message):
    """Показывает статистику за месяц для администраторов"""
    if not is_user_admin(message.from_user.id):
        return

    async with AsyncSessionLocal() as session:
        now = datetime.datetime.now()
        month_start = now.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
        week_start = now - datetime.timedelta(days=7)

        # Статистика за месяц
        clients_count_month = await session.scalar(select(func.count(User.id)).where(User.created_at >= month_start))
        bookings_count_month = await session.scalar(select(func.count(Booking.id)).where(Booking.date >= month_start, Booking.status == BookingStatus.CONFIRMED))
        trial_count_month = await session.scalar(select(func.count(Booking.id)).where(Booking.date >= month_start, Booking.status == BookingStatus.CONFIRMED, Booking.program == ProgramType.TRIAL))

        # Статистика за неделю
        clients_count_week = await session.scalar(select(func.count(User.id)).where(User.created_at >= week_start))
        bookings_count_week = await session.scalar(select(func.count(Booking.id)).where(Booking.date >= week_start, Booking.status == BookingStatus.CONFIRMED))
        trial_count_week = await session.scalar(select(func.count(Booking.id)).where(Booking.date >= week_start, Booking.status == BookingStatus.CONFIRMED, Booking.program == ProgramType.TRIAL))

        # Общая статистика
        total_users = await session.scalar(select(func.count(User.id)))
        total_bookings = await session.scalar(select(func.count(Booking.id)).where(Booking.status == BookingStatus.CONFIRMED))

        # Статистика по программам за месяц
        classic_count = await session.scalar(select(func.count(Booking.id)).where(
            Booking.date >= month_start, Booking.status == BookingStatus.CONFIRMED, Booking.program == ProgramType.CLASSIC
        ))
        gold_count = await session.scalar(select(func.count(Booking.id)).where(
            Booking.date >= month_start, Booking.status == BookingStatus.CONFIRMED, Booking.program == ProgramType.GOLD
        ))

    text = (
        f"📊 <b>Статистика Zumba SPb</b>\n\n"
        f"<b>За неделю:</b>\n"
        f"👥 Новых клиентов: {clients_count_week}\n"
        f"💃 Записей: {bookings_count_week}\n"
        f"🔥 Пробных: {trial_count_week}\n\n"
        f"<b>За месяц ({now.strftime('%B %Y')}):</b>\n"
        f"👥 Новых клиентов: {clients_count_month}\n"
        f"💃 Записей: {bookings_count_month}\n"
        f"🔥 Пробных: {trial_count_month}\n"
        f"💃 Zumba Classic: {classic_count}\n"
        f"💛 Zumba Gold: {gold_count}\n\n"
        f"<b>Всего:</b>\n"
        f"👥 Клиентов в базе: {total_users}\n"
        f"💃 Проведено тренировок: {total_bookings}\n"
    )

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="📋 Сегодняшние записи", callback_data="adm_today")],
        [InlineKeyboardButton(text="👩‍🏫 Панель тренера", callback_data="adm_main")]
    ])

    await message.answer(text, reply_markup=kb, parse_mode="HTML")

@dp.callback_query(F.data == "adm_today")
async def admin_today_bookings(callback: CallbackQuery):
    async with AsyncSessionLocal() as session:
        today_start = datetime.datetime.now().replace(hour=0, minute=0, second=0, microsecond=0)
        today_end = today_start + datetime.timedelta(days=1)
        stmt = select(Booking).where(Booking.date >= today_start, Booking.date < today_end, Booking.status == BookingStatus.CONFIRMED)
        res = await session.execute(stmt)
        bookings = res.scalars().all()
        
    if not bookings:
        await callback.message.edit_text("На сегодня записей нет.", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[InlineKeyboardButton(text="🔙 Назад", callback_data="adm_main")]]))
        return
        
    text = f"📅 <b>Сегодня ({datetime.datetime.now().strftime('%d.%m')}):</b>\n\n"
    prog_map = {ProgramType.CLASSIC: "Zumba", ProgramType.GOLD: "Gold", ProgramType.TRIAL: "Пробная", ProgramType.SINGLE: "Разовая"}
    for i, b in enumerate(bookings, 1):
        text += f"{i}️⃣ <b>{b.client_name}</b> - {prog_map.get(b.program)} ({b.date.strftime('%H:%M')})\n"
        
    await callback.message.edit_text(text, parse_mode="HTML", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[InlineKeyboardButton(text="🔙 Назад", callback_data="adm_main")]]))

@dp.callback_query(F.data == "adm_stats")
async def admin_stats(callback: CallbackQuery):
    async with AsyncSessionLocal() as session:
        now = datetime.datetime.now()
        month_start = now.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
        
        clients_count = await session.scalar(select(func.count(User.id)).where(User.created_at >= month_start))
        bookings_count = await session.scalar(select(func.count(Booking.id)).where(Booking.date >= month_start, Booking.status == BookingStatus.CONFIRMED))
        trial_count = await session.scalar(select(func.count(Booking.id)).where(Booking.date >= month_start, Booking.status == BookingStatus.CONFIRMED, Booking.program == ProgramType.TRIAL))
        
    text = (
        f"📊 <b>Статистика за {now.strftime('%B')}:</b>\n\n"
        f"👥 Новых клиентов: {clients_count}\n"
        f"💃 Записей: {bookings_count}\n"
        f"🔥 Пробных: {trial_count}\n"
    )
    await callback.message.edit_text(text, parse_mode="HTML", reply_markup=InlineKeyboardMarkup(inline_keyboard=[[InlineKeyboardButton(text="🔙 Назад", callback_data="adm_main")]]))

@dp.callback_query(F.data == "adm_main")
async def back_to_admin_main(callback: CallbackQuery):
    await show_admin_panel(callback.message)
    await callback.answer()

@dp.callback_query(F.data.startswith("adm_conf_"))
async def handle_admin_confirm(callback: CallbackQuery):
    booking_id = int(callback.data.split("_")[-1])
    async with AsyncSessionLocal() as session:
        booking = await session.get(Booking, booking_id)
        if booking:
            booking.status = BookingStatus.CONFIRMED
            await session.commit()
            
            if booking.user_id:
                user_obj = await session.get(User, booking.user_id)
                if user_obj:
                    prog_map = {ProgramType.CLASSIC: "Zumba Classic", ProgramType.GOLD: "Zumba Gold", ProgramType.TRIAL: "Пробная", ProgramType.SINGLE: "Разовая"}
                    client_text = (
                        f"🎉 <b>Вы записаны на тренировку!</b>\n\n"
                        f"💃 <b>Программа:</b> {prog_map.get(booking.program)}\n"
                        f"📅 <b>Дата:</b> {booking.date.strftime('%d.%m')}\n"
                        f"⏰ <b>Время:</b> {booking.date.strftime('%H:%M')}\n\n"
                        f"📍 <b>Адрес:</b>\nСанкт-Петербург, ул. Маршала Захарова 20Д\n\n"
                        f"Возьмите с собой:\n• удобную форму\n• кроссовки\n• воду\n\n"
                        f"До встречи! 💃"
                    )
                    try:
                        await bot.send_message(user_obj.telegram_id, client_text, parse_mode="HTML")
                    except Exception as e:
                        logger.error(f"Notify client error: {e}")

            await callback.message.edit_text(callback.message.text + "\n\n✅ <b>ПОДТВЕРЖДЕНО</b>", parse_mode="HTML")
            await callback.answer("Запись подтверждена")

@dp.callback_query(F.data.startswith("adm_canc_"))
async def handle_admin_cancel(callback: CallbackQuery):
    booking_id = int(callback.data.split("_")[-1])
    async with AsyncSessionLocal() as session:
        booking = await session.get(Booking, booking_id)
        if booking:
            booking.status = BookingStatus.CANCELLED
            await session.commit()
            await callback.message.edit_text(callback.message.text + "\n\n❌ <b>ОТМЕНЕНО</b>", parse_mode="HTML")
            await callback.answer("Запись отменена")

# --- Планировщик и Напоминания ---

async def send_reminders():
    logger.info("Running reminders check...")
    now = datetime.datetime.now()
    remind_time = now + datetime.timedelta(hours=6)
    
    async with AsyncSessionLocal() as session:
        # Ищем подтвержденные записи через 6 часов (плюс-минус 5 минут для надежности)
        stmt = select(Booking).where(
            Booking.status == BookingStatus.CONFIRMED,
            Booking.reminder_sent == False,
            Booking.date <= remind_time
        )
        result = await session.execute(stmt)
        bookings = result.scalars().all()
        
        for b in bookings:
            if b.user_id:
                user_obj = await session.get(User, b.user_id)
                if user_obj:
                    prog_map = {ProgramType.CLASSIC: "Zumba Classic", ProgramType.GOLD: "Zumba Gold", ProgramType.TRIAL: "Пробная", ProgramType.SINGLE: "Разовая"}
                    reminder_text = (
                        f"⏰ <b>Напоминание о тренировке</b>\n\n"
                        f"Сегодня в {b.date.strftime('%H:%M')}\n"
                        f"💃 {prog_map.get(b.program)}\n\n"
                        f"📍 ул. Маршала Захарова 20Д\n\n"
                        f"Ждем вас! 💃"
                    )
                    try:
                        await bot.send_message(user_obj.telegram_id, reminder_text, parse_mode="HTML")
                        b.reminder_sent = True
                        await session.commit()
                    except Exception as e:
                        logger.error(f"Reminder error: {e}")

# --- FastAPI API ---

@app.post("/api/leads")
async def create_lead(request: Request):
    data = await request.json()
    name = data.get("name")
    phone = data.get("phone")
    program_str = data.get("program", "classic")
    date_str = data.get("date", datetime.datetime.now().strftime("%Y-%m-%d %H:%M"))
    
    prog_map = {"classic": ProgramType.CLASSIC, "gold": ProgramType.GOLD, "trial": ProgramType.TRIAL, "single": ProgramType.SINGLE}
    program = prog_map.get(program_str, ProgramType.CLASSIC)
    
    deep_link_code = f"booking_{uuid.uuid4().hex[:8]}"
    
    async with AsyncSessionLocal() as session:
        new_booking = Booking(
            client_name=name,
            client_phone=phone,
            program=program,
            date=datetime.datetime.strptime(date_str, "%Y-%m-%d %H:%M"),
            status=BookingStatus.PENDING_TELEGRAM,
            source=BookingSource.WEBSITE,
            deep_link_code=deep_link_code
        )
        session.add(new_booking)
        await session.commit()
        await session.refresh(new_booking)

    me = await bot.get_me()
    bot_url = f"https://t.me/{me.username}?start={deep_link_code}"
    
    return {"success": True, "bot_url": bot_url}

# --- Lifecycle ---

@asynccontextmanager
async def lifespan(app_obj: FastAPI):
    await database.init_db()
    
    # Первоначальное заполнение расписания, если пусто
    async with AsyncSessionLocal() as session:
        count = await session.scalar(select(func.count(Schedule.id)))
        if count == 0:
            session.add_all([
                Schedule(day_of_week=1, time=datetime.time(19, 45), program=ProgramType.CLASSIC),
                Schedule(day_of_week=3, time=datetime.time(20, 0), program=ProgramType.CLASSIC),
                Schedule(day_of_week=5, time=datetime.time(10, 30), program=ProgramType.CLASSIC),
                Schedule(day_of_week=7, time=datetime.time(12, 0), program=ProgramType.GOLD),
            ])
            await session.commit()

    polling_task = asyncio.create_task(dp.start_polling(bot))
    scheduler.add_job(send_reminders, 'interval', minutes=1)
    scheduler.start()
    
    logger.info("Background tasks started")
    yield
    polling_task.cancel()
    scheduler.shutdown()

app.router.lifespan_context = lifespan

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
