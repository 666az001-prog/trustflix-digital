import type { Config } from "tailwindcss";
export default { content: ["./app/**/*.{ts,tsx}", "./components/**/*.{ts,tsx}"], theme: { extend: { colors: { canvas: "#09090b", surface: "#18181b" }, boxShadow: { glow: "0 12px 36px rgb(234 88 12 / .15)" } } }, plugins: [] } satisfies Config;
