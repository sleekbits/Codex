"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { StepNav } from "@/components/StepNav";
import { uploadTemplate } from "@/lib/api";

export default function UploadPage() {
  const [file, setFile] = useState<File | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const router = useRouter();

  async function submit() {
    if (!file) return;
    setLoading(true);
    setError("");
    try {
      const res = await uploadTemplate(file);
      router.push(`/review?jobId=${res.jobId}`);
    } catch (e) {
      setError((e as Error).message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div>
      <StepNav current={0} />
      <h2 className="text-xl font-semibold mb-4">Upload template</h2>
      <input type="file" accept=".pptx" onChange={(e) => setFile(e.target.files?.[0] ?? null)} />
      <button onClick={submit} disabled={!file || loading} className="ml-2 px-3 py-1 rounded bg-blue-600 text-white">
        {loading ? "Uploading..." : "Continue"}
      </button>
      {error && <p className="text-red-600 mt-3">{error}</p>}
    </div>
  );
}
