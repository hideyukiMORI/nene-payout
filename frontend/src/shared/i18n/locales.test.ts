import { describe, it, expect } from 'vitest'
import { expectCatalogParity } from '@hideyukimori/nene2-i18n/testing'
import { resolveLocale, DEFAULT_LOCALE, catalogs } from './locales'

// [nene2-exemplar:parity-test]
// 規約 04 I18N-20: 全ロケール shape 一致 CI ゲート。expectCatalogParity は内部で
// vitest の test() を自己登録するため、describe/test で包まずトップレベルで呼ぶ。
// 0.2.0 の実 API は (catalogs, options) の2引数形（04 §I18N-20 の1引数例は 0.2.0 実装前の草稿）。
expectCatalogParity(catalogs, {
  authority: 'ja',
  maxIdenticalRatio: 0.2,
  minKeys: 50,
  // 翻訳不能キー（固有名・商標・数値ラベル）— ja==en が正。
  identicalAllowlist: [
    'app.name',
    'admin.receivedInvoices.taxBreakdown.rate10',
    'admin.payments.gateway.stripe',
  ],
})

describe('resolveLocale', () => {
  it('returns a supported locale unchanged', () => {
    expect(resolveLocale('ja')).toBe('ja')
    expect(resolveLocale('en')).toBe('en')
  })

  it('resolves a language-region tag to the matching locale', () => {
    expect(resolveLocale('ja-JP')).toBe('ja')
    expect(resolveLocale('en-US')).toBe('en')
  })

  it('falls back to DEFAULT_LOCALE for unknown locales', () => {
    expect(resolveLocale('fr')).toBe(DEFAULT_LOCALE)
    expect(resolveLocale('xx-YY')).toBe(DEFAULT_LOCALE)
    expect(resolveLocale('')).toBe(DEFAULT_LOCALE)
  })
})
