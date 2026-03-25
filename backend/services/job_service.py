from __future__ import annotations

from datetime import datetime, UTC
from pathlib import Path
import shutil

from core.config import settings
from models.job import Job, JobState, job_store


def create_job(template_path: Path) -> Job:
    job = Job.new(template_path=template_path, ttl_minutes=settings.file_ttl_minutes)
    job.audit_events.append({"event": "upload", "at": datetime.now(UTC).isoformat()})
    return job_store.create(job)


def cleanup_expired_jobs() -> None:
    for job in job_store.all():
        if datetime.now(UTC) > job.expires_at:
            for p in [job.template_path, job.output_path]:
                if p and Path(p).exists():
                    Path(p).unlink(missing_ok=True)
            job_store.delete(job.id)


def delete_job_files(job: Job) -> None:
    for p in [job.template_path, job.output_path]:
        if p and Path(p).exists():
            Path(p).unlink(missing_ok=True)


def prepare_workspace(job_id: str) -> Path:
    workspace = Path(settings.temp_dir) / job_id
    workspace.mkdir(parents=True, exist_ok=True)
    return workspace


def cleanup_workspace(job_id: str) -> None:
    workspace = Path(settings.temp_dir) / job_id
    if workspace.exists():
        shutil.rmtree(workspace, ignore_errors=True)
