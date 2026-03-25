"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { StepNav } from "@/components/StepNav";
import { fillTemplate, parseTemplate } from "@/lib/api";
import { PlaceholderMeta } from "@/lib/types";

export default function FillPage() {
  const params = useSearchParams();
  const jobId = params.get("jobId") || "";
  const [fields, setFields] = useState<PlaceholderMeta[]>([]);
  const [answers, setAnswers] = useState<Record<string, string>>({});
  const router = useRouter();

  useEffect(() => {
    if (!jobId) return;
    parseTemplate(jobId, true).then((r) => setFields(r.placeholders));
  }, [jobId]);

  async function submit() {
    const res = await fillTemplate(jobId, answers);
    router.push(`/generate?jobId=${jobId}&download=${encodeURIComponent(res.downloadUrl)}`);
  }

  return (
    <div>
      <StepNav current={2} />
      <h2 className="text-xl font-semibold mb-4">Fill answers</h2>
      <div className="space-y-3">
        {fields.map((f) => (
          <div key={f.key}>
            <label className="block text-sm font-medium">{f.key}</label>
            {f.suggestedType === "longtext" ? (
              <textarea className="w-full border rounded p-2" onChange={(e) => setAnswers({ ...answers, [f.key]: e.target.value })} />
            ) : (
              <input
                className="w-full border rounded p-2"
                type={f.suggestedType === "date" ? "date" : f.suggestedType === "number" ? "number" : "text"}
                onChange={(e) => setAnswers({ ...answers, [f.key]: e.target.value })}
              />
            )}
          </div>
        ))}
      </div>
      <button onClick={submit} className="mt-4 px-3 py-1 rounded bg-blue-600 text-white">
        Generate PPTX
      </button>
    </div>
  );
}
