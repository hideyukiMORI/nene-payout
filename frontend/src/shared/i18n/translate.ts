import { ja } from './messages/ja'
import type { MessageCatalog, MessageKey } from './messages/ja'

export type { MessageKey }
export type MessageParams = Record<string, string | number>

/** console.error is fired once per missing key (DEV only) to avoid log spam. */
const reportedMisses = new Set<MessageKey>()

/**
 * Look up a message key in the given (possibly partial) catalog and interpolate
 * `{{param}}` placeholders.
 *
 * 規約 04 I18N-22 (沈黙フォールバック MUST NOT): a miss must never be silently
 * papered over. In DEV the miss is made visible — the value renders as `∅<key>`
 * and `console.error` fires once per key — so a dropped translation is caught in
 * review/E2E. In production the authority catalog (ja, #162 — never `en`) is the
 * one place a user is spared the `∅`. With today's full `Record<MessageKey,…>`
 * catalogs a miss cannot actually occur; this is the fail-loud shape the rule
 * requires regardless.
 */
export function translate(
  messages: Partial<MessageCatalog>,
  key: MessageKey,
  params?: MessageParams,
): string {
  let raw = messages[key]

  if (raw === undefined) {
    if (import.meta.env.DEV) {
      if (!reportedMisses.has(key)) {
        reportedMisses.add(key)
        console.error(`i18n: unresolved key ∅${key}`)
      }
      raw = `∅${key}`
    } else {
      raw = ja[key]
    }
  }

  if (params === undefined || Object.keys(params).length === 0) {
    return raw
  }

  return raw.replace(/\{\{(\w+)\}\}/g, (match, name: string) =>
    name in params ? String(params[name]) : match,
  )
}
