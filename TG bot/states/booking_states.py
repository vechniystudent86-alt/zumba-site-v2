from aiogram.fsm.state import State, StatesGroup

class RegistrationStates(StatesGroup):
    waiting_for_contact = State()

class BookingStates(StatesGroup):
    waiting_for_program = State()
    waiting_for_date = State()
    waiting_for_confirmation = State()
