import { prisma } from "./prisma";

const CLIENT_ACTIVE = "ACTIVE" as const;
const CLIENT_EXPIRED = "EXPIRED" as const;
const SLOT_FREE = "FREE" as const;

export async function expireDueClients(reference = new Date()) {
  const dueClients = await prisma.client.findMany({ where: { status: CLIENT_ACTIVE, endDate: { lt: new Date(reference.getFullYear(), reference.getMonth(), reference.getDate()) } }, select: { id: true, slotId: true } });
  if (!dueClients.length) return 0;
  await prisma.$transaction(dueClients.flatMap(client => [prisma.client.update({ where: { id: client.id }, data: { status: CLIENT_EXPIRED } }), ...(client.slotId ? [prisma.slot.update({ where: { id: client.slotId }, data: { status: SLOT_FREE } })] : [])]));
  return dueClients.length;
}
