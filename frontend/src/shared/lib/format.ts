import type { SupportedLocale } from '@/shared/i18n'

const LOCALE_TAG: Record<SupportedLocale, string> = {
  ja: 'ja-JP',
  en: 'en-US',
}

/**
 * Formats an integer amount expressed in the minimum currency unit (JPY has no
 * minor unit, so the integer is the yen amount) as a localized currency string.
 */
export function formatJpy(amount: number, locale: SupportedLocale): string {
  return new Intl.NumberFormat(LOCALE_TAG[locale], {
    style: 'currency',
    currency: 'JPY',
    maximumFractionDigits: 0,
  }).format(amount)
}

/**
 * Formats an ISO date-time for display in JST (UTC storage / JST display —
 * ADR 0012). Returns the raw value when it cannot be parsed.
 */
export function formatDateTime(isoDateTime: string, locale: SupportedLocale): string {
  const date = new Date(isoDateTime)
  if (Number.isNaN(date.getTime())) {
    return isoDateTime
  }
  return new Intl.DateTimeFormat(LOCALE_TAG[locale], {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'Asia/Tokyo',
  }).format(date)
}

/**
 * Formats an ISO calendar date (YYYY-MM-DD) for display. Parsed as a plain
 * calendar date (no timezone shift) per the JST display rule (ADR 0012).
 */
export function formatDate(isoDate: string, locale: SupportedLocale): string {
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(isoDate)
  if (match === null) {
    return isoDate
  }
  const [, year, month, day] = match
  const date = new Date(Number(year), Number(month) - 1, Number(day))
  return new Intl.DateTimeFormat(LOCALE_TAG[locale], {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  }).format(date)
}
