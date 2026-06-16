import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import { authToken } from '@/shared/api/auth-token'
import type { CurrentUserDto } from './api-types'
import { mapCurrentUserDtoToModel } from './mapper'
import type { CurrentUser } from './model'
import { sessionKeys } from './query-keys'

/**
 * Loads the authenticated user (GET /auth/me) so the UI can mirror role
 * capabilities for nav/route visibility. Disabled without a token; the API
 * stays the source of truth for authorization.
 */
export function useCurrentUser(): UseQueryResult<CurrentUser, AppError> {
  return useQuery({
    queryKey: sessionKeys.currentUser(),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<CurrentUserDto>('/api/v1/auth/me', signal)
      return mapCurrentUserDtoToModel(dto)
    },
    enabled: authToken.get() !== null,
    staleTime: Infinity,
  })
}
