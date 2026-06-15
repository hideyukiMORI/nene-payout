export const auditLogKeys = {
  all: ['audit-logs'] as const,
  lists: () => [...auditLogKeys.all, 'list'] as const,
  list: (params: { limit: number; offset: number }) => [...auditLogKeys.lists(), params] as const,
}
