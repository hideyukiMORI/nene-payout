import { useMutation, useQueryClient, type UseMutationResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { ReceivedInvoiceDto } from './api-types'
import type { ReceivedInvoiceId } from './ids'
import {
  mapCreateReceivedInvoiceInputToDto,
  mapReceivedInvoiceDtoToModel,
  mapUpdateReceivedInvoiceInputToDto,
} from './mapper'
import type {
  CreateReceivedInvoiceInput,
  ReceivedInvoice,
  UpdateReceivedInvoiceInput,
} from './model'
import { receivedInvoiceKeys } from './query-keys'

export function useCreateReceivedInvoice(): UseMutationResult<
  ReceivedInvoice,
  AppError,
  CreateReceivedInvoiceInput
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (input) => {
      const dto = await apiClient.post<ReceivedInvoiceDto>(
        '/api/v1/received-invoices',
        mapCreateReceivedInvoiceInputToDto(input),
      )
      return mapReceivedInvoiceDtoToModel(dto)
    },
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.lists() })
    },
  })
}

export function useUpdateReceivedInvoice(): UseMutationResult<
  ReceivedInvoice,
  AppError,
  { id: ReceivedInvoiceId; input: UpdateReceivedInvoiceInput }
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, input }) => {
      const dto = await apiClient.patch<ReceivedInvoiceDto>(
        `/api/v1/received-invoices/${id}`,
        mapUpdateReceivedInvoiceInputToDto(input),
      )
      return mapReceivedInvoiceDtoToModel(dto)
    },
    onSuccess: async (_data, variables) => {
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.lists() })
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.detail(variables.id) })
    },
  })
}

export function useVoidReceivedInvoice(): UseMutationResult<
  ReceivedInvoice,
  AppError,
  ReceivedInvoiceId
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id) => {
      const dto = await apiClient.post<ReceivedInvoiceDto>(
        `/api/v1/received-invoices/${id}/void`,
        {},
      )
      return mapReceivedInvoiceDtoToModel(dto)
    },
    onSuccess: async (_data, id) => {
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.lists() })
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.detail(id) })
    },
  })
}

export function useAttachReceivedInvoicePdf(): UseMutationResult<
  ReceivedInvoice,
  AppError,
  { id: ReceivedInvoiceId; file: File }
> {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, file }) => {
      const formData = new FormData()
      formData.append('file', file)
      const dto = await apiClient.postForm<ReceivedInvoiceDto>(
        `/api/v1/received-invoices/${id}/pdf`,
        formData,
      )
      return mapReceivedInvoiceDtoToModel(dto)
    },
    onSuccess: async (_data, variables) => {
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.lists() })
      await queryClient.invalidateQueries({ queryKey: receivedInvoiceKeys.detail(variables.id) })
    },
  })
}
