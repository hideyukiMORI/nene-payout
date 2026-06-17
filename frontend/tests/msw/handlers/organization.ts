import { http, HttpResponse } from 'msw'
import type { OrganizationDto } from '@/entities/organization/api-types'
import { organizationDto } from '../../factories/organization'

export const organizationHandlers = [
  http.get('*/api/v1/organization', () => HttpResponse.json(organizationDto())),
  http.patch('*/api/v1/organization', async ({ request }) => {
    const body = (await request.json()) as Partial<OrganizationDto>
    return HttpResponse.json(organizationDto({ name: body.name ?? organizationDto().name }))
  }),
]

export const errorOrganizationHandlers = [
  http.get('*/api/v1/organization', () =>
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
