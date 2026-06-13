import { describe, it, expect } from 'vitest'
import { en } from './messages/en'
import { ja } from './messages/ja'
import { translate } from './translate'

describe('translate', () => {
  it('returns the message for an existing key', () => {
    expect(translate(en, 'admin.nav.dashboard')).toBe('Dashboard')
    expect(translate(ja, 'admin.nav.dashboard')).toBe('ダッシュボード')
  })

  it('falls back to English when a key is missing from the locale catalog', () => {
    const partial = { 'admin.nav.dashboard': 'ダッシュボード' }
    expect(translate(partial, 'admin.nav.vendors')).toBe(en['admin.nav.vendors'])
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
