import { http, HttpResponse } from 'msw'
import type { VendorListDto } from '@/entities/vendor/api-types'
import { vendorDto } from '../../factories/vendor'

export const vendorHandlers = [
  http.get('*/api/v1/vendors', () => {
    const body: VendorListDto = {
      items: [vendorDto(), vendorDto({ id: '01VENDOR000000000000000002', name: '別の仕入先' })],
      limit: 20,
      offset: 0,
      total: 2,
    }
    return HttpResponse.json(body)
  }),
]

export const vendorDetailHandlers = [
  http.get('*/api/v1/vendors/:id', ({ params }) => {
    const id = typeof params.id === 'string' ? params.id : '01VENDOR000000000000000001'
    return HttpResponse.json(vendorDto({ id }))
  }),
]

export const emptyVendorHandlers = [
  http.get('*/api/v1/vendors', () => {
    const body: VendorListDto = { items: [], limit: 20, offset: 0, total: 0 }
    return HttpResponse.json(body)
  }),
]

export const errorVendorHandlers = [
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
]
