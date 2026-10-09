"use client";

import { useState, useTransition } from "react";
import { Eye, EyeOff, KeyRound } from "lucide-react";
import { getClientPin, updateClientPin } from "../../actions";

export function PinManager({ clientId, hasPin: initialHasPin }: { clientId: string; hasPin: boolean }) {
  const [hasPin, setHasPin] = useState(initialHasPin);
  const [pinCode, setPinCode] = useState("");
  const [newPin, setNewPin] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [pending, startTransition] = useTransition();

  function revealPin() {
    setError("");
    startTransition(async () => {
      const result = await getClientPin(clientId);
      if (result.error) setError(result.error);
      else setPinCode(result.pinCode ?? "");
    });
  }

  function savePin(formData: FormData) {
    const value = String(formData.get("pinCode") ?? "");
    setError("");
    setMessage("");
    startTransition(async () => {
      const result = await updateClientPin(clientId, value);
      if (result.error) setError(result.error);
      else {
        setMessage(result.success ?? "Code PIN mis à jour.");
        setHasPin(true);
        setPinCode(value);
        setNewPin("");
      }
    });
  }

  return <section className="card">
    <div className="mb-4 flex items-center gap-2"><KeyRound size={18} className="text-amber-500"/><h2 className="font-semibold">Code PIN du profil</h2></div>
    <div className="mb-4 flex items-center gap-3">
      <span className="min-w-24 font-mono text-lg tracking-widest">{pinCode || (hasPin ? "••••" : "Non défini")}</span>
      {pinCode ? <button type="button" className="btn-secondary px-3 py-2" title="Masquer le code PIN" aria-label="Masquer le code PIN" onClick={() => setPinCode("")}><EyeOff size={16}/></button> : <button type="button" className="btn-secondary px-3 py-2" title="Afficher le code PIN" aria-label="Afficher le code PIN" disabled={pending || !hasPin} onClick={revealPin}><Eye size={16}/></button>}
    </div>
    <form action={savePin} className="flex flex-col gap-3 sm:flex-row sm:items-end">
      <label className="flex-1"><span className="label">{hasPin ? "Remplacer le code PIN" : "Définir le code PIN"}</span><input name="pinCode" type="password" inputMode="numeric" pattern="[0-9]{4}" minLength={4} maxLength={4} autoComplete="new-password" value={newPin} onChange={event => setNewPin(event.target.value)} required/></label>
      <button className="btn-primary" type="submit" disabled={pending || newPin.length !== 4}>{pending ? "Enregistrement…" : "Enregistrer"}</button>
    </form>
    {error && <p role="alert" className="mt-3 text-sm text-red-300">{error}</p>}
    {message && <p role="status" className="mt-3 text-sm text-emerald-300">{message}</p>}
  </section>;
}