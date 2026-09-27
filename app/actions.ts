"use server";
import { Prisma } from "@prisma/client";
import { revalidatePath } from "next/cache";
import { z } from "zod";
import crypto from "crypto";
import { encrypt } from "../lib/crypto";
import { prisma } from "../lib/prisma";
import { assignSlotToClient } from "../lib/slot-assignment";

const ACCOUNT_ACTIVE = "ACTIVE" as const;
const SLOT_FREE = "FREE" as const;
const CLIENT_ACTIVE = "ACTIVE" as const;
const CLIENT_EXPIRED = "EXPIRED" as const;

const accountSchema = z.object({ serviceId: z.string().min(1), email: z.string().email(), password: z.string().min(8), renewalDate: z.coerce.date() });
export async function createAccount(formData: FormData) {
  const data = accountSchema.parse(Object.fromEntries(formData));
  const service = await prisma.service.findUniqueOrThrow({ where: { id: data.serviceId } });
  await prisma.account.create({ data: { serviceId: data.serviceId, email: data.email.toLowerCase(), passwordEncrypted: encrypt(data.password), renewalDate: data.renewalDate, status: ACCOUNT_ACTIVE, slots: { create: Array.from({ length: service.defaultSlotCount }, (_, i) => ({ identifier: `${service.slotPrefix} ${i + 1}`, pinCodeEncrypted: service.requiresPin ? encrypt(String(crypto.randomInt(1000, 10000))) : null })) } } });
  revalidatePath("/accounts"); revalidatePath("/");
}
const clientSchema = z.object({ name: z.string().min(2).max(100), whatsapp: z.string().min(7).max(30), serviceId: z.string().min(1), price: z.coerce.number().positive() });
export type ClientActionState = { error?: string };
export async function createClient(_previousState: ClientActionState, formData: FormData): Promise<ClientActionState> {
  const parsed = clientSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return { error: "Vérifiez le nom, le numéro WhatsApp, le service et le prix." };
  const data = parsed.data;
  const startDate = new Date();
  startDate.setHours(0, 0, 0, 0);
  const endDate = new Date(startDate);
  endDate.setDate(endDate.getDate() + 30);
  try {
    await prisma.$transaction(async tx => {
      const client = await tx.client.create({ data: { name: data.name, whatsapp: data.whatsapp, serviceId: data.serviceId, price: new Prisma.Decimal(data.price), startDate, endDate, status: CLIENT_ACTIVE } });
      await assignSlotToClient(tx, data.serviceId, client.id);
      await tx.payment.create({ data: { clientId: client.id, amount: new Prisma.Decimal(data.price) } });
    }, { isolationLevel: Prisma.TransactionIsolationLevel.Serializable });
  } catch (error) {
    return { error: error instanceof Error ? error.message : "Impossible d'ajouter ce client." };
  }
  revalidatePath("/clients"); revalidatePath("/"); revalidatePath("/accounts");
  return {};
}
export async function releaseClient(clientId: string) {
  await prisma.$transaction(async tx => { const client = await tx.client.findUniqueOrThrow({ where: { id: clientId } }); await tx.client.update({ where: { id: clientId }, data: { status: CLIENT_EXPIRED } }); if (client.slotId) await tx.slot.update({ where: { id: client.slotId }, data: { status: SLOT_FREE } }); });
  revalidatePath("/clients"); revalidatePath("/"); revalidatePath("/accounts");
}
export async function deleteClient(clientId: string) {
  await prisma.$transaction(async tx => { const client = await tx.client.findUniqueOrThrow({ where: { id: clientId } }); if (client.slotId) await tx.slot.update({ where: { id: client.slotId }, data: { status: SLOT_FREE } }); await tx.client.delete({ where: { id: clientId } }); });
  revalidatePath("/clients"); revalidatePath("/"); revalidatePath("/accounts");
}
const expenseSchema = z.object({ label: z.string().min(2).max(120), amount: z.coerce.number().positive() });
export async function createExpense(formData: FormData) {
  const data = expenseSchema.parse(Object.fromEntries(formData));
  await prisma.expense.create({ data: { label: data.label, amount: new Prisma.Decimal(data.amount) } });
  revalidatePath("/payments"); revalidatePath("/");
}
