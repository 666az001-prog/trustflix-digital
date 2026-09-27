import { MessageCircle } from "lucide-react";
import { prisma } from "../../lib/prisma";
import { date, money, whatsappUrl } from "../../lib/format";
import { ExpiryBadge } from "../../components/expiry-badge";
import { InstantSearch } from "../../components/instant-search";
import { NewClientForm } from "./new-client-form";
import { ReleaseButton } from "./client-actions";

export const dynamic = "force-dynamic";

export default async function ClientsPage() {
  const [services, clients] = await Promise.all([
    prisma.service.findMany({ orderBy: { name: "asc" } }),
    prisma.client.findMany({ include: { service: true, slot: true }, orderBy: { createdAt: "desc" } }),
  ]);

  return <>
    <div className="mb-8">
      <p className="text-sm text-amber-500">Relation client</p>
      <h1 className="mt-1 text-3xl font-bold">Clients</h1>
      <p className="mt-2 text-sm text-zinc-400">Un profil ou une place libre est attribué automatiquement au service choisi.</p>
    </div>
    <NewClientForm services={services}/>
    <section className="card overflow-x-auto">
      <div className="mb-5"><InstantSearch rowSelector=".client-row" placeholder="Rechercher un client, service ou numéro…"/></div>
      <table className="w-full min-w-[720px] text-left text-sm">
        <thead className="border-b border-zinc-800 text-xs uppercase tracking-wider text-zinc-500"><tr><th className="pb-3 font-medium">Client</th><th className="pb-3 font-medium">Service / slot</th><th className="pb-3 font-medium">Échéance</th><th className="pb-3 font-medium">Prix</th><th className="pb-3 font-medium">Statut</th><th className="pb-3"></th></tr></thead>
        <tbody className="divide-y divide-zinc-800">
          {clients.map(client => <tr className="client-row" key={client.id}>
            <td className="py-4 font-medium">{client.name}<div className="mt-1 text-xs font-normal text-zinc-500">{client.whatsapp}</div></td>
            <td className="py-4 text-zinc-300">{client.service.name}<div className="mt-1 text-xs text-zinc-500">{client.slot?.identifier ?? "Attribution en cours"}</div></td>
            <td className="py-4 text-zinc-400"><div>{date.format(client.endDate)}</div><ExpiryBadge endDate={client.endDate}/></td>
            <td className="py-4">{money.format(Number(client.price))}</td>
            <td className="py-4"><span className={`badge ${client.status === "ACTIVE" ? "bg-emerald-500/15 text-emerald-300" : "bg-zinc-800 text-zinc-400"}`}>{client.status === "ACTIVE" ? "Actif" : "Expiré"}</span></td>
            <td className="py-4"><div className="flex items-center gap-3"><a title="Relancer sur WhatsApp" className="text-emerald-400 hover:text-emerald-300" href={whatsappUrl(client.whatsapp, client.name, client.service.name, client.endDate)} target="_blank" rel="noreferrer"><MessageCircle size={17}/></a><ReleaseButton id={client.id}/></div></td>
          </tr>)}
        </tbody>
      </table>
    </section>
  </>;
}
