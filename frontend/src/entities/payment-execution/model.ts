import type { PaymentExecutionId } from './ids'

export type GatewayId = 'stripe' | 'gmo_pg'

export type PaymentExecutionStatus =
  'initiated' | 'succeeded' | 'failed' | 'refunded' | 'charged_back'

export interface PaymentExecution {
  id: PaymentExecutionId
  organizationId: string
  receivedInvoiceId: string
  amount: number
  chargeAmount: number | null
  processingFee: number | null
  gateway: GatewayId
  gatewayReference: string | null
  status: PaymentExecutionStatus
  initiatedAt: string
  completedAt: string | null
}

export interface PaymentExecutionList {
  items: PaymentExecution[]
  limit: number
  offset: number
  total: number | null
}

export interface InitiatePaymentInput {
  gateway: GatewayId
  returnUrl: string | null
}

export interface InitiatePaymentResult {
  paymentExecution: PaymentExecution
  gatewayRedirectUrl: string | null
  clientToken: string | null
}
