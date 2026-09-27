"use client";

import { useFormState } from "react-dom";
import { createAccount, type AccountActionState } from "../actions";
import { SubmitButton } from "../../components/submit-button";

type Service = { id: string; name: string };

export function NewAccountForm({ services }: { services: Service[] }) {
  const [state, formAction] = useFormState<AccountActionState, FormData>(createAccount, {});

  return <>
    {state.error && <p role="alert" className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{state.error}</p>}
    {state.success && <p role="status" className="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{state.success}</p>}
    <form action={formAction} className="grid gap-4 md:grid-cols-3">
      <label><span className="label">Service</span><select name="serviceId" required>{services.map(service => <option value={service.id} key={service.id}>{service.name}</option>)}</select></label>
      <label><span className="label">E-mail du compte</span><input name="email" type="email" placeholder="compte@exemple.com" required/></label>
      <label><span className="label">Mot de passe</span><input name="password" type="password" minLength={8} required/></label>
      <label><span className="label">Date de renouvellement</span><input name="renewalDate" type="date" required/></label>
      <div className="flex items-end"><SubmitButton>Créer le compte</SubmitButton></div>
    </form>
  </>;
}