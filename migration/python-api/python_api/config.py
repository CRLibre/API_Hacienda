from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env", env_prefix="API_HACIENDA_")

    env: str = "development"
    log_level: str = "INFO"
    php_fallback_url: str | None = "http://localhost:8080/api.php"
    request_timeout_seconds: float = 30.0
    database_url: str = "mysql+pymysql://testuser:testpassword@localhost:4407/testdb"
    crypto_key: str = ""
    users_session_lifetime: int = -1
    files_base_path: str = "/var/www/api/files/"
    cron_token: str = "ItIsGoodIfThisIsBigAndHasW3irDLeeT3rsAnd$ymb0lz.IniT"
    core_site_name: str = "Mi Sitio"
    mail_noreply: str = "no-reply@crlibre.org"
    mail_type: str = "mail"
    mail_address: str = ""
    mail_host: str = ""
    mail_username: str = ""
    mail_password: str = ""
    mail_secure: str = "tls"
    mail_port: int = 587


@lru_cache
def get_settings() -> Settings:
    return Settings()
