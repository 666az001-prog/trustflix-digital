import { AlertTriangle, ArrowUpRight, CalendarClock, CircleDollarSign, UsersRound } from "lucide-react";
import Link from "next/link";
import { prisma } from "../lib/prisma";
import { date, money } from "../lib/format";

export const dynamic = "force-dynamic";

type DashboardData = { services: Array<{ id: string; name: string }>; payments: number; activeClients: number; expiring: Array<{ id: string; name: string; endDate: Date; service: { name: string }; slot: { identifier: string } | null }>; slots: number };

async function getDashboardData(serviceId?: string): Promise<DashboardData> {
  const empty: DashboardData = { services: [], payments: 0, activeClients: 0, expiring: [], slots: 0 };
  try {
    const services = await prisma.service.findMany({ orderBy: { name: "asc" } });
    const filter = serviceId ? { serviceId } : {};
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const horizon = new Date(today); horizon.setDate(horizon.getDate() + 3);
    const month = new Date(today.getFullYear(), today.getMonth(), 1);
    const [payments, activeClients, expiring, slots] = await Promise.all([
      prisma.payment.aggregate({ _sum: { amount: true }, where: { createdAt: { gte: month }, client: filter } }),
      prisma.client.count({ where: { ...filter, status: "ACTIVE", endDate: { gte: today } } }),
      prisma.client.findMany({ where: { ...filter, status: "ACTIVE", endDate: { gte: today, lte: horizon } }, include: { service: true, slot: true }, orderBy: { endDate: "asc" }, take: 6 }),
      prisma.slot.count({ where: { status: "FREE", account: serviceId ? { serviceId } : undefined } }),
    ]);
    return { services, payments: Number(payments._sum.amount ?? 0), activeClients, expiring, slots };
  } catch (error) {
    console.error("TrustFlix Digital dashboard error", error);
    return empty;
  }
}

export default async function Dashboard({ searchParams }: { searchParams: { service?: string } }) {
  const serviceId = searchParams.service;
  const { services, payments, activeClients, expiring, slots } = await getDashboardData(serviceId);
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const stats = [{ label: "Chiffre d’affaires ce mois", value: money.format(payments), note: "Encaissements enregistrés", icon: CircleDollarSign }, { label: "Clients actifs", value: String(activeClients), note: "Abonnements en cours", icon: UsersRound }, { label: "Slots disponibles", value: String(slots), note: "Prêts à être attribués", icon: ArrowUpRight }, { label: "Échéances sous 3 jours", value: String(expiring.length), note: "À relancer en priorité", icon: CalendarClock }];
  return <>
    <div className="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p className="text-sm text-amber-500">Vue d’ensemble</p><h1 className="mt-1 text-3xl font-bold tracking-tight">Bonjour, Administrateur</h1><p className="mt-2 text-sm text-zinc-400">Gardez le contrôle de votre parc d’abonnements.</p></div><Link className="btn-primary" href="/clients#new-client">Ajouter un client</Link></div>
    <div className="mb-7 flex flex-wrap gap-2"><Link className={`rounded-full px-4 py-2 text-sm ${!serviceId ? "bg-amber-500 text-zinc-950" : "bg-zinc-900 text-zinc-400"}`} href="/">Tous les services</Link>{services.map(service => <Link key={service.id} href={`/?service=${service.id}`} className={`rounded-full px-4 py-2 text-sm ${serviceId === service.id ? "bg-amber-500 text-zinc-950" : "bg-zinc-900 text-zinc-400"}`}>{service.name}</Link>)}</div>
    <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{stats.map(({ label, value, note, icon: Icon }) => <div className="card" key={label}><div className="mb-4 flex items-start justify-between"><span className="text-sm text-zinc-400">{label}</span><Icon size={19} className="text-amber-500"/></div><p className="text-2xl font-bold">{value}</p><p className="mt-2 text-xs text-zinc-500">{note}</p></div>)}</section>
    <section className="card mt-6"><div className="mb-5 flex items-center justify-between"><div><h2 className="font-semibold">Abonnements à échéance proche</h2><p className="mt-1 text-sm text-zinc-500">🔴 Aujourd’hui · 🟠 J-3</p></div><AlertTriangle className="text-amber-500" size={21}/></div>{expiring.length ? <div className="divide-y divide-zinc-800">{expiring.map(client => { const days = Math.round((client.endDate.getTime() - today.getTime()) / 86400000); const alert = days === 0 ? "🔴 Expire aujourd’hui" : `🟠 J-${days}`; return <div className="flex items-center justify-between gap-3 py-3" key={client.id}><div><p className="font-medium">{client.name} <span className="ml-2 text-xs text-zinc-500">{client.service.name}</span></p><p className="mt-1 text-xs text-zinc-500">{date.format(client.endDate)} · {client.slot?.identifier ?? "Slot non attribué"}</p></div><span className="badge bg-amber-500/15 text-amber-300">{alert}</span></div>; })}</div> : <p className="text-sm text-zinc-500">Aucune échéance urgente.</p>}</section>
  </>;
}
