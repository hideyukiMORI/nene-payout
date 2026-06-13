import { describe, it, expect } from 'vitest'
import type { Vendor } from '@/entities/vendor'
import { toVendorId } from '@/entities/vendor'
import {
  formValuesToCreateInput,
  vendorFormSchema,
  vendorToFormValues,
  type VendorFormValues,
} from './vendor-form'

const VALID: VendorFormValues = {
  name: '仕入先株式会社',
  bankCode: '0001',
  branchCode: '001',
  accountType: '普通',
  accountNumber: '1234567',
  accountName: 'シイレサキ',
  registrationNumber: 'T1234567890123',
}

function firstError(values: VendorFormValues): string | undefined {
  const result = vendorFormSchema.safeParse(values)
  return result.success ? undefined : result.error.issues[0]?.message
}

describe('vendorFormSchema', () => {
  it('accepts a fully valid form', () => {
    expect(vendorFormSchema.safeParse(VALID).success).toBe(true)
  })

  it('accepts an empty registration number (optional)', () => {
    expect(vendorFormSchema.safeParse({ ...VALID, registrationNumber: '' }).success).toBe(true)
  })

  it('rejects an empty name with the i18n key', () => {
    expect(firstError({ ...VALID, name: '' })).toBe('admin.vendors.form.error.nameRequired')
  })

  it('rejects a bank code that is not 4 digits', () => {
    expect(firstError({ ...VALID, bankCode: '123' })).toBe('admin.vendors.form.error.bankCode')
    expect(firstError({ ...VALID, bankCode: '12345' })).toBe('admin.vendors.form.error.bankCode')
  })

  it('rejects a branch code that is not 3 digits', () => {
    expect(firstError({ ...VALID, branchCode: '12' })).toBe('admin.vendors.form.error.branchCode')
  })

  it('rejects an account number longer than 7 digits', () => {
    expect(firstError({ ...VALID, accountNumber: '12345678' })).toBe(
      'admin.vendors.form.error.accountNumber',
    )
  })

  it('rejects a non-numeric account number', () => {
    expect(firstError({ ...VALID, accountNumber: '12a4567' })).toBe(
      'admin.vendors.form.error.accountNumber',
    )
  })

  it('rejects a malformed registration number', () => {
    expect(firstError({ ...VALID, registrationNumber: 'T123' })).toBe(
      'admin.vendors.form.error.registrationNumber',
    )
  })

  it('rejects an unknown account type', () => {
    expect(vendorFormSchema.safeParse({ ...VALID, accountType: 'foo' }).success).toBe(false)
  })
})

describe('formValuesToCreateInput', () => {
  it('maps an empty registration number to null', () => {
    const input = formValuesToCreateInput({ ...VALID, registrationNumber: '' })
    expect(input.registrationNumber).toBeNull()
  })

  it('keeps a present registration number', () => {
    const input = formValuesToCreateInput(VALID)
    expect(input.registrationNumber).toBe('T1234567890123')
    expect(input.accountType).toBe('普通')
  })
})

describe('vendorToFormValues', () => {
  const vendor: Vendor = {
    id: toVendorId('01VENDOR000000000000000001'),
    organizationId: '01ORG00000000000000000001',
    name: 'Acme',
    bankCode: '0001',
    branchCode: '001',
    accountType: '当座',
    accountNumber: '7654321',
    accountName: 'アクメ',
    registrationNumber: null,
  }

  it('maps a vendor to form values and a null registration number to empty string', () => {
    const values = vendorToFormValues(vendor)
    expect(values.accountType).toBe('当座')
    expect(values.registrationNumber).toBe('')
  })

  it('falls back to 普通 for an unexpected account type', () => {
    const values = vendorToFormValues({ ...vendor, accountType: 'unknown' })
    expect(values.accountType).toBe('普通')
  })
})
