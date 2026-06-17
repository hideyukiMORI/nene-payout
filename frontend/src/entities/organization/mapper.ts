import type { OrganizationDto, UpdateOrganizationDto } from './api-types'
import type { Organization, UpdateOrganizationNameInput } from './model'

export function mapOrganizationDtoToModel(dto: OrganizationDto): Organization {
  return {
    id: dto.id,
    slug: dto.slug,
    name: dto.name,
    customDomain: dto.custom_domain,
    isActive: dto.is_active,
  }
}

export function mapUpdateOrganizationNameInputToDto(
  input: UpdateOrganizationNameInput,
): UpdateOrganizationDto {
  return {
    name: input.name,
  }
}
