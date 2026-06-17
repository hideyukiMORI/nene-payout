import type { UserId } from './ids'

export const userKeys = {
  all: ['users'] as const,
  lists: () => [...userKeys.all, 'list'] as const,
  list: (params: { limit: number; offset: number }) => [...userKeys.lists(), params] as const,
  details: () => [...userKeys.all, 'detail'] as const,
  detail: (id: UserId) => [...userKeys.details(), id] as const,
}
