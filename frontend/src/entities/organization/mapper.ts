import type {
  CreateOrganizationDto,
  OrganizationDto,
  OrganizationListDto,
  UpdateOrganizationDto,
  UpdateOrganizationManagementDto,
} from './api-types'
import type {
  CreateOrganizationInput,
  Organization,
  OrganizationList,
  UpdateOrganizationInput,
  UpdateOrganizationNameInput,
} from './model'

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

export function mapOrganizationListDtoToModel(dto: OrganizationListDto): OrganizationList {
  return {
    items: dto.items.map(mapOrganizationDtoToModel),
    limit: dto.limit,
    offset: dto.offset,
    total: dto.total ?? null,
  }
}

export function mapCreateOrganizationInputToDto(
  input: CreateOrganizationInput,
): CreateOrganizationDto {
  return {
    slug: input.slug,
    name: input.name,
    custom_domain: input.customDomain,
  }
}

export function mapUpdateOrganizationInputToDto(
  input: UpdateOrganizationInput,
): UpdateOrganizationManagementDto {
  return {
    name: input.name,
    custom_domain: input.customDomain,
  }
}
