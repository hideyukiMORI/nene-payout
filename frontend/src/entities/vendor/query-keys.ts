import type { VendorId } from './ids'

export const vendorKeys = {
  all: ['vendors'] as const,
  lists: () => [...vendorKeys.all, 'list'] as const,
  list: (params: { limit: number; offset: number; q: string | null }) =>
    [...vendorKeys.lists(), params] as const,
  details: () => [...vendorKeys.all, 'detail'] as const,
  detail: (id: VendorId) => [...vendorKeys.details(), id] as const,
}
