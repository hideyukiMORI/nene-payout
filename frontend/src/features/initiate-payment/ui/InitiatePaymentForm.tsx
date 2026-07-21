import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import type { GatewayId, InitiatePaymentInput } from '@/entities/payment-execution'
import { Button } from '@/shared/ui/primitives/Button'
import { FormField } from '@/shared/ui/components/FormField'
import { Select } from '@/shared/ui/primitives/Select'
import { Text } from '@/shared/ui/primitives/Text'
import { useTranslation } from '@/shared/i18n'
import {
  formValuesToInitiateInput,
  GATEWAY_LABEL_KEY,
  GATEWAY_VALUES,
  initiatePaymentFormSchema,
  type InitiatePaymentFormValues,
} from '../model/initiate-payment-form'

export interface InitiatePaymentFormProps {
  /** Default selected gateway (e.g. the operator's active gateway). */
  defaultGateway?: GatewayId
  /** Where the gateway should redirect the cardholder after capture. */
  returnUrl: string
  submitting: boolean
  submitError: boolean
  onSubmit: (input: InitiatePaymentInput) => void
  onCancel: () => void
}

export function InitiatePaymentForm({
  defaultGateway = 'stripe',
  returnUrl,
  submitting,
  submitError,
  onSubmit,
  onCancel,
}: InitiatePaymentFormProps) {
  const { t } = useTranslation()
  const { register, handleSubmit } = useForm<InitiatePaymentFormValues>({
    resolver: zodResolver(initiatePaymentFormSchema),
    defaultValues: { gateway: defaultGateway },
  })

  return (
    <form
      noValidate
      onSubmit={(event) => {
        void handleSubmit((values) => {
          onSubmit(formValuesToInitiateInput(values, returnUrl))
        })(event)
      }}
      className="flex flex-col gap-x-stack-md"
    >
      <FormField id="payment-gateway" label={t('admin.payments.field.gateway')}>
        <Select id="payment-gateway" {...register('gateway')}>
          {GATEWAY_VALUES.map((gateway) => (
            <option key={gateway} value={gateway}>
              {t(GATEWAY_LABEL_KEY[gateway])}
            </option>
          ))}
        </Select>
      </FormField>

      {submitError ? (
        <Text tone="muted">
          <span role="alert" className="text-danger">
            {t('admin.payments.pay.failed')}
          </span>
        </Text>
      ) : null}

      <div className="flex gap-x-inline-sm">
        <Button type="submit" disabled={submitting}>
          {submitting ? t('common.actions.saving') : t('admin.payments.initiate')}
        </Button>
        <Button type="button" variant="secondary" onClick={onCancel} disabled={submitting}>
          {t('common.actions.cancel')}
        </Button>
      </div>
    </form>
  )
}
