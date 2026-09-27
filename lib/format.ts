export const money = new Intl.NumberFormat("fr-FR", { style: "currency", currency: "EUR" });
export const date = new Intl.DateTimeFormat("fr-FR", { day: "2-digit", month: "short", year: "numeric" });
export function cleanWhatsapp(value: string) { const digits = value.replace(/[^\d]/g, "").replace(/^00/, ""); return digits.startsWith("225") ? digits : `225${digits.replace(/^0+/, "")}`; }
export function whatsappUrl(number: string, name: string, service: string, endDate: Date) { const text = `Bonjour ${name}, votre abonnement ${service} expire le ${date.format(endDate)}. Souhaitez-vous le renouveler ?`; return `https://wa.me/${cleanWhatsapp(number)}?text=${encodeURIComponent(text)}`; }
