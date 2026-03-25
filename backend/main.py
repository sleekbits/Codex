from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from core.config import settings
from routes import download, fill, jobs, parse, upload

app = FastAPI(title=settings.api_title)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(upload.router)
app.include_router(parse.router)
app.include_router(fill.router)
app.include_router(download.router)
app.include_router(jobs.router)


@app.get("/health")
def healthcheck():
    return {"status": "ok"}
