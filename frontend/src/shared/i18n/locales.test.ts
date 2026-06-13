import { describe, it, expect } from 'vitest'
import { resolveLocale, DEFAULT_LOCALE } from './locales'
import { en } from './messages/en'
import { ja } from './messages/ja'

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

describe('i18n key coverage (no gap on language switch)', () => {
  it('ja defines every key present in en', () => {
    const enKeys = Object.keys(en)
    const missingInJa = enKeys.filter((key) => !(key in ja))

    if (missingInJa.length > 0) {
      throw new Error(
        `ja.ts is missing ${String(missingInJa.length)} key(s):\n` +
          missingInJa.map((k) => `  • ${k}`).join('\n') +
          '\n\nAdd them to frontend/src/shared/i18n/messages/ja.ts',
      )
    }

    expect(missingInJa).toHaveLength(0)
  })

  it('ja defines no keys absent from en (no stray translations)', () => {
    const enKeys = new Set(Object.keys(en))
    const strayInJa = Object.keys(ja).filter((key) => !enKeys.has(key))

    expect(strayInJa).toHaveLength(0)
  })

  it('ja and en have an identical key count', () => {
    expect(Object.keys(ja)).toHaveLength(Object.keys(en).length)
  })
})
