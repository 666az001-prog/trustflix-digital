import { Plus, ShieldCheck } from "lucide-react";
import type { Prisma } from "@prisma/client";
import { createAccount } from "../actions";
import { prisma } from "../../lib/prisma";
import { date } from "../../lib/format";
import { SubmitButton } from "../../components/submit-button";
import { InstantSearch } from "../../components/instant-search";
import { DeleteAccountButton } from "./account-actions";
import { serviceCatalog } from "../../lib/services";

export const dynamic = "force-dynamic";

export default async function AccountsPage() {
  let services: Array<{ id: string; name: string }> = serviceCatalog.map(({ id, name }) => ({ id, name }));
  let accounts: Prisma.AccountGetPayload<{ include: { service: true; slots: { orderBy: { identifier: "asc" } } } }>[] = [];
  try {
    const [databaseServices, loadedAccounts] = await Promise.all([
      prisma.service.findMany({ orderBy: { name: "asc" } }),
      prisma.account.findMany({ include: { service: true, slots: { orderBy: { identifier: "asc" } } }, orderBy: { createdAt: "desc" } }),
    ]);
    services = databaseServices.length > 0 ? databaseServices : services;
    accounts = loadedAccounts;
  } catch (error) {
    console.error("TrustFlix Digital accounts error", error);
  }

  return <>
    <div className="mb-8"><p className="text-sm text-amber-500">Inventaire</p><h1 className="mt-1 text-3xl font-bold">Comptes maîtres</h1><p className="mt-2 text-sm text-zinc-400">Chaque compte génère automatiquement les places prévues par son service.</p></div>
    <section className="card mb-6">
      <div className="mb-5 flex items-center gap-2"><Plus size={18} className="text-amber-500"/><h2 className="font-semibold">Nouveau compte</h2></div>
      <form action={createAccount} className="grid gap-4 md:grid-cols-3">
        <label><span className="label">Service</span><select name="serviceId" required>{services.map(service => <option value={service.id} key={service.id}>{service.name}</option>)}</select></label>
        <label><span className="label">E-mail du compte</span><input name="email" type="email" placeholder="compte@exemple.com" required/></label>
        <label><span className="label">Mot de passe</span><input name="password" type="password" minLength={8} required/></label>
        <label><span className="label">Date de renouvellement</span><input name="renewalDate" type="date" required/></label>
        <div className="flex items-end"><SubmitButton>Créer le compte</SubmitButton></div>
      </form>
      <p className="mt-4 flex items-center gap-2 text-xs text-zinc-500"><ShieldCheck size={14}/>Les mots de passe et codes PIN sont chiffrés au repos avec AES-256-GCM.</p>
    </section>
    <div className="mb-4"><InstantSearch rowSelector=".account-row" placeholder="Rechercher un compte ou service…"/></div>
    <div className="grid gap-4 lg:grid-cols-2">
      {accounts.map(account => <article className="account-row card" key={account.id}>
        <div className="flex items-start justify-between gap-3"><div><span className="badge bg-zinc-800 text-zinc-300">{account.service.name}</span><h2 className="mt-3 font-semibold">{account.email}</h2><p className="mt-1 text-xs text-zinc-500">Renouvellement : {date.format(account.renewalDate)}</p></div><DeleteAccountButton id={account.id}/></div>
        <div className="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3">{account.slots.map(slot => <div className="rounded-xl border border-zinc-800 bg-zinc-950/60 p-3" key={slot.id}><p className="text-sm font-medium">{slot.identifier}</p><p className={`mt-1 text-xs ${slot.status === "FREE" ? "text-emerald-400" : "text-orange-300"}`}>{slot.status === "FREE" ? "Libre" : "Occupé"}</p></div>)}</div>
      </article>)}
    </div>
  </>;
}
