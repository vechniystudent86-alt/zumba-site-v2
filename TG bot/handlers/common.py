from aiogram import Router, F
from aiogram.filters import CommandStart
from aiogram.types import Message, CallbackQuery
from aiogram.fsm.context import FSMContext

from services.crm_api import crm_client
from keyboards.main_keyboard import get_main_menu, get_contact_keyboard
from states.booking_states import RegistrationStates

router = Router(name="common_router")

@router.message(CommandStart())
async def cmd_start(message: Message, state: FSMContext):
    # Очищаем состояния на случай, если пользователь был в процессе
    await state.clear()
    
    # Проверяем, есть ли пользователь в CRM
    user = await crm_client.check_user(message.from_user.id)
    
    if user:
        await message.answer(
            f"С возвращением, {user.get('name', message.from_user.first_name)}! 👋\n"
            "Выберите нужное действие в меню ниже:",
            reply_markup=get_main_menu()
        )
    else:
        await state.set_state(RegistrationStates.waiting_for_contact)
        await message.answer(
            "Добро пожаловать в бота Zumba Юго-Западная! 💃\n\n"
            "Чтобы начать записываться на тренировки, пожалуйста, поделитесь своим номером телефона (нажмите кнопку ниже).",
            reply_markup=get_contact_keyboard()
        )

@router.message(RegistrationStates.waiting_for_contact, F.contact)
async def process_contact(message: Message, state: FSMContext):
    contact = message.contact
    if contact.user_id != message.from_user.id:
        await message.answer("Пожалуйста, отправьте свой собственный контакт.")
        return

    phone = contact.phone_number
    name = message.from_user.first_name
    
    success = await crm_client.register_user(message.from_user.id, phone, name)
    
    if success:
        await state.clear()
        await message.answer(
            "Регистрация успешно завершена! 🎉\nТеперь вы можете записываться на тренировки.",
            reply_markup=get_main_menu()
        )
    else:
        await message.answer("Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.")

@router.callback_query(F.data == "menu_main")
async def back_to_main(callback: CallbackQuery, state: FSMContext):
    await state.clear()
    await callback.message.edit_text(
        "Главное меню:",
        reply_markup=get_main_menu()
    )
    await callback.answer()
