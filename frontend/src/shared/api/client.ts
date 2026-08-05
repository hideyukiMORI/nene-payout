import {
  createNene2Transport,
  createSessionTokenStore,
  isNene2ClientError,
  isValidationProblemDetails,
  type BlobDownload,
  type Nene2ClientError,
  type Nene2Transport,
  type Nene2TransportConfig,
  type RawBodyRequestOptions,
  type SessionTokenStore,
  type TokenStore,
  type TransportRequestOptions,
} from '@hideyukimori/nene2-client'
import { env } from '@/shared/config/env'
import { AppError, type ProblemDetails } from '@/shared/api/errors'

/** Fleet-wide naming is `nene_<product>_token` (frontend-standards 02). */
const STORAGE_KEY = 'nene_payout_token'

/**
 * Fleet-standard bearer token store (`createSessionTokenStore`): sessionStorage
 * (#152/#153). A factory rather than a bare constant so the L2 contract (#280)
 * can build the store through *this* wiring instead of restating the key and
 * store type in the test — a restatement would drift, and A-2 keeps this file
 * as the only `@hideyukimori/nene2-client` contact point regardless.
 *
 * `storage` is supplied only when the contract forces a storage failure (C2-6);
 * omitting it uses the ambient `sessionStorage`, which is the production path.
 */
export function createProductTokenStore(storage?: Storage): SessionTokenStore {
  return createSessionTokenStore({ key: STORAGE_KEY, storage })
}

/**
 * The one store for this product. `shared/api/auth-token.ts` re-exports it and
 * wraps it for call sites, and the transport below is handed the same instance,
 * so there is exactly one source of truth for the session.
 */
export const tokenStore = createProductTokenStore()

/**
 * The product's transport wiring, as a factory so the L2 transport contract
 * (`@hideyukimori/nene2-client/testing`, #280) can build the *real* config
 * against its own token store instead of asserting on a copy of it. Everything
 * the contract can get wrong about this product lives in this return value.
 */
export function buildTransportConfig(store: TokenStore): Nene2TransportConfig {
  return {
    baseUrl: env.apiBaseUrl,
    tokenStore: store,
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
  }
}

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

/**
 * This product's adapter over the fleet transport: every method routes through
 * `unwrap`, so a `Nene2ClientError` reaches call sites as this product's
 * `AppError`. Exposed as a full {@link Nene2Transport} — not just the five
 * methods `apiClient` re-exports — because the L2 contract (#280) declares
 * `surface: 'adapter'` and drives this object across the whole surface. If it
 * only covered the methods we happen to call today, a future call site added on
 * a path that bypasses this mapping would be invisible to the contract.
 */
export function createApiTransport(config: Nene2TransportConfig): Nene2Transport {
  const transport = createNene2Transport(config)

  return {
    get<T>(path: string, options?: TransportRequestOptions): Promise<T> {
      return unwrap(transport.get<T>(path, options))
    },
    post<T>(path: string, body?: unknown, options?: TransportRequestOptions): Promise<T> {
      return unwrap(transport.post<T>(path, body, options))
    },
    put<T>(path: string, body?: unknown, options?: TransportRequestOptions): Promise<T> {
      return unwrap(transport.put<T>(path, body, options))
    },
    patch<T>(path: string, body?: unknown, options?: TransportRequestOptions): Promise<T> {
      return unwrap(transport.patch<T>(path, body, options))
    },
    delete<T = void>(path: string, options?: TransportRequestOptions): Promise<T> {
      return unwrap(transport.delete<T>(path, options))
    },
    getBlob(path: string, options?: TransportRequestOptions): Promise<BlobDownload> {
      return unwrap(transport.getBlob(path, options))
    },
    postBlob(
      path: string,
      body?: unknown,
      options?: TransportRequestOptions,
    ): Promise<BlobDownload> {
      return unwrap(transport.postBlob(path, body, options))
    },
    upload<T>(path: string, formData: FormData, options?: TransportRequestOptions): Promise<T> {
      return unwrap(transport.upload<T>(path, formData, options))
    },
    postCsv<T>(path: string, csv: string, options?: RawBodyRequestOptions): Promise<T> {
      return unwrap(transport.postCsv<T>(path, csv, options))
    },
    postBytes<T>(path: string, body: Blob, options?: RawBodyRequestOptions): Promise<T> {
      return unwrap(transport.postBytes<T>(path, body, options))
    },
    recover(): Promise<boolean> {
      return transport.recover()
    },
  }
}

const apiTransport = createApiTransport(buildTransportConfig(tokenStore))

/**
 * The surface this product's features call. A thin facade over
 * {@link createApiTransport} that keeps the pre-migration signatures
 * (`get/post/postForm/patch/delete`) verbatim so call sites did not change.
 */
export const apiClient = {
  get<T>(path: string, signal?: AbortSignal): Promise<T> {
    return apiTransport.get<T>(path, signal !== undefined ? { signal } : {})
  },
  post<T>(path: string, body: unknown): Promise<T> {
    return apiTransport.post<T>(path, body)
  },
  /** multipart/form-data upload; `Content-Type` (with boundary) is left to the browser. */
  postForm<T>(path: string, formData: FormData): Promise<T> {
    return apiTransport.upload<T>(path, formData)
  },
  patch<T>(path: string, body: unknown): Promise<T> {
    return apiTransport.patch<T>(path, body)
  },
  delete(path: string): Promise<undefined> {
    return apiTransport.delete<undefined>(path)
  },
}

export { AppError }
