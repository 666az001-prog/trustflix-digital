import { PrismaClient } from "@prisma/client";
import { encrypt } from "../lib/crypto";

const prisma = new PrismaClient();
const ACCOUNT_ACTIVE = "ACTIVE" as const;
const SLOT_FREE = "FREE" as const;

async function main() {
  const catalog = [{ name: "Netflix", slug: "netflix", slotPrefix: "Profil", defaultSlotCount: 5, requiresPin: true }, { name: "Spotify", slug: "spotify", slotPrefix: "Place Duo", defaultSlotCount: 2, requiresPin: false }, { name: "Apple Music", slug: "apple-music", slotPrefix: "Place membre", defaultSlotCount: 5, requiresPin: false }];
  const services = await Promise.all(catalog.map(service => prisma.service.upsert({ where: { slug: service.slug }, update: service, create: service })));
  if (await prisma.account.count()) return;
  for (const service of services) {
    const account = await prisma.account.create({ data: { serviceId: service.id, email: `demo.${service.slug}@trustflix.test`, passwordEncrypted: encrypt("demo-only"), renewalDate: new Date(Date.now() + 7 * 86400000), status: ACCOUNT_ACTIVE } });
    await prisma.slot.createMany({ data: Array.from({ length: service.defaultSlotCount }, (_, i) => ({ accountId: account.id, identifier: `${service.slotPrefix} ${i + 1}`, pinCodeEncrypted: service.requiresPin ? encrypt(String(1000 + i)) : null, status: SLOT_FREE })) });
  }
}
main().finally(() => prisma.$disconnect());
