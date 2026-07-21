import { describe, it, expect, vi, afterEach } from 'vitest'
import { en } from './messages/en'
import { ja } from './messages/ja'
import { translate } from './translate'

afterEach(() => {
  vi.unstubAllEnvs()
})

describe('translate', () => {
  it('returns the message for an existing key', () => {
    expect(translate(en, 'admin.nav.dashboard')).toBe('Dashboard')
    expect(translate(ja, 'admin.nav.dashboard')).toBe('ダッシュボード')
  })

  it('makes a missing key visible in DEV (∅+key, no silent fallback) — I18N-22', () => {
    // 規約 04 I18N-22: 沈黙フォールバック MUST NOT。DEV（vitest）では ∅+キーを描画する。
    vi.stubEnv('DEV', true)
    const partial = { 'admin.nav.dashboard': 'ダッシュボード' }
    expect(translate(partial, 'admin.nav.vendors')).toBe('∅admin.nav.vendors')
  })

  it('falls back to the authority catalog (ja) in production — I18N-22', () => {
    // 本番では ∅ をユーザに見せず権威カタログ ja へフォールバックする（en ではない）。
    vi.stubEnv('DEV', false)
    const partial = {}
    expect(translate(partial, 'admin.nav.vendors')).toBe(ja['admin.nav.vendors'])
  })

  it('interpolates a {{param}} placeholder', () => {
    expect(translate(en, 'admin.payments.amountDue', { amount: '¥1,000' })).toBe(
      'Amount due: ¥1,000',
    )
    expect(translate(ja, 'admin.payments.amountDue', { amount: '¥1,000' })).toBe('支払金額: ¥1,000')
  })

  it('interpolates a named param into a confirm message', () => {
    expect(translate(en, 'admin.vendors.deactivate.confirmTitle', { name: 'Acme' })).toBe(
      'Deactivate vendor "Acme"?',
    )
  })

  it('leaves unmatched placeholders untouched', () => {
    expect(translate(en, 'admin.payments.amountDue', {})).toBe('Amount due: {{amount}}')
  })
})
