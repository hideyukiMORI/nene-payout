import { createSessionTokenStore } from '@hideyukimori/nene2-client'

const STORAGE_KEY = 'nene-payout-token'

/**
 * Fleet-standard bearer token store (`@hideyukimori/nene2-client`,
 * `createSessionTokenStore`): sessionStorage, same key as before (#152/#153).
 * `shared/api/client.ts` hands this same instance to `createNene2Transport`
 * so there is exactly one store — one source of truth for get/set/clear.
 */
export const tokenStore = createSessionTokenStore({ key: STORAGE_KEY })

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
