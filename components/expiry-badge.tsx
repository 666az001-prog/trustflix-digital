type ExpiryBadgeProps = { endDate: Date };

function daysUntil(endDate: Date): number {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const end = new Date(endDate);
  end.setHours(0, 0, 0, 0);
  return Math.ceil((end.getTime() - today.getTime()) / 86400000);
}

export function ExpiryBadge({ endDate }: ExpiryBadgeProps) {
  const days = daysUntil(endDate);
  const label = days < 0 ? "Expiré" : days === 0 ? "Expire aujourd’hui" : `Expire dans ${days} jour${days > 1 ? "s" : ""}`;
  const color = days <= 0 ? "bg-red-500/15 text-red-300" : days <= 3 ? "bg-orange-500/15 text-orange-300" : days <= 7 ? "bg-yellow-500/15 text-yellow-300" : "bg-emerald-500/15 text-emerald-300";

  return <span className={`badge ${color}`}>{label}</span>;
}
