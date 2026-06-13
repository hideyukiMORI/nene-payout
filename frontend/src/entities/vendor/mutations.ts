import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { VendorDto } from './api-types'
import type { VendorId } from './ids'
import { mapCreateVendorInputToDto, mapUpdateVendorInputToDto, mapVendorDtoToModel } from './mapper'
import type { CreateVendorInput, UpdateVendorInput, Vendor } from './model'
import { vendorKeys } from './query-keys'

export function useCreateVendor(): UseMutationResult<Vendor, AppError, CreateVendorInput> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (input) => {
      const dto = await apiClient.post<VendorDto>(
        '/api/v1/vendors',
        mapCreateVendorInputToDto(input),
      )
      return mapVendorDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: vendorKeys.lists() })
    },
  })
}

export function useUpdateVendor(): UseMutationResult<
  Vendor,
  AppError,
  { id: VendorId; input: UpdateVendorInput }
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, input }) => {
      const dto = await apiClient.patch<VendorDto>(
        `/api/v1/vendors/${id}`,
        mapUpdateVendorInputToDto(input),
      )
      return mapVendorDtoToModel(dto)
    },
    onSuccess: async (_data, variables) => {
      await queryClient.invalidateQueries({ queryKey: vendorKeys.lists() })
      await queryClient.invalidateQueries({ queryKey: vendorKeys.detail(variables.id) })
    },
  })
}

export function useDeactivateVendor(): UseMutationResult<Vendor, AppError, VendorId> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id) => {
      const dto = await apiClient.post<VendorDto>(`/api/v1/vendors/${id}/deactivate`, {})
      return mapVendorDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: vendorKeys.lists() })
    },
  })
}
