import { z } from 'zod'
import type { Credentials } from '@/entities/session'
import type { MessageKey } from '@/shared/i18n'

/**
 * Login form schema. Validation is intentionally light (presence + email shape);
 * the backend remains the source of truth for credential correctness. Error
 * messages are i18n keys, resolved by the view.
 */
export const loginFormSchema = z.object({
  email: z
    .string()
    .trim()
    .min(1, 'auth.login.error.emailRequired' satisfies MessageKey)
    .pipe(z.email('auth.login.error.emailInvalid' satisfies MessageKey)),
  password: z.string().min(1, 'auth.login.error.passwordRequired' satisfies MessageKey),
})

export type LoginFormValues = z.infer<typeof loginFormSchema>

export const EMPTY_LOGIN_FORM_VALUES: LoginFormValues = {
  email: '',
  password: '',
}

/** Maps validated form values to the entity credentials shape. */
export function formValuesToCredentials(values: LoginFormValues): Credentials {
  return {
    email: values.email,
    password: values.password,
  }
}
