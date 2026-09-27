import { Prisma } from "@prisma/client";

const ACCOUNT_ACTIVE = "ACTIVE" as const;
const SLOT_FREE = "FREE" as const;
const SLOT_OCCUPIED = "OCCUPIED" as const;

export async function assignSlotToClient(db: Prisma.TransactionClient, serviceId: string, clientId: string) {
  const availableSlot = await db.slot.findFirst({ where: { account: { serviceId, status: ACCOUNT_ACTIVE }, status: SLOT_FREE }, orderBy: { createdAt: "asc" } });
  if (!availableSlot) throw new Error("Aucun écran/place disponible pour ce service");
  const reservation = await db.slot.updateMany({ where: { id: availableSlot.id, status: SLOT_FREE }, data: { status: SLOT_OCCUPIED } });
  if (reservation.count !== 1) throw new Error("Le slot vient d’être attribué à un autre client. Réessayez.");
  const updatedClient = await db.client.update({ where: { id: clientId }, data: { slotId: availableSlot.id } });
  return { slot: availableSlot, client: updatedClient };
}
