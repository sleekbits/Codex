from datetime import datetime, UTC
from pathlib import Path

from fastapi import APIRouter, Depends, HTTPException
from fastapi.responses import FileResponse

from models.job import job_store
from routes.deps import require_token

router = APIRouter(prefix="/download", tags=["download"], dependencies=[Depends(require_token)])


@router.get("/{job_id}")
def download_job(job_id: str):
    try:
        job = job_store.get(job_id)
    except KeyError as exc:
        raise HTTPException(status_code=404, detail="Job not found") from exc

    if not job.output_path or not Path(job.output_path).exists():
        raise HTTPException(status_code=404, detail="Generated file not found")

    job.audit_events.append({"event": "download", "at": datetime.now(UTC).isoformat()})
    job_store.update(job)
    return FileResponse(job.output_path, filename=f"filled-{job.id}.pptx", media_type="application/vnd.openxmlformats-officedocument.presentationml.presentation")
