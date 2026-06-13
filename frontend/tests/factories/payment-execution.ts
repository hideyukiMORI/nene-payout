import type { PaymentExecutionDto } from '@/entities/payment-execution/api-types'

export function paymentExecutionDto(
  overrides: Partial<PaymentExecutionDto> = {},
): PaymentExecutionDto {
  return {
    id: '01PAY0000000000000000000001',
    organization_id: '01ORG00000000000000000001',
    received_invoice_id: '01INV0000000000000000000001',
    amount: 100000,
    charge_amount: 103300,
    processing_fee: 3300,
    gateway: 'stripe',
    gateway_reference: 'pi_123',
    status: 'succeeded',
    initiated_at: '2026-06-14T01:00:00Z',
    completed_at: '2026-06-14T01:00:05Z',
    ...overrides,
  }
}
