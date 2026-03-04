import aiohttp
import logging
from config import CRM_API_URL

logger = logging.getLogger(__name__)

class CRMClient:
    """Клиент для общения с Next.js CRM API"""
    
    def __init__(self):
        self.base_url = CRM_API_URL
        self._session = None
        
    async def get_session(self) -> aiohttp.ClientSession:
        if self._session is None or self._session.closed:
            self._session = aiohttp.ClientSession()
        return self._session

    async def check_user(self, tg_id: int) -> dict | None:
        """Проверяет, зарегистрирован ли пользователь в CRM."""
        try:
            session = await self.get_session()
            async with session.get(f"{self.base_url}/clients/tg/{tg_id}") as resp:
                if resp.status == 200:
                    return await resp.json()
                return None
        except Exception as e:
            logger.error(f"Error checking user {tg_id}: {e}")
            return None

    async def register_user(self, tg_id: int, phone: str, name: str) -> bool:
        """Регистрирует нового пользователя."""
        try:
            session = await self.get_session()
            payload = {
                "telegram_id": tg_id,
                "phone": phone,
                "name": name,
                "source": "telegram_bot"
            }
            async with session.post(f"{self.base_url}/clients", json=payload) as resp:
                return resp.status in (200, 201)
        except Exception as e:
            logger.error(f"Error registering user {tg_id}: {e}")
            return False

    async def get_schedule(self) -> list:
        """Получает доступное расписание тренировок."""
        try:
            session = await self.get_session()
            async with session.get(f"{self.base_url}/schedule/available") as resp:
                if resp.status == 200:
                    return await resp.json()
                return []
        except Exception as e:
            logger.error(f"Error fetching schedule: {e}")
            return []

    async def create_booking(self, tg_id: int, schedule_id: int, program: str) -> bool:
        """Создает запись на тренировку."""
        try:
            session = await self.get_session()
            payload = {
                "telegram_id": tg_id,
                "schedule_id": schedule_id,
                "program": program
            }
            async with session.post(f"{self.base_url}/bookings", json=payload) as resp:
                return resp.status in (200, 201)
        except Exception as e:
            logger.error(f"Error creating booking for {tg_id}: {e}")
            return False

    async def close(self):
        if self._session and not self._session.closed:
            await self._session.close()

# Глобальный экземпляр клиента для импорта в другие модули
crm_client = CRMClient()
