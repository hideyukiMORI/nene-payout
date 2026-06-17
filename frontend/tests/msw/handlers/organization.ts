import { http, HttpResponse } from 'msw'
import type { OrganizationDto, OrganizationListDto } from '@/entities/organization/api-types'
import { organizationDto } from '../../factories/organization'

export const organizationHandlers = [
  http.get('*/api/v1/organization', () => HttpResponse.json(organizationDto())),
  http.patch('*/api/v1/organization', async ({ request }) => {
    const body = (await request.json()) as Partial<OrganizationDto>
    return HttpResponse.json(organizationDto({ name: body.name ?? organizationDto().name }))
  }),
]

/** Cross-tenant management (superadmin; /api/v1/organizations). */
export const organizationsManagementHandlers = [
  http.get('*/api/v1/organizations', () => {
    const body: OrganizationListDto = {
      items: [
        organizationDto(),
        organizationDto({
          id: '01ORG00000000000000000002',
          slug: 'beta',
          name: 'Beta Inc.',
          custom_domain: null,
          is_active: false,
        }),
      ],
      limit: 20,
      offset: 0,
      total: 2,
    }
    return HttpResponse.json(body)
  }),
  http.get('*/api/v1/organizations/:id', ({ params }) => {
    const id = typeof params.id === 'string' ? params.id : '01ORG00000000000000000001'
    return HttpResponse.json(organizationDto({ id }))
  }),
  http.post('*/api/v1/organizations', async ({ request }) => {
    const body = (await request.json()) as Partial<OrganizationDto>
    return HttpResponse.json(
      organizationDto({
        id: '01ORG00000000000000000009',
        slug: body.slug ?? 'newco',
        name: body.name ?? 'New Co.',
        custom_domain: body.custom_domain ?? null,
      }),
      { status: 201 },
    )
  }),
  http.patch('*/api/v1/organizations/:id', async ({ params, request }) => {
    const id = typeof params.id === 'string' ? params.id : '01ORG00000000000000000001'
    const body = (await request.json()) as Partial<OrganizationDto>
    return HttpResponse.json(organizationDto({ id, name: body.name ?? organizationDto().name }))
  }),
  http.post('*/api/v1/organizations/:id/deactivate', ({ params }) => {
    const id = typeof params.id === 'string' ? params.id : '01ORG00000000000000000001'
    return HttpResponse.json(organizationDto({ id, is_active: false }))
  }),
]

export const emptyOrganizationsManagementHandlers = [
  http.get('*/api/v1/organizations', () => {
    const body: OrganizationListDto = { items: [], limit: 20, offset: 0, total: 0 }
    return HttpResponse.json(body)
  }),
]

export const conflictCreateOrganizationHandlers = [
  http.post('*/api/v1/organizations', () =>
    HttpResponse.json(
      {
        type: 'https://nene-payout.dev/problems/conflict',
        title: 'Conflict',
        status: 409,
      },
      { status: 409 },
    ),
  ),
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
