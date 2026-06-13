import { env } from '@/shared/config/env'
import { AppError, parseProblemDetails } from '@/shared/api/errors'
import { authToken } from '@/shared/api/auth-token'

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'

interface RequestOptions {
  method?: HttpMethod
  body?: unknown
  signal?: AbortSignal
}

function authHeaders(): Record<string, string> {
  const token = authToken.get()
  return token !== null ? { Authorization: `Bearer ${token}` } : {}
}

function handleErrorResponse(response: Response, path: string): void {
  if (response.status === 401 && !path.includes('/auth/login')) {
    authToken.clear()
    window.location.href = '/login'
  }
  if (response.status === 403) {
    window.location.href = '/forbidden'
  }
}

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const base = env.apiBaseUrl.replace(/\/$/, '')
  const headers: Record<string, string> = { ...authHeaders() }

  const init: RequestInit = {
    method: options.method ?? 'GET',
    headers,
  }

  if (options.body !== undefined) {
    headers['Content-Type'] = 'application/json'
    init.body = JSON.stringify(options.body)
  }

  if (options.signal !== undefined) {
    init.signal = options.signal
  }

  const response = await fetch(`${base}${path}`, init)

  if (!response.ok) {
    handleErrorResponse(response, path)
    throw await parseProblemDetails(response)
  }

  if (response.status === 204) {
    return undefined as T
  }

  return (await response.json()) as T
}

export const apiClient = {
  get<T>(path: string, signal?: AbortSignal): Promise<T> {
    return request<T>(path, signal !== undefined ? { signal } : {})
  },
  post<T>(path: string, body: unknown): Promise<T> {
    return request<T>(path, { method: 'POST', body })
  },
  patch<T>(path: string, body: unknown): Promise<T> {
    return request<T>(path, { method: 'PATCH', body })
  },
  delete(path: string): Promise<undefined> {
    return request<undefined>(path, { method: 'DELETE' })
  },
}

export { AppError }
