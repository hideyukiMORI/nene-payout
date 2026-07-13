export interface ApiValidationError {
  field: string
  message: string
  code: string
}

export interface ProblemDetails {
  type: string
  title: string
  status: number
  detail?: string
  instance?: string
  errors?: readonly ApiValidationError[]
}

/** Typed error thrown by the API client from an RFC 9457 Problem Details response. */
export class AppError extends Error {
  readonly status: number
  readonly type: string
  readonly title: string
  readonly detail?: string
  readonly errors?: readonly ApiValidationError[]

  constructor(problem: ProblemDetails) {
    super(problem.title)
    this.name = 'AppError'
    this.status = problem.status
    this.type = problem.type
    this.title = problem.title
    if (problem.detail !== undefined) {
      this.detail = problem.detail
    }
    if (problem.errors !== undefined) {
      this.errors = problem.errors
    }
  }

  get isRetryable(): boolean {
    return this.status >= 500 || this.status === 429
  }
}
