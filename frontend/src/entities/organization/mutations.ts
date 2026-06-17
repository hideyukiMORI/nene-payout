import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { OrganizationDto } from './api-types'
import {
  mapCreateOrganizationInputToDto,
  mapOrganizationDtoToModel,
  mapUpdateOrganizationInputToDto,
  mapUpdateOrganizationNameInputToDto,
} from './mapper'
import type {
  CreateOrganizationInput,
  Organization,
  UpdateOrganizationInput,
  UpdateOrganizationNameInput,
} from './model'
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

export function useCreateOrganization(): UseMutationResult<
  Organization,
  AppError,
  CreateOrganizationInput
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (input) => {
      const dto = await apiClient.post<OrganizationDto>(
        '/api/v1/organizations',
        mapCreateOrganizationInputToDto(input),
      )
      return mapOrganizationDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: organizationKeys.lists() })
    },
  })
}

export function useUpdateOrganization(): UseMutationResult<
  Organization,
  AppError,
  { id: string; input: UpdateOrganizationInput }
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, input }) => {
      const dto = await apiClient.patch<OrganizationDto>(
        `/api/v1/organizations/${id}`,
        mapUpdateOrganizationInputToDto(input),
      )
      return mapOrganizationDtoToModel(dto)
    },
    onSuccess: async (_data, variables) => {
      await queryClient.invalidateQueries({ queryKey: organizationKeys.lists() })
      await queryClient.invalidateQueries({ queryKey: organizationKeys.detail(variables.id) })
    },
  })
}

export function useDeactivateOrganization(): UseMutationResult<Organization, AppError, string> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id) => {
      const dto = await apiClient.post<OrganizationDto>(
        `/api/v1/organizations/${id}/deactivate`,
        {},
      )
      return mapOrganizationDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: organizationKeys.lists() })
    },
  })
}
