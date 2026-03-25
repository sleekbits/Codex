from pathlib import Path
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    api_title: str = "PPTX Autofill API"
    api_token: str = "change-me"
    temp_dir: str = "./tmp"
    max_upload_size_mb: int = 25
    file_ttl_minutes: int = 60
    log_level: str = "INFO"
    allow_notes: bool = True

    model_config = SettingsConfigDict(env_file=".env", env_file_encoding="utf-8")


settings = Settings()
Path(settings.temp_dir).mkdir(parents=True, exist_ok=True)
