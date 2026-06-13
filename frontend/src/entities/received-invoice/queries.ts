import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { ReceivedInvoiceDto, ReceivedInvoiceListDto } from './api-types'
import type { ReceivedInvoiceId } from './ids'
import { mapReceivedInvoiceDtoToModel, mapReceivedInvoiceListDtoToModel } from './mapper'
import type { ReceivedInvoice, ReceivedInvoiceList, ReceivedInvoiceStatus } from './model'
import { receivedInvoiceKeys } from './query-keys'

export interface ReceivedInvoiceListParams {
  limit: number
  offset: number
  status: ReceivedInvoiceStatus | null
}

const DEFAULT_LIST_PARAMS: ReceivedInvoiceListParams = { limit: 20, offset: 0, status: null }

export function useReceivedInvoiceList(
  params: ReceivedInvoiceListParams = DEFAULT_LIST_PARAMS,
): UseQueryResult<ReceivedInvoiceList, AppError> {
  return useQuery({
    queryKey: receivedInvoiceKeys.list(params),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(params.limit),
        offset: String(params.offset),
      })
      if (params.status !== null) {
        search.set('status', params.status)
      }
      const dto = await apiClient.get<ReceivedInvoiceListDto>(
        `/api/v1/received-invoices?${search.toString()}`,
        signal,
      )
      return mapReceivedInvoiceListDtoToModel(dto)
    },
  })
}

export function useReceivedInvoice(
  id: ReceivedInvoiceId,
): UseQueryResult<ReceivedInvoice, AppError> {
  return useQuery({
    queryKey: receivedInvoiceKeys.detail(id),
    queryFn: async ({ signal }) => {
      const dto = await apiClient.get<ReceivedInvoiceDto>(`/api/v1/received-invoices/${id}`, signal)
      return mapReceivedInvoiceDtoToModel(dto)
    },
  })
}
