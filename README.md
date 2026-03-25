# PPTX Autofill Web Application

Production-ready starter for automating placeholder-based PowerPoint generation.

## Stack
- **Frontend:** Next.js 15 (App Router), TypeScript, Tailwind
- **Backend:** FastAPI, python-pptx
- **Async-ready:** in-memory jobs now, Redis/Celery scaffold included

## Project Structure

```text
/backend
  /core/config.py
  /models/job.py
  /routes/upload.py
  /routes/parse.py
  /routes/fill.py
  /routes/download.py
  /services/pptx_parser.py
  /services/pptx_filler.py
  /tests
/frontend
  /app/upload
  /app/review
  /app/fill
  /app/generate
docker-compose.yml
.env.example
```

## Features
1. Upload `.pptx` template with validation and TTL-based temporary storage.
2. Detect placeholders in text frames, tables, and optional speaker notes.
3. Supports `{{key}}`, `[[key]]`, `<<key>>`, including nested keys like `project.title`.
4. Dynamic 4-step UI flow:
   - Upload
   - Review fields + mapping context
   - Fill answers form (typed controls)
   - Generate + download
5. Replaces placeholders in text and tables while preserving slide-level structure.
6. Job lifecycle states: `uploaded -> parsed -> awaiting_input -> generating -> complete|failed`.
7. Token-based API auth via `x-api-token` header.
8. Audit log events for upload, parse, generate, download.
9. Repeating sections scaffolded as v2 through token sanitizer support for section syntax (`#` and `/`).

## Backend Run (local)

```bash
cd backend
python -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp ../.env.example .env
uvicorn main:app --reload
```

API docs: http://localhost:8000/docs

## Frontend Run (local)

```bash
cd frontend
npm install
npm run dev
```

UI: http://localhost:3000

## Docker

```bash
cp .env.example .env
docker compose up --build
```

## Tests

```bash
cd backend
pytest
```

Covers:
- Placeholder extraction (including split-token runs)
- Placeholder replacement in text boxes and table cells

## API Flow
1. `POST /upload` -> returns `jobId`
2. `POST /parse/{jobId}?include_notes=true` -> placeholder metadata JSON
3. `POST /fill/{jobId}` with answers map -> returns `/download/{jobId}` URL
4. `GET /download/{jobId}` -> generated PPTX file

## Security & Compliance
- Files saved in temp workspace with TTL cleanup hooks.
- Placeholder keys sanitized to allow only safe chars.
- No document content in audit logs.

## Known limitations
- For split placeholders across many runs, replacement is best-effort by collapsing paragraph runs into first run (preserves paragraph but not full per-run styling granularity).
- Repeating row sections (`{{#collection}}...{{/collection}}`) are scaffolded as v2 only.
