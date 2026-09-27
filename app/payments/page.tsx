import { ArrowDownRight, ArrowUpRight, WalletCards } from "lucide-react";
import { prisma } from "../../lib/prisma";
import { date, money } from "../../lib/format";
import { SubmitButton } from "../../components/submit-button";
import { createExpense } from "../actions";

export const dynamic = "force-dynamic";

type Transaction = { id: string; type: "income" | "expense"; label: string; detail: string; amount: number; createdAt: Date };

export default async function PaymentsPage() {
  let transactions: Transaction[] = [];
  let income = 0;
  let outgoings = 0;
  try {
    const [payments, expenses, incomeTotal, expenseTotal] = await Promise.all([
      prisma.payment.findMany({ include: { client: { include: { service: true } } }, orderBy: { createdAt: "desc" } }),
      prisma.expense.findMany({ orderBy: { createdAt: "desc" } }),
      prisma.payment.aggregate({ _sum: { amount: true } }),
      prisma.expense.aggregate({ _sum: { amount: true } }),
    ]);
    income = Number(incomeTotal._sum.amount ?? 0);
    outgoings = Number(expenseTotal._sum.amount ?? 0);
    transactions = [
      ...payments.map(payment => ({ id: payment.id, type: "income" as const, label: payment.client.name, detail: payment.client.service.name, amount: Number(payment.amount), createdAt: payment.createdAt })),
      ...expenses.map(expense => ({ id: expense.id, type: "expense" as const, label: expense.label, detail: "Dépense", amount: Number(expense.amount), createdAt: expense.createdAt })),
    ].sort((a, b) => b.createdAt.getTime() - a.createdAt.getTime());
  } catch (error) {
    console.error("TrustFlix Digital payments error", error);
  }
  const net = income - outgoings;

  return <>
    <div className="mb-8"><p className="text-sm text-amber-500">Finances</p><h1 className="mt-1 text-3xl font-bold">Trésorerie</h1><p className="mt-2 text-sm text-zinc-400">Entrées, sorties et bénéfice net en temps réel.</p></div>
    <section className="mb-6 grid gap-4 md:grid-cols-3"><div className="card"><ArrowUpRight className="mb-4 text-emerald-400" size={21}/><p className="text-sm text-zinc-400">Entrées</p><p className="mt-1 text-2xl font-bold text-emerald-300">{money.format(income)}</p></div><div className="card"><ArrowDownRight className="mb-4 text-red-400" size={21}/><p className="text-sm text-zinc-400">Sorties</p><p className="mt-1 text-2xl font-bold text-red-300">{money.format(outgoings)}</p></div><div className="card"><WalletCards className="mb-4 text-amber-500" size={21}/><p className="text-sm text-zinc-400">Bénéfice net</p><p className={`mt-1 text-2xl font-bold ${net >= 0 ? "text-amber-300" : "text-red-300"}`}>{money.format(net)}</p></div></section>
    <section className="card mb-6"><h2 className="mb-4 font-semibold">Ajouter une dépense</h2><form action={createExpense} className="flex flex-col gap-3 sm:flex-row"><input name="label" placeholder="Libellé" required minLength={2}/><input name="amount" type="number" min="1" step="1" placeholder="Montant (F CFA)" required/><SubmitButton>Enregistrer</SubmitButton></form></section>
    <section className="card overflow-x-auto"><h2 className="mb-4 font-semibold">Historique</h2>{transactions.length === 0 ? <p className="text-sm text-zinc-500">Aucune transaction disponible.</p> : <table className="w-full min-w-[560px] text-left text-sm"><thead className="border-b border-zinc-800 text-xs uppercase tracking-wider text-zinc-500"><tr><th className="pb-3 font-medium">Libellé</th><th className="pb-3 font-medium">Type</th><th className="pb-3 font-medium">Date</th><th className="pb-3 text-right font-medium">Montant</th></tr></thead><tbody className="divide-y divide-zinc-800">{transactions.map(transaction => <tr key={transaction.id}><td className="py-4">{transaction.label}<div className="text-xs text-zinc-500">{transaction.detail}</div></td><td className={`py-4 ${transaction.type === "income" ? "text-emerald-300" : "text-red-300"}`}>{transaction.type === "income" ? "Entrée" : "Sortie"}</td><td className="py-4 text-zinc-400">{date.format(transaction.createdAt)}</td><td className={`py-4 text-right font-semibold ${transaction.type === "income" ? "text-emerald-300" : "text-red-300"}`}>{transaction.type === "income" ? "+" : "-"}{money.format(transaction.amount)}</td></tr>)}</tbody></table>}</section>
  </>;
}
