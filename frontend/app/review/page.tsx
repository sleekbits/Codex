"use client";

import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { StepNav } from "@/components/StepNav";
import { parseTemplate } from "@/lib/api";
import { PlaceholderMeta } from "@/lib/types";

export default function ReviewPage() {
  const params = useSearchParams();
  const jobId = params.get("jobId") || "";
  const [fields, setFields] = useState<PlaceholderMeta[]>([]);
  const router = useRouter();

  useEffect(() => {
    if (!jobId) return;
    parseTemplate(jobId, true).then((r) => setFields(r.placeholders));
  }, [jobId]);

  return (
    <div>
      <StepNav current={1} />
      <h2 className="text-xl font-semibold mb-4">Review detected fields</h2>
      <div className="space-y-3">
        {fields.map((f) => (
          <div key={`${f.key}-${f.slideIndex}`} className="bg-white p-3 rounded border">
            <div className="font-semibold">{f.key}</div>
            <div className="text-sm text-slate-600">Slide {f.slideIndex} · {f.shapeName} · type: {f.suggestedType}</div>
            <div className="text-sm mt-1">{f.context}</div>
          </div>
        ))}
      </div>
      <button
        onClick={() => router.push(`/fill?jobId=${jobId}`)}
        className="mt-4 px-3 py-1 rounded bg-blue-600 text-white"
      >
        Continue to form
      </button>
    </div>
  );
}
