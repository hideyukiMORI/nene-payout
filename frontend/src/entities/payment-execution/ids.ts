declare const paymentExecutionIdBrand: unique symbol

export type PaymentExecutionId = string & { readonly [paymentExecutionIdBrand]: never }

export function toPaymentExecutionId(value: string): PaymentExecutionId {
  return value as PaymentExecutionId
}
