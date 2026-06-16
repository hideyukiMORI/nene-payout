import { describe, expect, it } from 'vitest'
import { roleHasCapability, type Capability, type Role } from './model'

const ALL_CAPABILITIES: Capability[] = [
  'ManageOrganizations',
  'ManageGatewaySettings',
  'ManageVendors',
  'ManageOrganizationSettings',
  'RegisterInvoice',
  'InitiatePayment',
  'ViewPayments',
]

describe('roleHasCapability', () => {
  it('grants every capability to superadmin', () => {
    for (const capability of ALL_CAPABILITIES) {
      expect(roleHasCapability('superadmin', capability)).toBe(true)
    }
  })

  it('grants admin everything except ManageOrganizations', () => {
    for (const capability of ALL_CAPABILITIES) {
      expect(roleHasCapability('admin', capability)).toBe(capability !== 'ManageOrganizations')
    }
  })

  it('grants operator only invoice/payment capabilities', () => {
    const operatorGranted: Capability[] = ['RegisterInvoice', 'InitiatePayment', 'ViewPayments']
    for (const capability of ALL_CAPABILITIES) {
      expect(roleHasCapability('operator', capability)).toBe(operatorGranted.includes(capability))
    }
  })

  it('fails closed for a null role', () => {
    for (const capability of ALL_CAPABILITIES) {
      expect(roleHasCapability(null, capability)).toBe(false)
    }
  })

  it('matches the backend matrix for nav-gating capabilities', () => {
    const cases: Array<[Role | null, Capability, boolean]> = [
      ['operator', 'ManageVendors', false],
      ['operator', 'ManageOrganizationSettings', false],
      ['admin', 'ManageVendors', true],
      ['admin', 'ManageOrganizationSettings', true],
    ]
    for (const [role, capability, expected] of cases) {
      expect(roleHasCapability(role, capability)).toBe(expected)
    }
  })
})
