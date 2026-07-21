import { ja, type MessageKey } from './messages/ja'
import { en } from './messages/en'

/**
 * Supported locales for NeNe Payout. Bilingual ja / en (ADR 0006, i18n.md).
 * Japanese is the typed authority catalog (規約 04 I18N-8, #162) and the default
 * display language. `catalogs`, `SUPPORTED_LOCALES` and `DEFAULT_LOCALE` are the
 * exemplar of 規約 04 I18N-6 (`[nene2-exemplar:locales-config]`).
 */

// [nene2-exemplar:locales-config]
export const SUPPORTED_LOCALES = ['ja', 'en'] as const
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number]
export const DEFAULT_LOCALE: SupportedLocale = 'ja'
export const catalogs: Record<SupportedLocale, Record<MessageKey, string>> = { ja, en }

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

/**
 * Resolve a raw locale string (from localStorage or navigator.language) to a
 * supported locale, falling back to DEFAULT_LOCALE.
 *
 * Note (規約 04 I18N-6): initial-locale resolution is meant to be the package's
 * responsibility, but `@hideyukimori/nene2-i18n` 0.2.0 ships no `resolveLocale`
 * / `I18nProvider` yet (W0b / 0.3.0). Until it does, this stays in product code;
 * it is removed when the package takes over (tracked fleet#69).
 *
 * Examples: 'ja-JP' → 'ja', 'en-US' → 'en', 'fr' → DEFAULT_LOCALE.
 */
export function resolveLocale(raw: string): SupportedLocale {
  if (SUPPORTED_LOCALES.includes(raw as SupportedLocale)) {
    return raw as SupportedLocale
  }

  const prefix = raw.split('-')[0] ?? ''
  if (SUPPORTED_LOCALES.includes(prefix as SupportedLocale)) {
    return prefix as SupportedLocale
  }

  return DEFAULT_LOCALE
}
