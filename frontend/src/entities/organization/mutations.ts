import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationDto } from './api-types'
import { mapOrganizationDtoToModel, mapUpdateOrganizationNameInputToDto } from './mapper'
import type { Organization, UpdateOrganizationNameInput } from './model'
import { organizationKeys } from './query-keys'

export function useUpdateOrganizationName(): UseMutationResult<
  Organization,
  AppError,
  UpdateOrganizationNameInput
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (input) => {
      const dto = await apiClient.patch<OrganizationDto>(
        '/api/v1/organization',
        mapUpdateOrganizationNameInputToDto(input),
      )
      return mapOrganizationDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: organizationKeys.current() })
    },
  })
}
