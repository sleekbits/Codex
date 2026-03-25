from datetime import datetime, UTC

from fastapi import APIRouter, Depends, HTTPException, Query

from models.job import JobState, job_store
from routes.deps import require_token
from services.pptx_parser import parse_pptx_placeholders

router = APIRouter(prefix="/parse", tags=["parse"], dependencies=[Depends(require_token)])


@router.post("/{job_id}")
def parse_job(job_id: str, include_notes: bool = Query(default=False)):
    try:
        job = job_store.get(job_id)
    except KeyError as exc:
        raise HTTPException(status_code=404, detail="Job not found") from exc

    placeholders = parse_pptx_placeholders(job.template_path, include_notes=include_notes)
    job.placeholders = placeholders
    job.state = JobState.awaiting_input if placeholders else JobState.parsed
    job.audit_events.append({"event": "parse", "at": datetime.now(UTC).isoformat()})
    job_store.update(job)

    return {"jobId": job.id, "state": job.state, "placeholders": [p.model_dump() for p in placeholders]}
