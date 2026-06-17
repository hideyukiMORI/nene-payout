export interface OrganizationDto {
  id: string
  slug: string
  name: string
  custom_domain: string | null
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface UpdateOrganizationDto {
  name: string
}

export interface OrganizationListDto {
  items: OrganizationDto[]
  limit: number
  offset: number
  total?: number
}

export interface CreateOrganizationDto {
  slug: string
  name: string
  custom_domain: string | null
}

export interface UpdateOrganizationManagementDto {
  name: string
  custom_domain: string | null
}
