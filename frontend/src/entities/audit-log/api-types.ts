export interface AuditLogDto {
  id: string
  actor_user_id?: string | null
  actor_email?: string | null
  organization_id?: string | null
  action: string
  entity_type: string
  entity_id: string
  request_id?: string | null
  created_at: string
}

export interface AuditLogListDto {
  items: AuditLogDto[]
  limit: number
  offset: number
  total?: number
}
