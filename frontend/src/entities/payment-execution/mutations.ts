import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { InitiatePaymentResultDto } from './api-types'
import { mapInitiatePaymentInputToDto, mapInitiatePaymentResultDtoToModel } from './mapper'
import type { InitiatePaymentInput, InitiatePaymentResult } from './model'
import { paymentExecutionKeys } from './query-keys'

export function useInitiatePayment(): UseMutationResult<
  InitiatePaymentResult,
  AppError,
  { receivedInvoiceId: string; input: InitiatePaymentInput }
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ receivedInvoiceId, input }) => {
      const dto = await apiClient.post<InitiatePaymentResultDto>(
        `/api/v1/received-invoices/${receivedInvoiceId}/payments`,
        mapInitiatePaymentInputToDto(input),
      )
      return mapInitiatePaymentResultDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: paymentExecutionKeys.lists() })
    },
  })
}
