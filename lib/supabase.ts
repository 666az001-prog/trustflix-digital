import { createClient } from "@supabase/supabase-js";

const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL || "https://qrugbbynubipoacvewdo.supabase.co";
const supabaseAnonKey = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY || "sb_publishable_YcjFzcsfoGaboo4nZvQOrQ__zHBsI2y";

export const supabase = createClient(supabaseUrl, supabaseAnonKey);
