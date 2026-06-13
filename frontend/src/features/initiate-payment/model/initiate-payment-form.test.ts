import { describe, it, expect } from 'vitest'
import { formValuesToInitiateInput, initiatePaymentFormSchema } from './initiate-payment-form'

describe('initiatePaymentFormSchema', () => {
  it('accepts a supported gateway', () => {
    expect(initiatePaymentFormSchema.safeParse({ gateway: 'stripe' }).success).toBe(true)
    expect(initiatePaymentFormSchema.safeParse({ gateway: 'gmo_pg' }).success).toBe(true)
  })

  it('rejects an unsupported gateway', () => {
    expect(initiatePaymentFormSchema.safeParse({ gateway: 'paypal' }).success).toBe(false)
  })
})

describe('formValuesToInitiateInput', () => {
  it('maps the gateway and attaches the return url', () => {
    const input = formValuesToInitiateInput(
      { gateway: 'stripe' },
      'https://app.example/received-invoices',
    )
    expect(input).toEqual({
      gateway: 'stripe',
      returnUrl: 'https://app.example/received-invoices',
    })
  })
})
