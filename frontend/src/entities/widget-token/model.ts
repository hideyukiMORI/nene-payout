/** An issued organization-scoped widget token plus its ready-to-paste embed snippet. */
export interface WidgetToken {
  token: string
  expiresAt: string
  embedSnippet: string
}
