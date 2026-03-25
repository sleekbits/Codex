from fastapi import APIRouter, Depends, HTTPException

from models.job import job_store
from routes.deps import require_token

router = APIRouter(prefix="/jobs", tags=["jobs"], dependencies=[Depends(require_token)])


@router.get("/{job_id}")
def get_job(job_id: str):
    try:
        job = job_store.get(job_id)
    except KeyError as exc:
        raise HTTPException(status_code=404, detail="Job not found") from exc

    return {
        "jobId": job.id,
        "state": job.state,
        "error": job.error,
        "placeholders": [p.model_dump() for p in job.placeholders],
        "auditEvents": job.audit_events,
        "expiresAt": job.expires_at,
    }
