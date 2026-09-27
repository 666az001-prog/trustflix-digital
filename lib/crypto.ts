import crypto from "crypto";
function key() {
  const value = process.env.ACCOUNT_CREDENTIALS_KEY;
  if (!value) throw new Error("ACCOUNT_CREDENTIALS_KEY est requis.");
  const result = Buffer.from(value, "base64");
  if (result.length !== 32) throw new Error("ACCOUNT_CREDENTIALS_KEY doit être une clé base64 de 32 octets.");
  return result;
}
export function encrypt(value: string) {
  const iv = crypto.randomBytes(12); const cipher = crypto.createCipheriv("aes-256-gcm", key(), iv);
  return [iv.toString("base64"), cipher.update(value, "utf8", "base64") + cipher.final("base64"), cipher.getAuthTag().toString("base64")].join(".");
}
export function decrypt(value: string) {
  const [iv, encrypted, tag] = value.split("."); const decipher = crypto.createDecipheriv("aes-256-gcm", key(), Buffer.from(iv, "base64")); decipher.setAuthTag(Buffer.from(tag, "base64"));
  return decipher.update(encrypted, "base64", "utf8") + decipher.final("utf8");
}
