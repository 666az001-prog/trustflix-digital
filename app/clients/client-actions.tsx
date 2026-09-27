"use client";
import { useTransition } from "react";
import { Trash2 } from "lucide-react";
import { deleteClient, releaseClient } from "../actions";
export function ReleaseButton({ id }: { id: string }) { const [pending, startTransition] = useTransition(); const run = (mode: "release" | "delete") => { if (confirm(mode === "release" ? "Expirer ce client et libérer son slot ?" : "Supprimer définitivement ce client et libérer son slot ?")) startTransition(() => mode === "release" ? releaseClient(id) : deleteClient(id)); }; return <div className="flex gap-2"><button className="text-xs text-zinc-500 hover:text-amber-300" disabled={pending} onClick={() => run("release")}>{pending ? "…" : "Expirer"}</button><button title="Supprimer le client" aria-label="Supprimer le client" className="inline-flex items-center gap-1 text-xs text-zinc-500 hover:text-red-300" disabled={pending} onClick={() => run("delete")}><Trash2 size={14}/>{pending ? "…" : "Supprimer"}</button></div>; }
