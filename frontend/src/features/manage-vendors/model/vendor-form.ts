import { z } from 'zod'
import {
  ACCOUNT_TYPES,
  type AccountType,
  type CreateVendorInput,
  type Vendor,
} from '@/entities/vendor'
import type { MessageKey } from '@/shared/i18n'

const REGISTRATION_NUMBER_PATTERN = /^T[0-9]{13}$/

/**
 * Vendor form schema. Rules mirror the backend `VendorInputMapper` exactly so the
 * client and server agree (a server 422 stays the safety net, not the first line).
 * Error messages are i18n keys (resolved by the view), keeping all UI text in the
 * message catalogs.
 */
export const vendorFormSchema = z.object({
  name: z
    .string()
    .trim()
    .min(1, 'admin.vendors.form.error.nameRequired' satisfies MessageKey),
  bankCode: z
    .string()
    .regex(/^[0-9]{4}$/, 'admin.vendors.form.error.bankCode' satisfies MessageKey),
  branchCode: z
    .string()
    .regex(/^[0-9]{3}$/, 'admin.vendors.form.error.branchCode' satisfies MessageKey),
  accountType: z.enum(ACCOUNT_TYPES),
  accountNumber: z
    .string()
    .regex(/^[0-9]{1,7}$/, 'admin.vendors.form.error.accountNumber' satisfies MessageKey),
  accountName: z
    .string()
    .trim()
    .min(1, 'admin.vendors.form.error.accountNameRequired' satisfies MessageKey),
  registrationNumber: z.union([
    z.literal(''),
    z
      .string()
      .regex(
        REGISTRATION_NUMBER_PATTERN,
        'admin.vendors.form.error.registrationNumber' satisfies MessageKey,
      ),
  ]),
})

export type VendorFormValues = z.infer<typeof vendorFormSchema>

export const EMPTY_VENDOR_FORM_VALUES: VendorFormValues = {
  name: '',
  bankCode: '',
  branchCode: '',
  accountType: '普通',
  accountNumber: '',
  accountName: '',
  registrationNumber: '',
}

function coerceAccountType(value: string): AccountType {
  return (ACCOUNT_TYPES as readonly string[]).includes(value) ? (value as AccountType) : '普通'
}

/** Builds form defaults from an existing vendor (edit mode). */
export function vendorToFormValues(vendor: Vendor): VendorFormValues {
  return {
    name: vendor.name,
    bankCode: vendor.bankCode,
    branchCode: vendor.branchCode,
    accountType: coerceAccountType(vendor.accountType),
    accountNumber: vendor.accountNumber,
    accountName: vendor.accountName,
    registrationNumber: vendor.registrationNumber ?? '',
  }
}

/** Maps validated form values to the entity create/update input. */
export function formValuesToCreateInput(values: VendorFormValues): CreateVendorInput {
  return {
    name: values.name,
    bankCode: values.bankCode,
    branchCode: values.branchCode,
    accountType: values.accountType,
    accountNumber: values.accountNumber,
    accountName: values.accountName,
    registrationNumber: values.registrationNumber === '' ? null : values.registrationNumber,
  }
}
