from datetime import datetime, UTC
from pathlib import Path

from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel

from core.config import settings
from models.job import JobState, job_store
from routes.deps import require_token
from services.pptx_filler import fill_pptx

router = APIRouter(prefix="/fill", tags=["fill"], dependencies=[Depends(require_token)])


class FillPayload(BaseModel):
    answers: dict[str, str]
    keepUnanswered: bool = False


@router.post("/{job_id}")
def fill_job(job_id: str, payload: FillPayload):
    try:
        job = job_store.get(job_id)
    except KeyError as exc:
        raise HTTPException(status_code=404, detail="Job not found") from exc

    try:
        job.state = JobState.generating
        output = Path(settings.temp_dir) / job.id / "output.pptx"
        job.output_path = fill_pptx(
            template_path=job.template_path,
            output_path=str(output),
            values=payload.answers,
            keep_unanswered=payload.keepUnanswered,
        )
        job.state = JobState.complete
        job.audit_events.append({"event": "generate", "at": datetime.now(UTC).isoformat()})
        job_store.update(job)
    except Exception as exc:  # noqa: BLE001
        job.state = JobState.failed
        job.error = str(exc)
        job_store.update(job)
        raise HTTPException(status_code=500, detail="Failed generating file") from exc

    return {"jobId": job.id, "state": job.state, "downloadUrl": f"/download/{job.id}"}
