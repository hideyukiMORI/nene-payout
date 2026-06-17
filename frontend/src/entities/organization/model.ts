/**
 * The current tenant's organization (singleton; no list/detail). Returned by
 * GET /api/v1/organization. `slug` / `customDomain` drive tenant resolution and
 * are read-only here — only `name` is mutable (see PATCH /api/v1/organization).
 */
export interface Organization {
  id: string
  slug: string
  name: string
  customDomain: string | null
  isActive: boolean
}

export interface UpdateOrganizationNameInput {
  name: string
}

/** Cross-tenant management (superadmin; /api/v1/organizations). */
export interface OrganizationList {
  items: Organization[]
  limit: number
  offset: number
  total: number | null
}

export interface CreateOrganizationInput {
  slug: string
  name: string
  customDomain: string | null
}

export interface UpdateOrganizationInput {
  name: string
  customDomain: string | null
}
