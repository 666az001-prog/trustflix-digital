"use server";
import { Prisma } from "@prisma/client";
import { revalidatePath } from "next/cache";
import { z } from "zod";
import crypto from "crypto";
import { decrypt, encrypt } from "../lib/crypto";
import { prisma } from "../lib/prisma";
import { assignSlotToClient } from "../lib/slot-assignment";
import { serviceCatalog } from "../lib/services";

const ACCOUNT_ACTIVE = "ACTIVE" as const;
const SLOT_FREE = "FREE" as const;
const CLIENT_ACTIVE = "ACTIVE" as const;
const CLIENT_EXPIRED = "EXPIRED" as const;

const accountSchema = z.object({ serviceId: z.string().min(1), email: z.string().email(), password: z.string().min(8), renewalDate: z.coerce.date() });
export type AccountActionState = { error?: string; success?: string };
export async function createAccount(_previousState: AccountActionState, formData: FormData): Promise<AccountActionState> {
  const parsed = accountSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return { error: "Vérifiez le service, l’e-mail, le mot de passe et la date de renouvellement." };
  const data = parsed.data;
  try {
    const selectedService = serviceCatalog.find(service => service.id === data.serviceId);
    const existingService = selectedService ? null : await prisma.service.findUnique({ where: { id: data.serviceId } });
    const service = existingService ?? await prisma.service.upsert({ where: { slug: selectedService?.slug ?? data.serviceId }, update: {}, create: selectedService ?? { name: data.serviceId, slug: data.serviceId, slotPrefix: "Place", defaultSlotCount: 1, requiresPin: false } });
    await prisma.account.create({ data: { serviceId: service.id, email: data.email.toLowerCase(), passwordEncrypted: encrypt(data.password), renewalDate: data.renewalDate, status: ACCOUNT_ACTIVE, slots: { create: Array.from({ length: 5 }, (_, i) => ({ identifier: `${service.slotPrefix} ${i + 1}`, pinCodeEncrypted: service.requiresPin ? encrypt(String(crypto.randomInt(1000, 10000))) : null })) } } });
  } catch (error) {
    console.error("TrustFlix Digital account creation error", error);
    return { error: "Impossible de créer le compte. Vérifiez la connexion à la base et les variables Vercel." };
  }
  revalidatePath("/accounts"); revalidatePath("/");
  return { success: "Compte maître créé avec succès." };
}
const clientSchema = z.object({ name: z.string().min(2).max(100), whatsapp: z.string().min(7).max(30), serviceId: z.string().min(1), price: z.coerce.number().positive(), pinCode: z.string().regex(/^\d{4}$/) });
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
      const selectedService = serviceCatalog.find(service => service.id === data.serviceId);
      const existingService = selectedService ? null : await tx.service.findUnique({ where: { id: data.serviceId } });
      const service = existingService ?? await tx.service.upsert({ where: { slug: selectedService?.slug ?? data.serviceId }, update: {}, create: selectedService ?? { name: data.serviceId, slug: data.serviceId, slotPrefix: "Place", defaultSlotCount: 1, requiresPin: false } });
      const client = await tx.client.create({ data: { name: data.name, whatsapp: data.whatsapp, serviceId: service.id, price: new Prisma.Decimal(data.price), startDate, endDate, status: CLIENT_ACTIVE } });
      const assignment = await assignSlotToClient(tx, service.id, client.id);
      await tx.slot.update({ where: { id: assignment.slot.id }, data: { pinCodeEncrypted: encrypt(data.pinCode) } });
      await tx.payment.create({ data: { clientId: client.id, amount: new Prisma.Decimal(data.price) } });
    }, { isolationLevel: Prisma.TransactionIsolationLevel.Serializable });
  } catch (error) {
    return { error: error instanceof Error ? error.message : "Impossible d'ajouter ce client." };
  }
  revalidatePath("/clients"); revalidatePath("/"); revalidatePath("/accounts");
  return {};
}
export async function getClientPin(clientId: string) {
  const client = await prisma.client.findUnique({ where: { id: clientId }, select: { slot: { select: { pinCodeEncrypted: true } } } });
  if (!client?.slot?.pinCodeEncrypted) return { error: "Aucun code PIN n’est défini pour ce client." };
  try {
    return { pinCode: decrypt(client.slot.pinCodeEncrypted) };
  } catch (error) {
    console.error("TrustFlix Digital PIN decryption error", error);
    return { error: "Impossible de déchiffrer le code PIN. Vérifiez ACCOUNT_CREDENTIALS_KEY." };
  }
}
export async function updateClientPin(clientId: string, pinCode: string) {
  if (!/^\d{4}$/.test(pinCode)) return { error: "Le code PIN doit contenir exactement 4 chiffres." };
  const client = await prisma.client.findUnique({ where: { id: clientId }, select: { slotId: true } });
  if (!client?.slotId) return { error: "Ce client n’a pas de slot attribué." };
  await prisma.slot.update({ where: { id: client.slotId }, data: { pinCodeEncrypted: encrypt(pinCode) } });
  revalidatePath(`/clients/${clientId}`);
  return { success: "Code PIN mis à jour." };
}
export async function releaseClient(clientId: string) {
  await prisma.$transaction(async tx => { const client = await tx.client.findUniqueOrThrow({ where: { id: clientId } }); await tx.client.update({ where: { id: clientId }, data: { status: CLIENT_EXPIRED } }); if (client.slotId) await tx.slot.update({ where: { id: client.slotId }, data: { status: SLOT_FREE } }); });
  revalidatePath("/clients"); revalidatePath("/"); revalidatePath("/accounts");
}
export async function deleteClient(clientId: string) {
  await prisma.$transaction(async tx => { const client = await tx.client.findUniqueOrThrow({ where: { id: clientId } }); if (client.slotId) await tx.slot.update({ where: { id: client.slotId }, data: { status: SLOT_FREE } }); await tx.client.delete({ where: { id: clientId } }); });
  revalidatePath("/clients"); revalidatePath("/"); revalidatePath("/accounts");
}
export async function deleteAccount(accountId: string) {
  await prisma.$transaction(async tx => {
    const account = await tx.account.findUniqueOrThrow({ where: { id: accountId }, include: { slots: { select: { id: true } } } });
    const slotIds = account.slots.map(slot => slot.id);
    if (slotIds.length > 0) {
      await tx.client.updateMany({ where: { slotId: { in: slotIds } }, data: { status: CLIENT_EXPIRED, slotId: null } });
    }
    await tx.account.delete({ where: { id: accountId } });
  });
  revalidatePath("/accounts"); revalidatePath("/clients"); revalidatePath("/");
}
const expenseSchema = z.object({ label: z.string().min(2).max(120), amount: z.coerce.number().positive() });
export async function createExpense(formData: FormData) {
  const data = expenseSchema.parse(Object.fromEntries(formData));
  await prisma.expense.create({ data: { label: data.label, amount: new Prisma.Decimal(data.amount) } });
  revalidatePath("/payments"); revalidatePath("/");
}
