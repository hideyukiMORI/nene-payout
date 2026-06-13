/**
 * Supported locales for NeNe Payout. Bilingual ja / en (ADR 0006, i18n.md).
 * English is the typed source of truth and the runtime fallback; Japanese is the
 * primary audience and the default when no locale is detected.
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
