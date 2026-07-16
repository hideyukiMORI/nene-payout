import { en, type MessageCatalog } from './messages/en'

export type MessageKey = keyof MessageCatalog
export type MessageParams = Record<string, string | number>

/**
 * Look up a message key in the given (possibly partial) catalog, falling back to
 * English, and interpolate `{{param}}` placeholders.
 *
 * The `en` fallback never fires today: `en.ts` is checked against
 * `Record<MessageKey, string>` and `locales.test.ts` pins the ja / en key sets
 * to each other, so every key resolves in its own catalog. The shape predates
 * fleet i18n I18N-22, which wants a DEV-only miss to surface (`∅` + one
 * `console.error`) and production to fall back to the authority catalog — ja
 * since #162, not `en`. Aligning it is W1 work: see the I18N-6/20/22 exemplar
 * note in that standard.
 */
export function translate(
  messages: Partial<MessageCatalog>,
  key: MessageKey,
  params?: MessageParams,
): string {
  const raw: string = messages[key] ?? en[key]

  if (params === undefined || Object.keys(params).length === 0) {
    return raw
  }

  return raw.replace(/\{\{(\w+)\}\}/g, (match, name: string) =>
    name in params ? String(params[name]) : match,
  )
}
