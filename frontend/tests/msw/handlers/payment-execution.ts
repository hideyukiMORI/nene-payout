import { http, HttpResponse } from 'msw'
import type { PaymentExecutionListDto } from '@/entities/payment-execution/api-types'
import { paymentExecutionDto } from '../../factories/payment-execution'

export const paymentExecutionHandlers = [
  http.get('*/api/v1/payment-executions', () => {
    const body: PaymentExecutionListDto = {
      items: [
        paymentExecutionDto(),
        paymentExecutionDto({
          id: '01PAY0000000000000000000002',
          amount: 50000,
          charge_amount: null,
          processing_fee: null,
          gateway: 'gmo_pg',
          gateway_reference: null,
          status: 'initiated',
          completed_at: null,
        }),
      ],
      limit: 20,
      offset: 0,
      total: 2,
    }
    return HttpResponse.json(body)
  }),
]

export const emptyPaymentExecutionHandlers = [
  http.get('*/api/v1/payment-executions', () => {
    const body: PaymentExecutionListDto = { items: [], limit: 20, offset: 0, total: 0 }
    return HttpResponse.json(body)
  }),
]

export const errorPaymentExecutionHandlers = [
  http.get('*/api/v1/payment-executions', () =>
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
