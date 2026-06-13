import type { MessageKey } from './translate'

/**
 * Map an HTTP status (from an RFC 9457 Problem Details response) to a localizable
 * message key. API error titles stay English (NENE2 language policy); the UI
 * shows the localized message instead. Returns null when there is no generic
 * mapping (caller falls back to `common.error.unknown`).
 */
export function mapProblemStatusToMessageKey(status: number): MessageKey | null {
  switch (status) {
    case 401:
      return 'common.error.unauthorized'
    case 403:
      return 'common.error.forbidden'
    case 404:
      return 'common.error.notFound'
    case 409:
      return 'common.error.conflict'
    case 413:
      return 'common.error.payloadTooLarge'
    case 422:
      return 'common.error.validation'
    default:
      return status >= 500 ? 'common.error.serverError' : null
  }
}
