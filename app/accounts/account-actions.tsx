"use client";

import { Trash2 } from "lucide-react";
import { useTransition } from "react";
import { deleteAccount } from "../actions";

export function DeleteAccountButton({ id }: { id: string }) {
  const [pending, startTransition] = useTransition();

  function handleDelete() {
    const confirmed = window.confirm("Supprimer ce compte ? Les clients associés seront expirés et leurs slots libérés.");
    if (confirmed) startTransition(() => deleteAccount(id));
  }

  return <button type="button" className="btn-secondary text-red-300 hover:border-red-500/50 hover:bg-red-500/10" disabled={pending} onClick={handleDelete}>
    <Trash2 size={15}/>{pending ? "Suppression…" : "Supprimer le compte"}
  </button>;
}
