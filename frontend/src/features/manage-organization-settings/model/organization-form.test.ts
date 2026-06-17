import { describe, it, expect } from 'vitest'
import type { Organization } from '@/entities/organization'
import {
  formValuesToUpdateInput,
  organizationFormSchema,
  organizationToFormValues,
  type OrganizationFormValues,
} from './organization-form'

const VALID: OrganizationFormValues = { name: 'Acme 株式会社' }

function firstError(values: OrganizationFormValues): string | undefined {
  const result = organizationFormSchema.safeParse(values)
  return result.success ? undefined : result.error.issues[0]?.message
}

describe('organizationFormSchema', () => {
  it('accepts a valid name', () => {
    expect(organizationFormSchema.safeParse(VALID).success).toBe(true)
  })

  it('rejects an empty name with the i18n key', () => {
    expect(firstError({ name: '   ' })).toBe('admin.organization.form.error.nameRequired')
  })

  it('rejects a name over 255 characters with the i18n key', () => {
    expect(firstError({ name: 'a'.repeat(256) })).toBe('admin.organization.form.error.nameTooLong')
  })
})

describe('organizationToFormValues', () => {
  it('builds form defaults from an organization', () => {
    const organization: Organization = {
      id: '01ORG00000000000000000001',
      slug: 'acme',
      name: 'Acme 株式会社',
      customDomain: null,
      isActive: true,
    }
    expect(organizationToFormValues(organization)).toEqual({ name: 'Acme 株式会社' })
  })
})

describe('formValuesToUpdateInput', () => {
  it('maps form values to the update input', () => {
    expect(formValuesToUpdateInput(VALID)).toEqual({ name: 'Acme 株式会社' })
  })
})
