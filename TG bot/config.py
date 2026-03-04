import os
from dotenv import load_dotenv

load_dotenv()

BOT_TOKEN = os.getenv("BOT_TOKEN")
if not BOT_TOKEN:
    raise ValueError("BOT_TOKEN is not set in .env file")

# Укажите базовый URL вашей Next.js CRM. Для локального тестирования можно использовать localhost
CRM_API_URL = os.getenv("CRM_API_URL", "http://localhost:8000/api")
