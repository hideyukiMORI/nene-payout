import { http, HttpResponse } from 'msw'
import type { ReceivedInvoiceListDto } from '@/entities/received-invoice/api-types'
import { receivedInvoiceDto } from '../../factories/received-invoice'

export const receivedInvoiceHandlers = [
  http.get('*/api/v1/received-invoices', () => {
    const body: ReceivedInvoiceListDto = {
      items: [
        receivedInvoiceDto(),
        receivedInvoiceDto({
          id: '01INV0000000000000000000002',
          amount: 88000,
          status: 'paid',
        }),
      ],
      limit: 20,
      offset: 0,
      total: 2,
    }
    return HttpResponse.json(body)
  }),
]

export const receivedInvoiceDetailHandlers = [
  http.get('*/api/v1/received-invoices/:id', ({ params }) => {
    const id = typeof params.id === 'string' ? params.id : '01INV0000000000000000000001'
    return HttpResponse.json(receivedInvoiceDto({ id }))
  }),
]

export const emptyReceivedInvoiceHandlers = [
  http.get('*/api/v1/received-invoices', () => {
    const body: ReceivedInvoiceListDto = { items: [], limit: 20, offset: 0, total: 0 }
    return HttpResponse.json(body)
  }),
]

export const errorReceivedInvoiceHandlers = [
  http.get('*/api/v1/received-invoices', () =>
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
