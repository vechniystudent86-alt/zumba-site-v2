import os
import datetime
from sqlalchemy import Column, Integer, String, DateTime, ForeignKey, Enum, Boolean, Time
from sqlalchemy.ext.asyncio import create_async_engine, AsyncSession
from sqlalchemy.orm import relationship, sessionmaker, declarative_base
import enum

DATABASE_URL = os.getenv("DATABASE_URL", "postgresql+asyncpg://zumba_user:zumba_pass@db:5432/zumba_db")

Base = declarative_base()

class BookingStatus(enum.Enum):
    PENDING_TELEGRAM = "pending_telegram" # Заявка с сайта, ждем перехода в бот
    PENDING = "pending"                   # Ждем подтверждения тренера
    CONFIRMED = "confirmed"               # Подтверждено
    CANCELLED = "cancelled"               # Отменено

class BookingSource(enum.Enum):
    WEBSITE = "website"
    BOT = "bot"

class ProgramType(enum.Enum):
    CLASSIC = "classic"
    GOLD = "gold"
    TRIAL = "trial"
    SINGLE = "single"

class User(Base):
    __tablename__ = "users"
    id = Column(Integer, primary_key=True)
    telegram_id = Column(Integer, unique=True, index=True)
    name = Column(String)
    phone = Column(String)
    username = Column(String)
    created_at = Column(DateTime, default=datetime.datetime.utcnow)
    
    bookings = relationship("Booking", back_populates="user")

class Schedule(Base):
    __tablename__ = "schedule"
    id = Column(Integer, primary_key=True)
    day_of_week = Column(Integer) # 1-7
    time = Column(Time)
    program = Column(Enum(ProgramType))
    capacity = Column(Integer, default=20)
    is_active = Column(Boolean, default=True)

class Booking(Base):
    __tablename__ = "bookings"
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    schedule_id = Column(Integer, ForeignKey("schedule.id"), nullable=True)
    
    # Поля для заявок с сайта, пока нет user_id
    client_name = Column(String)
    client_phone = Column(String)
    
    program = Column(Enum(ProgramType))
    date = Column(DateTime)
    status = Column(Enum(BookingStatus), default=BookingStatus.PENDING)
    source = Column(Enum(BookingSource))
    deep_link_code = Column(String, unique=True, index=True)
    reminder_sent = Column(Boolean, default=False)
    
    created_at = Column(DateTime, default=datetime.datetime.utcnow)
    
    user = relationship("User", back_populates="bookings")

engine = create_async_engine(DATABASE_URL, echo=True)
AsyncSessionLocal = sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)

async def init_db():
    async with engine.begin() as conn:
        # В продакшене лучше использовать Alembic
        await conn.run_sync(Base.metadata.create_all)

async def get_db():
    async with AsyncSessionLocal() as session:
        yield session

# Helper functions
async def get_user_by_tg_id(session: AsyncSession, tg_id: int):
    from sqlalchemy import select
    result = await session.execute(select(User).where(User.telegram_id == tg_id))
    return result.scalars().first()

async def create_user(session: AsyncSession, tg_id: int, name: str, phone: str = None, username: str = None):
    user = User(telegram_id=tg_id, name=name, phone=phone, username=username)
    session.add(user)
    await session.commit()
    return user
