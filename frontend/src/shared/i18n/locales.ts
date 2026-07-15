/**
 * Supported locales for NeNe Payout. Bilingual ja / en (ADR 0006, i18n.md).
 * Japanese is the typed source of truth (#162), the primary audience, and the
 * default when no locale is detected. English is still the runtime fallback in
 * `translate.ts` — pointing that at the authority catalog instead is W1 work
 * (fleet i18n I18N-22; see the I18N-6/20/22 exemplar note in that standard).
 */

export type SupportedLocale = 'ja' | 'en'

export interface LocaleMeta {
  /** Native language name shown in the locale selector. */
  label: string
  /** Text direction. */
  dir: 'ltr' | 'rtl'
}

export const LOCALES: Record<SupportedLocale, LocaleMeta> = {
  ja: { label: '日本語', dir: 'ltr' },
  en: { label: 'English', dir: 'ltr' },
}

export const DEFAULT_LOCALE: SupportedLocale = 'ja'

export const SUPPORTED_LOCALE_IDS = Object.keys(LOCALES) as SupportedLocale[]

/**
 * Resolve a raw locale string (from localStorage or navigator.language) to a
 * supported locale, falling back to DEFAULT_LOCALE.
 *
 * Examples: 'ja-JP' → 'ja', 'en-US' → 'en', 'fr' → DEFAULT_LOCALE.
 */
export function resolveLocale(raw: string): SupportedLocale {
  if (SUPPORTED_LOCALE_IDS.includes(raw as SupportedLocale)) {
    return raw as SupportedLocale
  }

  const prefix = raw.split('-')[0] ?? ''
  if (SUPPORTED_LOCALE_IDS.includes(prefix as SupportedLocale)) {
    return prefix as SupportedLocale
  }

  return DEFAULT_LOCALE
}
