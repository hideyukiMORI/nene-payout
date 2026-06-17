export const organizationKeys = {
  all: ['organization'] as const,
  current: () => [...organizationKeys.all, 'current'] as const,
  lists: () => [...organizationKeys.all, 'list'] as const,
  list: (params: { limit: number; offset: number }) =>
    [...organizationKeys.lists(), params] as const,
  details: () => [...organizationKeys.all, 'detail'] as const,
  detail: (id: string) => [...organizationKeys.details(), id] as const,
}
