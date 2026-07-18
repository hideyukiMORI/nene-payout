import { afterEach, beforeEach, describe, expect, it } from 'vitest'
import { authToken, tokenStore } from './auth-token'

/**
 * The bearer token store is the single source of truth for the session
 * (AuthGate, SignOutButton, the session entity all read through it). These
 * tests pin the fleet storage-key contract (`nene_payout_token`, #152/#171)
 * and the get/set/clear round-trip so a regression fails closed loudly.
 */
const STORAGE_KEY = 'nene_payout_token'

describe('auth-token store', () => {
  beforeEach(() => {
    sessionStorage.clear()
  })
  afterEach(() => {
    sessionStorage.clear()
  })

  describe('authToken thin surface', () => {
    it('returns null when no token has been set', () => {
      expect(authToken.get()).toBeNull()
    })

    it('round-trips a token through set → get', () => {
      authToken.set('jwt.abc.123')
      expect(authToken.get()).toBe('jwt.abc.123')
    })

    it('clears the token so a subsequent get fails closed to null', () => {
      authToken.set('jwt.abc.123')
      authToken.clear()
      expect(authToken.get()).toBeNull()
    })
  })

  describe('fleet storage-key contract', () => {
    it('persists under the exact key `nene_payout_token` in sessionStorage', () => {
      authToken.set('jwt.abc.123')
      expect(sessionStorage.getItem(STORAGE_KEY)).toBe('jwt.abc.123')
    })

    it('removes the sessionStorage entry on clear', () => {
      authToken.set('jwt.abc.123')
      authToken.clear()
      expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull()
    })

    it('does not read from an unrelated storage key', () => {
      sessionStorage.setItem('some_other_token', 'unrelated')
      expect(authToken.get()).toBeNull()
    })
  })

  describe('single source of truth', () => {
    it('authToken and tokenStore share one underlying store', () => {
      authToken.set('from.authToken')
      expect(tokenStore.getToken()).toBe('from.authToken')

      tokenStore.setToken('from.tokenStore')
      expect(authToken.get()).toBe('from.tokenStore')

      tokenStore.clearToken()
      expect(authToken.get()).toBeNull()
    })
  })
})
