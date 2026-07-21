import { tokenStore } from '@/shared/api/client'

/**
 * The bearer token store is created in `shared/api/client.ts` — the single
 * `@hideyukimori/nene2-client` contact file (A-2) — and re-exported here so the
 * store's public import path (`shared/api/auth-token`) stays stable for callers
 * and tests. There is exactly one store: one source of truth for get/set/clear.
 */
export { tokenStore }

/**
 * Thin, stable surface over `tokenStore` for call sites that predate the
 * transport migration (AuthGate, SignOutButton, session entity). Kept as-is
 * so those files did not need to change.
 */
export const authToken = {
  get(): string | null {
    return tokenStore.getToken()
  },
  set(token: string): void {
    tokenStore.setToken(token)
  },
  clear(): void {
    tokenStore.clearToken()
  },
}
