const STORAGE_KEY = 'nene-payout-token'

/**
 * Bearer token store. The token is sent as `Authorization: Bearer` by the API
 * client. Stored in sessionStorage (fleet-wide interim fix, 2026-07-14) so the
 * token does not persist across browser restarts or leak via localStorage; a
 * future ADR may move it to an httpOnly cookie, or this module may be
 * replaced by `@hideyukimori/nene2-client` (frontend-standards security).
 */
export const authToken = {
  get(): string | null {
    try {
      return sessionStorage.getItem(STORAGE_KEY)
    } catch {
      return null
    }
  },
  set(token: string): void {
    try {
      sessionStorage.setItem(STORAGE_KEY, token)
    } catch {
      // ignore storage errors
    }
  },
  clear(): void {
    try {
      sessionStorage.removeItem(STORAGE_KEY)
    } catch {
      // ignore storage errors
    }
  },
}
