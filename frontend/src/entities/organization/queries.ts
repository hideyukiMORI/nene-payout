import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationDto, OrganizationListDto } from './api-types'
import { mapOrganizationDtoToModel, mapOrganizationListDtoToModel } from './mapper'
import type { Organization, OrganizationList } from './model'
import { organizationKeys } from './query-keys'

export function useOrganizationSettings(): UseQueryResult<Organization, AppError> {
  return useQuery({
    queryKey: organizationKeys.current(),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<OrganizationDto>('/api/v1/organization', signal)
      return mapOrganizationDtoToModel(dto)
    },
  })
}

export interface OrganizationListParams {
  limit: number
  offset: number
}

const DEFAULT_LIST_PARAMS: OrganizationListParams = { limit: 20, offset: 0 }

/** Cross-tenant list (superadmin; /api/v1/organizations). */
export function useOrganizationList(
  params: OrganizationListParams = DEFAULT_LIST_PARAMS,
): UseQueryResult<OrganizationList, AppError> {
  return useQuery({
    queryKey: organizationKeys.list(params),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(params.limit),
        offset: String(params.offset),
      })
      const dto = await apiClient.get<OrganizationListDto>(
        `/api/v1/organizations?${search.toString()}`,
        signal,
      )
      return mapOrganizationListDtoToModel(dto)
    },
  })
}

export function useOrganizationById(id: string): UseQueryResult<Organization, AppError> {
  return useQuery({
    queryKey: organizationKeys.detail(id),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<OrganizationDto>(`/api/v1/organizations/${id}`, signal)
      return mapOrganizationDtoToModel(dto)
    },
  })
}
