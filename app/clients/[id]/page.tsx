import Link from "next/link";
import { ArrowLeft, CreditCard, UserRound } from "lucide-react";
import { notFound } from "next/navigation";
import { prisma } from "../../../lib/prisma";
import { date, money } from "../../../lib/format";
import { PinManager } from "./pin-manager";

export const dynamic = "force-dynamic";

type ClientProfilePageProps = { params: { id: string } };

export default async function ClientProfilePage({ params }: ClientProfilePageProps) {
  const client = await prisma.client.findUnique({
    where: { id: params.id },
    include: {
      service: true,
      slot: { include: { account: true } },
      payments: { orderBy: { createdAt: "desc" } },
    },
  });
  if (!client) notFound();

  return <>
    <Link href="/clients" className="mb-6 inline-flex items-center gap-2 text-sm text-zinc-400 hover:text-white"><ArrowLeft size={16}/>Retour aux clients</Link>
    <div className="mb-8"><p className="text-sm text-amber-500">Fiche client</p><h1 className="mt-1 text-3xl font-bold">{client.name}</h1><p className="mt-2 text-sm text-zinc-400">{client.service.name} · {client.status === "ACTIVE" ? "Actif" : "Expiré"}</p></div>
    <div className="grid gap-5 lg:grid-cols-2">
      <section className="card">
        <div className="mb-4 flex items-center gap-2"><UserRound size={18} className="text-amber-500"/><h2 className="font-semibold">Informations générales</h2></div>
        <dl className="grid gap-4 sm:grid-cols-2">
          <div><dt className="label">Nom complet</dt><dd>{client.name}</dd></div>
          <div><dt className="label">WhatsApp</dt><dd>{client.whatsapp}</dd></div>
          <div><dt className="label">Service</dt><dd>{client.service.name}</dd></div>
          <div><dt className="label">Prix mensuel</dt><dd>{money.format(Number(client.price))}</dd></div>
          <div><dt className="label">Début</dt><dd>{date.format(client.startDate)}</dd></div>
          <div><dt className="label">Échéance</dt><dd>{date.format(client.endDate)}</dd></div>
        </dl>
      </section>
      <section className="card">
        <h2 className="mb-4 font-semibold">Compte et place attribués</h2>
        {client.slot ? <dl className="grid gap-4 sm:grid-cols-2">
          <div><dt className="label">Compte</dt><dd>{client.slot.account.email}</dd></div>
          <div><dt className="label">Renouvellement du compte</dt><dd>{date.format(client.slot.account.renewalDate)}</dd></div>
          <div><dt className="label">Place</dt><dd>{client.slot.identifier}</dd></div>
          <div><dt className="label">État de la place</dt><dd>{client.slot.status === "FREE" ? "Libre" : "Occupée"}</dd></div>
        </dl> : <p className="text-sm text-zinc-400">Aucun compte ou slot n’est actuellement attribué.</p>}
      </section>
      {client.slot && <PinManager clientId={client.id} hasPin={Boolean(client.slot.pinCodeEncrypted)}/>}
      <section className="card lg:col-span-2">
        <div className="mb-4 flex items-center gap-2"><CreditCard size={18} className="text-amber-500"/><h2 className="font-semibold">Historique des paiements</h2></div>
        {client.payments.length === 0 ? <p className="text-sm text-zinc-500">Aucun paiement enregistré.</p> : <div className="overflow-x-auto"><table className="w-full min-w-[420px] text-left text-sm"><thead className="border-b border-zinc-800 text-xs uppercase tracking-wider text-zinc-500"><tr><th className="pb-3 font-medium">Date</th><th className="pb-3 font-medium">Référence</th><th className="pb-3 text-right font-medium">Montant</th></tr></thead><tbody className="divide-y divide-zinc-800">{client.payments.map(payment => <tr key={payment.id}><td className="py-3 text-zinc-400">{date.format(payment.createdAt)}</td><td className="py-3">Paiement reçu</td><td className="py-3 text-right font-semibold text-emerald-300">{money.format(Number(payment.amount))}</td></tr>)}</tbody></table></div>}
      </section>
    </div>
  </>;
}
