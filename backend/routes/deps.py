from fastapi import Header, HTTPException

from core.config import settings


def require_token(x_api_token: str = Header(default="")) -> None:
    if settings.api_token and x_api_token != settings.api_token:
        raise HTTPException(status_code=401, detail="Unauthorized")
