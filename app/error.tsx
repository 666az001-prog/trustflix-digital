"use client";

import { useEffect } from "react";

export default function Error({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => {
    console.error("TrustFlix Digital error", error);
  }, [error]);

  return <main className="grid min-h-screen place-items-center bg-canvas px-6 text-center text-zinc-100">
    <div className="card max-w-md">
      <p className="text-sm text-amber-500">TrustFlix Digital</p>
      <h1 className="mt-2 text-2xl font-bold">Une erreur est survenue</h1>
      <p className="mt-3 text-sm text-zinc-400">La page n’a pas pu être chargée. Réessayez pour reprendre votre activité.</p>
      <button className="btn-primary mt-6" onClick={() => reset()}>Réessayer</button>
    </div>
  </main>;
}
