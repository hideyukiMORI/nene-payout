export type GatewayIdDto = 'stripe' | 'gmo_pg'

export type PaymentExecutionStatusDto =
  | 'initiated'
  | 'succeeded'
  | 'failed'
  | 'refunded'
  | 'charged_back'

export interface PaymentExecutionDto {
  id: string
  organization_id: string
  received_invoice_id: string
  amount: number
  charge_amount?: number | null
  processing_fee?: number | null
  gateway: GatewayIdDto
  gateway_reference?: string | null
  status: PaymentExecutionStatusDto
  initiated_at: string
  completed_at?: string | null
}

export interface PaymentExecutionListDto {
  items: PaymentExecutionDto[]
  limit: number
  offset: number
  total?: number
}

export interface InitiatePaymentDto {
  gateway: GatewayIdDto
  return_url?: string
}

export interface InitiatePaymentResultDto {
  payment_execution: PaymentExecutionDto
  gateway_redirect_url?: string | null
  client_token?: string | null
}
