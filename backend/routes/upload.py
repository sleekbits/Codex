from pathlib import Path
from uuid import uuid4

from fastapi import APIRouter, Depends, File, HTTPException, UploadFile

from core.config import settings
from routes.deps import require_token
from services.job_service import create_job, prepare_workspace

router = APIRouter(prefix="/upload", tags=["upload"], dependencies=[Depends(require_token)])


@router.post("")
async def upload_template(file: UploadFile = File(...)):
    if not file.filename.lower().endswith(".pptx"):
        raise HTTPException(status_code=400, detail="Only .pptx files are supported")

    data = await file.read()
    if len(data) > settings.max_upload_size_mb * 1024 * 1024:
        raise HTTPException(status_code=400, detail="File too large")

    job_id = uuid4().hex
    workspace = prepare_workspace(job_id)
    template_path = workspace / "template.pptx"
    template_path.write_bytes(data)

    job = create_job(template_path)
    return {"jobId": job.id, "state": job.state}
