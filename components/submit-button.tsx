"use client";
import { useFormStatus } from "react-dom";
export function SubmitButton({ children, disabled = false }: { children: React.ReactNode; disabled?: boolean }) { const { pending } = useFormStatus(); return <button className="btn-primary" disabled={pending || disabled}>{pending ? "Enregistrement…" : children}</button>; }
