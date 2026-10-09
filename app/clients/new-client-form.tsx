"use client";

import { useMemo, useState } from "react";
import { useFormState } from "react-dom";
import { UserPlus } from "lucide-react";
import { createClient, type ClientActionState } from "../actions";
import { SubmitButton } from "../../components/submit-button";

type Service = { id: string; name: string };
type AccountOption = { id: string; serviceId: string; email: string; freeSlots: number };

export function NewClientForm({ services, accounts }: { services: Service[]; accounts: AccountOption[] }) {
  const [state, formAction] = useFormState<ClientActionState, FormData>(createClient, {});
  const [serviceId, setServiceId] = useState(services[0]?.id ?? "");
  const [accountId, setAccountId] = useState("");
  const matchingAccounts = useMemo(() => accounts.filter(account => account.serviceId === serviceId), [accounts, serviceId]);

  function changeService(value: string) {
    setServiceId(value);
    setAccountId("");
  }

  return <section id="new-client" className="card mb-6">
    <div className="mb-5 flex items-center gap-2"><UserPlus size={18} className="text-amber-500"/><div><h2 className="font-semibold">Attribuer un client</h2><p className="mt-1 text-xs text-zinc-500">Choisissez le compte maître et une de ses places libres sera attribuée.</p></div></div>
    {state.error && <p role="alert" className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{state.error}</p>}
    <form action={formAction} className="grid gap-4 md:grid-cols-3">
      <label><span className="label">Nom complet</span><input name="name" minLength={2} required/></label>
      <label><span className="label">WhatsApp +225</span><input name="whatsapp" placeholder="+225 07 xx xx xx xx" pattern="\+?225[0-9 ]{8,}" required/></label>
      <label><span className="label">Service</span><select name="serviceId" value={serviceId} onChange={event => changeService(event.target.value)} required>{services.map(service => <option value={service.id} key={service.id}>{service.name}</option>)}</select></label>
      <label><span className="label">Compte maître</span><select name="accountId" value={accountId} onChange={event => setAccountId(event.target.value)} required disabled={matchingAccounts.length === 0}><option value="" disabled>{matchingAccounts.length ? "Choisir un compte" : "Aucun compte avec une place libre"}</option>{matchingAccounts.map(account => <option value={account.id} key={account.id}>{account.email} · {account.freeSlots} place{account.freeSlots > 1 ? "s" : ""} libre{account.freeSlots > 1 ? "s" : ""}</option>)}</select></label>
      <label><span className="label">Prix mensuel (F CFA)</span><input name="price" type="number" step="1" min="1" required/></label>
      <label><span className="label">Code PIN (4 chiffres)</span><input name="pinCode" type="password" inputMode="numeric" pattern="[0-9]{4}" minLength={4} maxLength={4} autoComplete="new-password" required/></label>
      <div className="flex items-end"><SubmitButton disabled={!accountId}>Attribuer au compte choisi</SubmitButton></div>
    </form>
  </section>;
}