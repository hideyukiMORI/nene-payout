import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { http, HttpResponse } from 'msw'
import { mswServer } from '../../../tests/msw/server'
import { vendorDto } from '../../../tests/factories/vendor'
import { apiClient, AppError } from './client'
import { tokenStore } from './auth-token'

const TOKEN = 'test.jwt.token'

describe('apiClient (nene2-client transport adapter)', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  beforeEach(() => {
    tokenStore.setToken(TOKEN)
  })
  afterEach(() => {
    tokenStore.clearToken()
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('mirrors the bearer token onto both Authorization and X-Authorization on a representative GET', async () => {
    let authorization: string | null = null
    let xAuthorization: string | null = null

    mswServer.use(
      http.get('*/api/v1/vendors', ({ request }) => {
        authorization = request.headers.get('Authorization')
        xAuthorization = request.headers.get('X-Authorization')
        return HttpResponse.json({ items: [vendorDto()], limit: 20, offset: 0, total: 1 })
      }),
    )

    await apiClient.get('/api/v1/vendors')

    expect(authorization).toBe(`Bearer ${TOKEN}`)
    expect(xAuthorization).toBe(`Bearer ${TOKEN}`)
  })

  it('mirrors both headers on POST/PATCH/postForm/delete as well', async () => {
    const seen: Record<string, { auth: string | null; xAuth: string | null }> = {}

    mswServer.use(
      http.post('*/api/v1/vendors', ({ request }) => {
        seen['post'] = {
          auth: request.headers.get('Authorization'),
          xAuth: request.headers.get('X-Authorization'),
        }
        return HttpResponse.json(vendorDto())
      }),
      http.patch('*/api/v1/vendors/:id', ({ request }) => {
        seen['patch'] = {
          auth: request.headers.get('Authorization'),
          xAuth: request.headers.get('X-Authorization'),
        }
        return HttpResponse.json(vendorDto())
      }),
      http.post('*/api/v1/received-invoices/upload', ({ request }) => {
        seen['postForm'] = {
          auth: request.headers.get('Authorization'),
          xAuth: request.headers.get('X-Authorization'),
        }
        return HttpResponse.json({ ok: true })
      }),
      http.delete('*/api/v1/vendors/:id', ({ request }) => {
        seen['delete'] = {
          auth: request.headers.get('Authorization'),
          xAuth: request.headers.get('X-Authorization'),
        }
        return new HttpResponse(null, { status: 204 })
      }),
    )

    await apiClient.post('/api/v1/vendors', { name: 'x' })
    await apiClient.patch('/api/v1/vendors/1', { name: 'y' })
    await apiClient.postForm('/api/v1/received-invoices/upload', new FormData())
    await apiClient.delete('/api/v1/vendors/1')

    for (const method of ['post', 'patch', 'postForm', 'delete']) {
      expect(seen[method]?.auth, `${method} Authorization`).toBe(`Bearer ${TOKEN}`)
      expect(seen[method]?.xAuth, `${method} X-Authorization`).toBe(`Bearer ${TOKEN}`)
    }
  })

  it('sends no auth headers when signed out', async () => {
    tokenStore.clearToken()
    let authorization: string | null = null

    mswServer.use(
      http.get('*/api/v1/vendors', ({ request }) => {
        authorization = request.headers.get('Authorization')
        return HttpResponse.json({ items: [], limit: 20, offset: 0, total: 0 })
      }),
    )

    await apiClient.get('/api/v1/vendors')

    expect(authorization).toBeNull()
  })

  it('maps a Problem Details error response to AppError (unchanged public shape)', async () => {
    mswServer.use(
      http.get('*/api/v1/vendors', () =>
        HttpResponse.json(
          {
            type: 'https://nene-payout.dev/problems/internal-server-error',
            title: 'Server Error',
            status: 500,
          },
          { status: 500 },
        ),
      ),
    )

    await expect(apiClient.get('/api/v1/vendors')).rejects.toMatchObject({
      status: 500,
      title: 'Server Error',
      type: 'https://nene-payout.dev/problems/internal-server-error',
    })
    await expect(apiClient.get('/api/v1/vendors')).rejects.toBeInstanceOf(AppError)
  })
})
