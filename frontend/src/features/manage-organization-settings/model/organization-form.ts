import { z } from 'zod'
import type { Organization, UpdateOrganizationNameInput } from '@/entities/organization'
import type { MessageKey } from '@/shared/i18n'

/**
 * Organization settings form schema. Mirrors the backend `OrganizationInputMapper`
 * (name required, non-empty); a server 422 stays the safety net. Error messages
 * are i18n keys resolved by the view.
 */
export const organizationFormSchema = z.object({
  name: z
    .string()
    .trim()
    .min(1, 'admin.organization.form.error.nameRequired' satisfies MessageKey)
    .max(255, 'admin.organization.form.error.nameTooLong' satisfies MessageKey),
})

export type OrganizationFormValues = z.infer<typeof organizationFormSchema>

export function organizationToFormValues(organization: Organization): OrganizationFormValues {
  return {
    name: organization.name,
  }
}

export function formValuesToUpdateInput(
  values: OrganizationFormValues,
): UpdateOrganizationNameInput {
  return {
    name: values.name,
  }
}
