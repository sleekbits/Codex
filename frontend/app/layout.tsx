import "./globals.css";
import type { ReactNode } from "react";

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en">
      <body>
        <main className="max-w-5xl mx-auto p-8">{children}</main>
      </body>
    </html>
  );
}
