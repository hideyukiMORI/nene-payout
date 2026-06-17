import { describe, it, expect } from 'vitest'
import type { Organization } from '@/entities/organization'
import {
  createFormValuesToInput,
  createOrganizationFormSchema,
  editFormValuesToInput,
  editOrganizationFormSchema,
  organizationToEditFormValues,
  type CreateOrganizationFormValues,
} from './organization-management-forms'

const VALID_CREATE: CreateOrganizationFormValues = {
  slug: 'acme-co',
  name: 'Acme 株式会社',
  customDomain: 'pay.acme.example',
}

function firstCreateError(values: CreateOrganizationFormValues): string | undefined {
  const result = createOrganizationFormSchema.safeParse(values)
  return result.success ? undefined : result.error.issues[0]?.message
}

describe('createOrganizationFormSchema', () => {
  it('accepts a fully valid form', () => {
    expect(createOrganizationFormSchema.safeParse(VALID_CREATE).success).toBe(true)
  })

  it('accepts an empty custom domain (optional)', () => {
    expect(
      createOrganizationFormSchema.safeParse({ ...VALID_CREATE, customDomain: '' }).success,
    ).toBe(true)
  })

  it('rejects an empty slug with the i18n key', () => {
    expect(firstCreateError({ ...VALID_CREATE, slug: '' })).toBe(
      'admin.organizations.form.error.slugRequired',
    )
  })

  it('rejects an uppercase slug with the i18n key', () => {
    expect(firstCreateError({ ...VALID_CREATE, slug: 'Acme' })).toBe(
      'admin.organizations.form.error.slug',
    )
  })

  it('rejects a slug with invalid characters', () => {
    expect(firstCreateError({ ...VALID_CREATE, slug: 'a_c_me' })).toBe(
      'admin.organizations.form.error.slug',
    )
  })

  it('rejects an empty name', () => {
    expect(firstCreateError({ ...VALID_CREATE, name: '' })).toBe(
      'admin.organizations.form.error.nameRequired',
    )
  })

  it('rejects a malformed custom domain', () => {
    expect(firstCreateError({ ...VALID_CREATE, customDomain: 'not a domain' })).toBe(
      'admin.organizations.form.error.customDomain',
    )
  })
})

describe('createFormValuesToInput', () => {
  it('maps an empty custom domain to null', () => {
    expect(createFormValuesToInput({ ...VALID_CREATE, customDomain: '' }).customDomain).toBeNull()
  })

  it('keeps a present custom domain', () => {
    expect(createFormValuesToInput(VALID_CREATE).customDomain).toBe('pay.acme.example')
  })
})

describe('editOrganizationFormSchema', () => {
  it('accepts a valid edit', () => {
    expect(
      editOrganizationFormSchema.safeParse({ name: 'Renamed', customDomain: '' }).success,
    ).toBe(true)
  })

  it('rejects an empty name', () => {
    const result = editOrganizationFormSchema.safeParse({ name: '', customDomain: '' })
    expect(result.success).toBe(false)
  })
})

describe('organizationToEditFormValues', () => {
  const organization: Organization = {
    id: '01ORG00000000000000000001',
    slug: 'acme',
    name: 'Acme',
    customDomain: null,
    isActive: true,
  }

  it('maps an organization to edit defaults and a null domain to empty string', () => {
    expect(organizationToEditFormValues(organization)).toEqual({ name: 'Acme', customDomain: '' })
  })
})

describe('editFormValuesToInput', () => {
  it('maps an empty custom domain to null', () => {
    expect(editFormValuesToInput({ name: 'Acme', customDomain: '' }).customDomain).toBeNull()
  })
})
