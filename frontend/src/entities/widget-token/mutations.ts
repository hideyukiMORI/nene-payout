import { useMutation, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { WidgetTokenResponseDto } from './api-types'
import { mapWidgetTokenDtoToModel } from './mapper'
import type { WidgetToken } from './model'

/** Issues a fresh organization-scoped widget token + embed snippet (admin only). */
export function useGenerateWidgetToken(): UseMutationResult<WidgetToken, AppError, void> {
  return useMutation({
    mutationFn: async () => {
      const dto = await apiClient.post<WidgetTokenResponseDto>('/api/v1/widget-tokens', {})
      return mapWidgetTokenDtoToModel(dto)
    },
  })
}
