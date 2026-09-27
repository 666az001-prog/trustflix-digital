import { NextRequest, NextResponse } from "next/server";
import { expireDueClients } from "../../../../lib/subscriptions";
export async function GET(request: NextRequest) {
  const authorization = request.headers.get("authorization");
  if (!process.env.CRON_SECRET || authorization !== `Bearer ${process.env.CRON_SECRET}`) return NextResponse.json({ error: "Non autorisé" }, { status: 401 });
  return NextResponse.json({ expiredClients: await expireDueClients() });
}
