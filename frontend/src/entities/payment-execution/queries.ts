import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { PaymentExecutionDto, PaymentExecutionListDto } from './api-types'
import type { PaymentExecutionId } from './ids'
import { mapPaymentExecutionDtoToModel, mapPaymentExecutionListDtoToModel } from './mapper'
import type { PaymentExecution, PaymentExecutionList, PaymentExecutionStatus } from './model'
import { paymentExecutionKeys } from './query-keys'

export interface PaymentExecutionListParams {
  limit: number
  offset: number
  status: PaymentExecutionStatus | null
  receivedInvoiceId: string | null
}

const DEFAULT_LIST_PARAMS: PaymentExecutionListParams = {
  limit: 20,
  offset: 0,
  status: null,
  receivedInvoiceId: null,
}

export function usePaymentExecutionList(
  params: PaymentExecutionListParams = DEFAULT_LIST_PARAMS,
): UseQueryResult<PaymentExecutionList, AppError> {
  return useQuery({
    queryKey: paymentExecutionKeys.list(params),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(params.limit),
        offset: String(params.offset),
      })
      if (params.status !== null) {
        search.set('status', params.status)
      }
      if (params.receivedInvoiceId !== null) {
        search.set('received_invoice_id', params.receivedInvoiceId)
      }
      const dto = await apiClient.get<PaymentExecutionListDto>(
        `/api/v1/payment-executions?${search.toString()}`,
        signal,
      )
      return mapPaymentExecutionListDtoToModel(dto)
    },
  })
}

export function usePaymentExecution(
  id: PaymentExecutionId,
): UseQueryResult<PaymentExecution, AppError> {
  return useQuery({
    queryKey: paymentExecutionKeys.detail(id),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<PaymentExecutionDto>(
        `/api/v1/payment-executions/${id}`,
        signal,
      )
      return mapPaymentExecutionDtoToModel(dto)
    },
  })
}
