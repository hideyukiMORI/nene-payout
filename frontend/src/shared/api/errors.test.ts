import { describe, expect, it } from 'vitest'
import { AppError, type ProblemDetails } from './errors'

/**
 * AppError is the typed error every call site inspects (status branching,
 * validation-error surfacing, retry decisions). client.test.ts covers the
 * transport mapping; these pin the class contract directly — the optional
 * field branches and the isRetryable boundary.
 */
function problem(overrides: Partial<ProblemDetails> = {}): ProblemDetails {
  return {
    type: 'https://nene-payout.dev/problems/bad-request',
    title: 'Bad Request',
    status: 400,
    ...overrides,
  }
}

describe('AppError', () => {
  it('is an Error subclass carrying name "AppError" and title as message', () => {
    const err = new AppError(problem())
    expect(err).toBeInstanceOf(Error)
    expect(err.name).toBe('AppError')
    expect(err.message).toBe('Bad Request')
  })

  it('copies the core Problem Details fields', () => {
    const err = new AppError(problem({ status: 404, type: 'about:blank', title: 'Not Found' }))
    expect(err.status).toBe(404)
    expect(err.type).toBe('about:blank')
    expect(err.title).toBe('Not Found')
  })

  describe('optional field branches', () => {
    it('leaves detail/errors undefined when absent from the problem', () => {
      const err = new AppError(problem())
      expect(err.detail).toBeUndefined()
      expect(err.errors).toBeUndefined()
    })

    it('carries detail when present', () => {
      const err = new AppError(problem({ detail: 'The name field is required.' }))
      expect(err.detail).toBe('The name field is required.')
    })

    it('carries the validation errors array when present', () => {
      const err = new AppError(
        problem({
          status: 422,
          errors: [{ field: 'email', message: 'is invalid', code: 'invalid_format' }],
        }),
      )
      expect(err.errors).toEqual([
        { field: 'email', message: 'is invalid', code: 'invalid_format' },
      ])
    })
  })

  describe('isRetryable boundary', () => {
    it('is true for 5xx server errors', () => {
      expect(new AppError(problem({ status: 500 })).isRetryable).toBe(true)
      expect(new AppError(problem({ status: 503 })).isRetryable).toBe(true)
    })

    it('is true for 429 Too Many Requests', () => {
      expect(new AppError(problem({ status: 429 })).isRetryable).toBe(true)
    })

    it('is false for other 4xx client errors', () => {
      expect(new AppError(problem({ status: 400 })).isRetryable).toBe(false)
      expect(new AppError(problem({ status: 401 })).isRetryable).toBe(false)
      expect(new AppError(problem({ status: 404 })).isRetryable).toBe(false)
      expect(new AppError(problem({ status: 422 })).isRetryable).toBe(false)
    })

    it('is false just below the 5xx boundary (499)', () => {
      expect(new AppError(problem({ status: 499 })).isRetryable).toBe(false)
    })
  })
})
