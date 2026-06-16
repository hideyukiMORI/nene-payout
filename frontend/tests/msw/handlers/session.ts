import { http, HttpResponse } from 'msw'
import type { CurrentUserDto, LoginResponseDto } from '@/entities/session/api-types'

export const TEST_TOKEN = 'test.jwt.token'

export const sessionHandlers = [
  http.post('*/api/v1/auth/login', () => {
    const body: LoginResponseDto = { token: TEST_TOKEN }
    return HttpResponse.json(body)
  }),
]

const DEFAULT_CURRENT_USER: CurrentUserDto = {
  id: '01J9Z0VENDOR000000000USER',
  email: 'admin@example.com',
  role: 'admin',
  organization_id: '01J9Z0ORG0000000000000001',
}

/** GET /auth/me handler; pass a partial to vary the role/identity per test. */
export function currentUserHandlers(user: Partial<CurrentUserDto> = {}) {
  return [
    http.get('*/api/v1/auth/me', () =>
      HttpResponse.json<CurrentUserDto>({ ...DEFAULT_CURRENT_USER, ...user }),
    ),
  ]
}

/** Default (admin) /auth/me handler registered on the shared server. */
export const sessionMeHandlers = currentUserHandlers()

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
