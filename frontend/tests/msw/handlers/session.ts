import { http, HttpResponse } from 'msw'
import type { LoginResponseDto } from '@/entities/session/api-types'

export const TEST_TOKEN = 'test.jwt.token'

export const sessionHandlers = [
  http.post('*/api/v1/auth/login', () => {
    const body: LoginResponseDto = { token: TEST_TOKEN }
    return HttpResponse.json(body)
  }),
]

export const invalidCredentialsHandlers = [
  http.post('*/api/v1/auth/login', () =>
    HttpResponse.json(
      {
        type: 'https://nene-payout.dev/problems/unauthorized',
        title: 'Unauthorized',
        status: 401,
      },
      { status: 401 },
    ),
  ),
]
