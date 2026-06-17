import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { UserDto } from './api-types'
import type { UserId } from './ids'
import { mapInviteUserInputToDto, mapUpdateUserRoleInputToDto, mapUserDtoToModel } from './mapper'
import type { InviteUserInput, UpdateUserRoleInput, User } from './model'
import { userKeys } from './query-keys'

export function useInviteUser(): UseMutationResult<User, AppError, InviteUserInput> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (input) => {
      const dto = await apiClient.post<UserDto>('/api/v1/users', mapInviteUserInputToDto(input))
      return mapUserDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: userKeys.lists() })
    },
  })
}

export function useUpdateUserRole(): UseMutationResult<
  User,
  AppError,
  { id: UserId; input: UpdateUserRoleInput }
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, input }) => {
      const dto = await apiClient.patch<UserDto>(
        `/api/v1/users/${id}`,
        mapUpdateUserRoleInputToDto(input),
      )
      return mapUserDtoToModel(dto)
    },
    onSuccess: async (_data, variables) => {
      await queryClient.invalidateQueries({ queryKey: userKeys.lists() })
      await queryClient.invalidateQueries({ queryKey: userKeys.detail(variables.id) })
    },
  })
}

export function useDeactivateUser(): UseMutationResult<User, AppError, UserId> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id) => {
      const dto = await apiClient.post<UserDto>(`/api/v1/users/${id}/deactivate`, {})
      return mapUserDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: userKeys.lists() })
    },
  })
}
