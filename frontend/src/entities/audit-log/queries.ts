import { useQuery, type UseQueryResult } from '@tanstack/react-query'
import { apiClient, AppError } from '@/shared/api/client'
import type { AuditLogListDto } from './api-types'
import { mapAuditLogListDtoToModel } from './mapper'
import type { AuditLogList } from './model'
import { auditLogKeys } from './query-keys'

export interface AuditLogListParams {
  limit: number
  offset: number
}

const DEFAULT_LIST_PARAMS: AuditLogListParams = {
  limit: 20,
  offset: 0,
}

export function useAuditLogList(
  params: AuditLogListParams = DEFAULT_LIST_PARAMS,
): UseQueryResult<AuditLogList, AppError> {
  return useQuery({
    queryKey: auditLogKeys.list(params),
    queryFn: async ({ signal }) => {
      const search = new URLSearchParams({
        limit: String(params.limit),
        offset: String(params.offset),
      })
      const dto = await apiClient.get<AuditLogListDto>(
        `/api/v1/audit-logs?${search.toString()}`,
        signal,
      )
      return mapAuditLogListDtoToModel(dto)
    },
  })
}
