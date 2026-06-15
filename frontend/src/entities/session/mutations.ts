import { useMutation, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import { authToken } from '@/shared/api/auth-token'
import type { LoginRequestDto, LoginResponseDto } from './api-types'
import type { Credentials } from './model'

/**
 * Exchanges credentials for a bearer token and stores it. The token store lives
 * in shared/api (features/pages may not import it), so the session entity is the
 * single place that establishes a session. The API remains the source of truth
 * for authorization; this only unlocks the fail-closed AuthGate.
 */
export function useLogin(): UseMutationResult<void, AppError, Credentials> {
  return useMutation({
    mutationFn: async ({ email, password }: Credentials) => {
      const dto = await apiClient.post<LoginResponseDto>('/api/v1/auth/login', {
        email,
        password,
      } satisfies LoginRequestDto)
      authToken.set(dto.token)
    },
  })
}
