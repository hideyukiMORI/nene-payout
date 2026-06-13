import type {
  InitiatePaymentDto,
  InitiatePaymentResultDto,
  PaymentExecutionDto,
  PaymentExecutionListDto,
} from './api-types'
import { toPaymentExecutionId } from './ids'
import type {
  InitiatePaymentInput,
  InitiatePaymentResult,
  PaymentExecution,
  PaymentExecutionList,
} from './model'

export function mapPaymentExecutionDtoToModel(dto: PaymentExecutionDto): PaymentExecution {
  return {
    id: toPaymentExecutionId(dto.id),
    organizationId: dto.organization_id,
    receivedInvoiceId: dto.received_invoice_id,
    amount: dto.amount,
    chargeAmount: dto.charge_amount ?? null,
    processingFee: dto.processing_fee ?? null,
    gateway: dto.gateway,
    gatewayReference: dto.gateway_reference ?? null,
    status: dto.status,
    initiatedAt: dto.initiated_at,
    completedAt: dto.completed_at ?? null,
  }
}

export function mapPaymentExecutionListDtoToModel(
  dto: PaymentExecutionListDto,
): PaymentExecutionList {
  return {
    items: dto.items.map(mapPaymentExecutionDtoToModel),
    limit: dto.limit,
    offset: dto.offset,
    total: dto.total ?? null,
  }
}

export function mapInitiatePaymentInputToDto(input: InitiatePaymentInput): InitiatePaymentDto {
  const dto: InitiatePaymentDto = { gateway: input.gateway }
  if (input.returnUrl !== null) {
    dto.return_url = input.returnUrl
  }
  return dto
}

export function mapInitiatePaymentResultDtoToModel(
  dto: InitiatePaymentResultDto,
): InitiatePaymentResult {
  return {
    paymentExecution: mapPaymentExecutionDtoToModel(dto.payment_execution),
    gatewayRedirectUrl: dto.gateway_redirect_url ?? null,
    clientToken: dto.client_token ?? null,
  }
}
