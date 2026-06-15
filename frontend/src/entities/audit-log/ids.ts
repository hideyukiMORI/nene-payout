declare const auditLogIdBrand: unique symbol

export type AuditLogId = string & { readonly [auditLogIdBrand]: never }

export function toAuditLogId(value: string): AuditLogId {
  return value as AuditLogId
}
