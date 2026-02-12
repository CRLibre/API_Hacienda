from sqlalchemy import Index, Integer, String, Text
from sqlalchemy.orm import Mapped, mapped_column

from python_api.db.base import Base


class User(Base):
    __tablename__ = "users"

    id_user: Mapped[int] = mapped_column("idUser", Integer, primary_key=True, autoincrement=True)
    full_name: Mapped[str] = mapped_column("fullName", String(255))
    user_name: Mapped[str] = mapped_column("userName", String(100))
    email: Mapped[str] = mapped_column(String(100))
    about: Mapped[str] = mapped_column(String(255))
    country: Mapped[str] = mapped_column(String(3))
    status: Mapped[str] = mapped_column(String(1))
    timestamp: Mapped[int] = mapped_column(Integer)
    last_access: Mapped[int] = mapped_column("lastAccess", Integer)
    pwd: Mapped[str] = mapped_column(String(255))
    avatar: Mapped[str] = mapped_column(String(200))
    settings: Mapped[str | None] = mapped_column(Text, nullable=True)

    __table_args__ = (
        Index("idx_users_user_name", "userName"),
        Index("idx_users_email", "email"),
    )


class Session(Base):
    __tablename__ = "sessions"

    id_session: Mapped[int] = mapped_column("idSession", Integer, primary_key=True, autoincrement=True)
    id_user: Mapped[int] = mapped_column("idUser", Integer)
    session_key: Mapped[str] = mapped_column("sessionKey", String(255))
    ip: Mapped[str] = mapped_column(String(25))
    last_access: Mapped[int] = mapped_column("lastAccess", Integer)

