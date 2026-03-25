import Link from "next/link";

export default function Home() {
  return (
    <div>
      <h1 className="text-2xl font-bold mb-2">PPTX Autofill</h1>
      <p className="mb-6">Upload, detect placeholders, complete a generated form, and download a filled deck.</p>
      <Link className="bg-blue-600 text-white px-4 py-2 rounded" href="/upload">
        Start workflow
      </Link>
    </div>
  );
}
