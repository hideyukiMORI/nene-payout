import {
  createNene2Transport,
  isNene2ClientError,
  isValidationProblemDetails,
  type Nene2ClientError,
} from '@hideyukimori/nene2-client'
import { env } from '@/shared/config/env'
import { AppError, type ProblemDetails } from '@/shared/api/errors'
import { tokenStore } from '@/shared/api/auth-token'

/**
 * Fleet-standard transport (`@hideyukimori/nene2-client`, issue #102): every
 * request mirrors the bearer token onto `Authorization` *and*
 * `X-Authorization` so shared-hosting proxies that strip the standard header
 * still authenticate. `apiClient` below is a thin adapter that keeps this
 * product's existing surface (`get/post/postForm/patch/delete`) verbatim so
 * call sites did not need to change.
 */
const transport = createNene2Transport({
  baseUrl: env.apiBaseUrl,
  tokenStore,
  // Look up `fetch` at call time (not bind it once at module load): tests
  // patch `globalThis.fetch` via msw's `server.listen()`, which can run
  // after this module is first imported.
  fetch: (input, init) => globalThis.fetch(input, init),
  onUnauthorized: () => {
    window.location.href = '/login'
  },
  onForbidden: () => {
    window.location.href = '/forbidden'
  },
})

/** Maps the package's `Nene2ClientError` to this product's `AppError` (unchanged shape/behavior for callers). */
function toAppError(error: Nene2ClientError): AppError {
  const problem = error.problem
  if (problem === undefined) {
    return new AppError({ type: 'about:blank', title: 'Request failed', status: error.status })
  }

  const mapped: ProblemDetails = {
    type: problem.type,
    title: problem.title,
    status: problem.status,
  }
  if (problem.instance !== undefined) {
    mapped.instance = problem.instance
  }
  if (problem.detail !== undefined) {
    mapped.detail = problem.detail
  }
  if (isValidationProblemDetails(problem)) {
    mapped.errors = problem.errors
  }
  return new AppError(mapped)
}

async function unwrap<T>(promise: Promise<T>): Promise<T> {
  try {
    return await promise
  } catch (error) {
    if (isNene2ClientError(error)) {
      throw toAppError(error)
    }
    throw error
  }
}

export const apiClient = {
  get<T>(path: string, signal?: AbortSignal): Promise<T> {
    return unwrap(transport.get<T>(path, signal !== undefined ? { signal } : {}))
  },
  post<T>(path: string, body: unknown): Promise<T> {
    return unwrap(transport.post<T>(path, body))
  },
  /** multipart/form-data upload; `Content-Type` (with boundary) is left to the browser. */
  postForm<T>(path: string, formData: FormData): Promise<T> {
    return unwrap(transport.upload<T>(path, formData))
  },
  patch<T>(path: string, body: unknown): Promise<T> {
    return unwrap(transport.patch<T>(path, body))
  },
  delete(path: string): Promise<undefined> {
    return unwrap(transport.delete<undefined>(path))
  },
}

export { AppError }
