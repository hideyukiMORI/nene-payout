import { describe, it, expect } from 'vitest'
import type { PaymentExecutionDto, PaymentExecutionListDto } from './api-types'
import {
  mapInitiatePaymentInputToDto,
  mapPaymentExecutionDtoToModel,
  mapPaymentExecutionListDtoToModel,
} from './mapper'
import type { InitiatePaymentInput } from './model'

const dto: PaymentExecutionDto = {
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
}

describe('payment execution mapper', () => {
  it('maps snake_case DTO to camelCase model', () => {
    const model = mapPaymentExecutionDtoToModel(dto)

    expect(model.id).toBe('01PAY0000000000000000000001')
    expect(model.receivedInvoiceId).toBe('01INV0000000000000000000001')
    expect(model.amount).toBe(100000)
    expect(model.chargeAmount).toBe(103300)
    expect(model.processingFee).toBe(3300)
    expect(model.gateway).toBe('stripe')
    expect(model.gatewayReference).toBe('pi_123')
    expect(model.status).toBe('succeeded')
    expect(model.completedAt).toBe('2026-06-14T01:00:05Z')
  })

  it('defaults nullable amounts and references when absent', () => {
    const pending: PaymentExecutionDto = {
      id: '01PAY0000000000000000000002',
      organization_id: '01ORG00000000000000000001',
      received_invoice_id: '01INV0000000000000000000001',
      amount: 5000,
      gateway: 'gmo_pg',
      status: 'initiated',
      initiated_at: '2026-06-14T02:00:00Z',
    }
    const model = mapPaymentExecutionDtoToModel(pending)

    expect(model.chargeAmount).toBeNull()
    expect(model.processingFee).toBeNull()
    expect(model.gatewayReference).toBeNull()
    expect(model.completedAt).toBeNull()
  })

  it('maps the list envelope and defaults total to null when absent', () => {
    const listDto: PaymentExecutionListDto = { items: [dto], limit: 20, offset: 0 }
    const list = mapPaymentExecutionListDtoToModel(listDto)

    expect(list.items).toHaveLength(1)
    expect(list.total).toBeNull()
  })

  it('omits return_url from the initiate DTO when null', () => {
    const input: InitiatePaymentInput = { gateway: 'stripe', returnUrl: null }
    expect(mapInitiatePaymentInputToDto(input)).toEqual({ gateway: 'stripe' })
  })

  it('includes return_url in the initiate DTO when present', () => {
    const input: InitiatePaymentInput = {
      gateway: 'stripe',
      returnUrl: 'https://app.example/return',
    }
    expect(mapInitiatePaymentInputToDto(input)).toEqual({
      gateway: 'stripe',
      return_url: 'https://app.example/return',
    })
  })
})
