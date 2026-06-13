import { z } from 'zod'
import type { GatewayId, InitiatePaymentInput } from '@/entities/payment-execution'
import type { MessageKey } from '@/shared/i18n'

/** Selectable payment gateways (GatewayId values). */
export const GATEWAY_VALUES = ['stripe', 'gmo_pg'] as const

export const initiatePaymentFormSchema = z.object({
  gateway: z.enum(GATEWAY_VALUES),
})

export type InitiatePaymentFormValues = z.infer<typeof initiatePaymentFormSchema>

export const GATEWAY_LABEL_KEY: Record<GatewayId, MessageKey> = {
  stripe: 'admin.payments.gateway.stripe',
  gmo_pg: 'admin.payments.gateway.gmoPg',
}

/**
 * Maps form values to the entity initiate input. The return URL is where the
 * gateway redirects the cardholder after capture; we send the app's invoice list
 * on the current origin.
 */
export function formValuesToInitiateInput(
  values: InitiatePaymentFormValues,
  returnUrl: string,
): InitiatePaymentInput {
  return {
    gateway: values.gateway,
    returnUrl,
  }
}
