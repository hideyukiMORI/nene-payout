import { http, HttpResponse } from 'msw'
import type { UserListDto } from '@/entities/user/api-types'
import { userDto } from '../../factories/user'

export const userHandlers = [
  http.get('*/api/v1/users', () => {
    const body: UserListDto = {
      items: [
        userDto(),
        userDto({
          id: '01USER0000000000000000002',
          email: 'operator@example.com',
          role: 'operator',
          status: 'invited',
        }),
      ],
      limit: 20,
      offset: 0,
      total: 2,
    }
    return HttpResponse.json(body)
  }),
]

export const userDetailHandlers = [
  http.get('*/api/v1/users/:id', ({ params }) => {
    const id = typeof params.id === 'string' ? params.id : '01USER0000000000000000001'
    return HttpResponse.json(userDto({ id }))
  }),
]

export const emptyUserHandlers = [
  http.get('*/api/v1/users', () => {
    const body: UserListDto = { items: [], limit: 20, offset: 0, total: 0 }
    return HttpResponse.json(body)
  }),
]

export const errorUserHandlers = [
  http.get('*/api/v1/users', () =>
    HttpResponse.json(
      {
        type: 'https://nene-payout.dev/problems/internal-server-error',
        title: 'Server Error',
        status: 500,
      },
      { status: 500 },
    ),
  ),
]
