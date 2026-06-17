import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { UserDto, UserListDto } from './api-types'
import type { UserId } from './ids'
import { mapUserDtoToModel, mapUserListDtoToModel } from './mapper'
import type { User, UserList } from './model'
import { userKeys } from './query-keys'

export interface UserListParams {
  limit: number
  offset: number
}

const DEFAULT_LIST_PARAMS: UserListParams = { limit: 20, offset: 0 }

export function useUserList(
  params: UserListParams = DEFAULT_LIST_PARAMS,
): UseQueryResult<UserList, AppError> {
  return useQuery({
    queryKey: userKeys.list(params),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(params.limit),
        offset: String(params.offset),
      })
      const dto = await apiClient.get<UserListDto>(`/api/v1/users?${search.toString()}`, signal)
      return mapUserListDtoToModel(dto)
    },
  })
}

export function useUser(id: UserId): UseQueryResult<User, AppError> {
  return useQuery({
    queryKey: userKeys.detail(id),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<UserDto>(`/api/v1/users/${id}`, signal)
      return mapUserDtoToModel(dto)
    },
  })
}
