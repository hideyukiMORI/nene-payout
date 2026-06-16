/** Sign-in credentials (domain shape; the view/form maps into this). */
export interface Credentials {
  email: string
  password: string
}

/** User roles (mirrors src/Auth/Role.php). */
export type Role = 'superadmin' | 'admin' | 'operator'

export const ROLES: readonly Role[] = ['superadmin', 'admin', 'operator']

export function isRole(value: string): value is Role {
  return (ROLES as readonly string[]).includes(value)
}

/** Authorization capabilities (mirrors src/Auth/Capability.php). */
export type Capability =
  | 'ManageOrganizations'
  | 'ManageGatewaySettings'
  | 'ManageVendors'
  | 'ManageOrganizationSettings'
  | 'RegisterInvoice'
  | 'InitiatePayment'
  | 'ViewPayments'

/** The authenticated user, as returned by GET /api/v1/auth/me. */
export interface CurrentUser {
  id: string | null
  email: string | null
  /** null when the wire role is unrecognized — treated as no capabilities. */
  role: Role | null
  organizationId: string | null
}

const OPERATOR_CAPABILITIES: readonly Capability[] = [
  'RegisterInvoice',
  'InitiatePayment',
  'ViewPayments',
]

/**
 * Whether a role grants a capability. Exact mirror of Role::hasCapability
 * (src/Auth/Role.php); the API remains the source of truth for authorization,
 * so this only drives UI visibility. A null/unknown role fails closed.
 */
export function roleHasCapability(role: Role | null, capability: Capability): boolean {
  switch (role) {
    case 'superadmin':
      return true
    case 'admin':
      return capability !== 'ManageOrganizations'
    case 'operator':
      return OPERATOR_CAPABILITIES.includes(capability)
    default:
      return false
  }
}
