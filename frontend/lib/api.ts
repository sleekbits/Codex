import { PlaceholderMeta } from "./types";

const API = process.env.NEXT_PUBLIC_API_BASE ?? "http://localhost:8000";
const TOKEN = process.env.NEXT_PUBLIC_API_TOKEN ?? "change-me";

async function req(path: string, init?: RequestInit) {
  const res = await fetch(`${API}${path}`, {
    ...init,
    headers: {
      "Content-Type": "application/json",
      "x-api-token": TOKEN,
      ...(init?.headers ?? {}),
    },
    cache: "no-store",
  });

  if (!res.ok) throw new Error(await res.text());
  return res;
}

export async function uploadTemplate(file: File) {
  const form = new FormData();
  form.append("file", file);
  const res = await fetch(`${API}/upload`, {
    method: "POST",
    body: form,
    headers: { "x-api-token": TOKEN },
  });
  if (!res.ok) throw new Error(await res.text());
  return res.json() as Promise<{ jobId: string; state: string }>;
}

export async function parseTemplate(jobId: string, includeNotes = false) {
  const res = await req(`/parse/${jobId}?include_notes=${includeNotes}`, { method: "POST" });
  return res.json() as Promise<{ placeholders: PlaceholderMeta[] }>;
}

export async function fillTemplate(jobId: string, answers: Record<string, string>) {
  const res = await req(`/fill/${jobId}`, {
    method: "POST",
    body: JSON.stringify({ answers, keepUnanswered: false }),
  });
  return res.json() as Promise<{ downloadUrl: string }>;
}

export function downloadUrl(path: string) {
  return `${API}${path}`;
}
