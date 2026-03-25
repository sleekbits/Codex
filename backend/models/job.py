from __future__ import annotations

from datetime import datetime, timedelta, UTC
from enum import Enum
from pathlib import Path
from threading import Lock
from typing import Any
from uuid import uuid4

from pydantic import BaseModel, Field


class JobState(str, Enum):
    uploaded = "uploaded"
    parsed = "parsed"
    awaiting_input = "awaiting_input"
    generating = "generating"
    complete = "complete"
    failed = "failed"


class PlaceholderMeta(BaseModel):
    key: str
    format: str
    slideIndex: int
    shapeName: str | None = None
    context: str
    suggestedType: str = "string"


class Job(BaseModel):
    id: str = Field(default_factory=lambda: uuid4().hex)
    state: JobState = JobState.uploaded
    template_path: str
    output_path: str | None = None
    placeholders: list[PlaceholderMeta] = Field(default_factory=list)
    created_at: datetime = Field(default_factory=lambda: datetime.now(UTC))
    expires_at: datetime
    error: str | None = None
    audit_events: list[dict[str, Any]] = Field(default_factory=list)

    @classmethod
    def new(cls, template_path: Path, ttl_minutes: int) -> "Job":
        now = datetime.now(UTC)
        return cls(
            template_path=str(template_path),
            expires_at=now + timedelta(minutes=ttl_minutes),
        )


class JobStore:
    def __init__(self) -> None:
        self._jobs: dict[str, Job] = {}
        self._lock = Lock()

    def create(self, job: Job) -> Job:
        with self._lock:
            self._jobs[job.id] = job
        return job

    def get(self, job_id: str) -> Job:
        return self._jobs[job_id]

    def all(self) -> list[Job]:
        return list(self._jobs.values())

    def update(self, job: Job) -> None:
        with self._lock:
            self._jobs[job.id] = job

    def delete(self, job_id: str) -> None:
        with self._lock:
            self._jobs.pop(job_id, None)


job_store = JobStore()
