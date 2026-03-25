const steps = ["Upload", "Review", "Fill", "Generate"];

export function StepNav({ current }: { current: number }) {
  return (
    <div className="flex gap-2 mb-6">
      {steps.map((s, i) => (
        <div key={s} className={`px-3 py-1 rounded text-sm ${i <= current ? "bg-blue-600 text-white" : "bg-slate-200"}`}>
          {i + 1}. {s}
        </div>
      ))}
    </div>
  );
}
