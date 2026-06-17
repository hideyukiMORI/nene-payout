import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationDto } from './api-types'
import { mapOrganizationDtoToModel } from './mapper'
import type { Organization } from './model'
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
