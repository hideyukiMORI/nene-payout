import { z } from 'zod'
import type {
  CreateOrganizationInput,
  Organization,
  UpdateOrganizationInput,
} from '@/entities/organization'
import type { MessageKey } from '@/shared/i18n'

const SLUG_PATTERN = /^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/
const DOMAIN_PATTERN = /^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i

const nameField = z
  .string()
  .trim()
  .min(1, 'admin.organizations.form.error.nameRequired' satisfies MessageKey)
  .max(255, 'admin.organizations.form.error.nameTooLong' satisfies MessageKey)

const customDomainField = z.union([
  z.literal(''),
  z
    .string()
    .trim()
    .max(255, 'admin.organizations.form.error.customDomain' satisfies MessageKey)
    .regex(DOMAIN_PATTERN, 'admin.organizations.form.error.customDomain' satisfies MessageKey),
])

/**
 * Create form schema. Rules mirror the backend `OrganizationManagementInputMapper`
 * so client and server agree (a server 422/409 stays the safety net).
 */
export const createOrganizationFormSchema = z.object({
  slug: z
    .string()
    .trim()
    .min(1, 'admin.organizations.form.error.slugRequired' satisfies MessageKey)
    .max(100, 'admin.organizations.form.error.slug' satisfies MessageKey)
    .regex(SLUG_PATTERN, 'admin.organizations.form.error.slug' satisfies MessageKey),
  name: nameField,
  customDomain: customDomainField,
})

export type CreateOrganizationFormValues = z.infer<typeof createOrganizationFormSchema>

export const EMPTY_CREATE_ORGANIZATION_FORM_VALUES: CreateOrganizationFormValues = {
  slug: '',
  name: '',
  customDomain: '',
}

export function createFormValuesToInput(
  values: CreateOrganizationFormValues,
): CreateOrganizationInput {
  return {
    slug: values.slug,
    name: values.name,
    customDomain: values.customDomain === '' ? null : values.customDomain,
  }
}

/** Edit form schema. Slug is immutable (drives tenant resolution) — not editable. */
export const editOrganizationFormSchema = z.object({
  name: nameField,
  customDomain: customDomainField,
})

export type EditOrganizationFormValues = z.infer<typeof editOrganizationFormSchema>

export function organizationToEditFormValues(
  organization: Organization,
): EditOrganizationFormValues {
  return {
    name: organization.name,
    customDomain: organization.customDomain ?? '',
  }
}

export function editFormValuesToInput(values: EditOrganizationFormValues): UpdateOrganizationInput {
  return {
    name: values.name,
    customDomain: values.customDomain === '' ? null : values.customDomain,
  }
}
