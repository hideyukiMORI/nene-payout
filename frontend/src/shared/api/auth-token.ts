const STORAGE_KEY = 'nene-payout-token'

/**
 * Bearer token store. The token is sent as `Authorization: Bearer` by the API
 * client. Stored in localStorage for the scaffold; a future ADR may move it to
 * an httpOnly cookie (frontend-standards security).
 */
export const authToken = {
  get(): string | null {
    try {
      return localStorage.getItem(STORAGE_KEY)
    } catch {
      return null
    }
  },
  set(token: string): void {
    try {
      localStorage.setItem(STORAGE_KEY, token)
    } catch {
      // ignore storage errors
    }
  },
  clear(): void {
    try {
      localStorage.removeItem(STORAGE_KEY)
    } catch {
      // ignore storage errors
    }
  },
}
