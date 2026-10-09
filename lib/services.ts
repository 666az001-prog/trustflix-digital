export const serviceCatalog = [
  { id: "netflix", name: "Netflix", slug: "netflix", slotPrefix: "Profil", defaultSlotCount: 5, requiresPin: true },
  { id: "spotify", name: "Spotify Family", slug: "spotify", slotPrefix: "Place famille", defaultSlotCount: 6, requiresPin: false },
  { id: "apple-music", name: "Apple Music", slug: "apple-music", slotPrefix: "Place membre", defaultSlotCount: 5, requiresPin: false },
] as const;

export type ServiceOption = (typeof serviceCatalog)[number];
