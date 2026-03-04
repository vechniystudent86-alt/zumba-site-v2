import asyncio
import logging

from aiogram import Bot, Dispatcher
from aiogram.fsm.storage.memory import MemoryStorage

from config import BOT_TOKEN
from handlers.common import router as common_router
from handlers.booking import router as booking_router
from services.crm_api import crm_client

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(levelname)s - %(name)s - %(message)s",
)
logger = logging.getLogger(__name__)

async def main():
    logger.info("Starting bot...")

    # Инициализация бота и диспетчера с использованием MemoryStorage
    bot = Bot(token=BOT_TOKEN)
    storage = MemoryStorage()
    dp = Dispatcher(storage=storage)

    # Подключение роутеров
    dp.include_router(common_router)
    dp.include_router(booking_router)

    try:
        # Запуск polling
        await dp.start_polling(bot)
    finally:
        # Корректное завершение работы
        await bot.session.close()
        await crm_client.close()

if __name__ == "__main__":
    try:
        asyncio.run(main())
    except (KeyboardInterrupt, SystemExit):
        logger.info("Bot stopped.")
