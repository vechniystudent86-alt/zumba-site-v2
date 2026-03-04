from aiogram import Router, F
from aiogram.types import CallbackQuery
from aiogram.fsm.context import FSMContext

from states.booking_states import BookingStates
from keyboards.main_keyboard import get_programs_keyboard, get_main_menu
from services.crm_api import crm_client

router = Router(name="booking_router")

@router.callback_query(F.data == "menu_book")
async def start_booking(callback: CallbackQuery, state: FSMContext):
    await state.set_state(BookingStates.waiting_for_program)
    await callback.message.edit_text(
        "Выберите программу тренировки:",
        reply_markup=get_programs_keyboard()
    )
    await callback.answer()

@router.callback_query(BookingStates.waiting_for_program, F.data.startswith("program_"))
async def process_program(callback: CallbackQuery, state: FSMContext):
    program = callback.data.split("_")[1] # 'classic' или 'gold'
    await state.update_data(program=program)
    
    # Запрашиваем расписание из CRM
    schedule = await crm_client.get_schedule()
    
    if not schedule:
        await callback.message.edit_text(
            "К сожалению, сейчас нет доступных тренировок для записи.\nСледите за обновлениями!",
            reply_markup=get_main_menu()
        )
        await state.clear()
        return

    # TODO: Сгенерировать Inline-клавиатуру из полученного schedule
    # Пока выводим заглушку
    await state.set_state(BookingStates.waiting_for_date)
    await callback.message.edit_text(
        f"Вы выбрали Zumba {program.capitalize()}.\n\n(Здесь будет вывод доступных дат из CRM)"
    )
    await callback.answer()

# Здесь можно добавить дальнейшие шаги FSM для подтверждения записи
