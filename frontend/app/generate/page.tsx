"use client";

import { useSearchParams } from "next/navigation";
import { StepNav } from "@/components/StepNav";
import { downloadUrl } from "@/lib/api";

export default function GeneratePage() {
  const params = useSearchParams();
  const path = params.get("download") || "";

  return (
    <div>
      <StepNav current={3} />
      <h2 className="text-xl font-semibold mb-4">Done</h2>
      <a href={downloadUrl(path)} className="bg-emerald-600 text-white px-4 py-2 rounded">
        Download generated PPTX
      </a>
    </div>
  );
}
