import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { VendorDto, VendorListDto } from './api-types'
import type { VendorId } from './ids'
import { mapVendorDtoToModel, mapVendorListDtoToModel } from './mapper'
import type { Vendor, VendorList } from './model'
import { vendorKeys } from './query-keys'

export interface VendorListParams {
  limit: number
  offset: number
  q: string | null
}

const DEFAULT_LIST_PARAMS: VendorListParams = { limit: 20, offset: 0, q: null }

export function useVendorList(
  params: VendorListParams = DEFAULT_LIST_PARAMS,
): UseQueryResult<VendorList, AppError> {
  return useQuery({
    queryKey: vendorKeys.list(params),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(params.limit),
        offset: String(params.offset),
      })
      if (params.q !== null && params.q !== '') {
        search.set('q', params.q)
      }
      const dto = await apiClient.get<VendorListDto>(`/api/v1/vendors?${search.toString()}`, signal)
      return mapVendorListDtoToModel(dto)
    },
  })
}

export function useVendor(id: VendorId): UseQueryResult<Vendor, AppError> {
  return useQuery({
    queryKey: vendorKeys.detail(id),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<VendorDto>(`/api/v1/vendors/${id}`, signal)
      return mapVendorDtoToModel(dto)
    },
  })
}
