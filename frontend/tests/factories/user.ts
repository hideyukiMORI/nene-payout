import type { UserDto } from '@/entities/user/api-types'

export function userDto(overrides: Partial<UserDto> = {}): UserDto {
  return {
    id: '01USER0000000000000000001',
    email: 'admin@example.com',
    role: 'admin',
    organization_id: '01ORG00000000000000000001',
    status: 'active',
    created_at: '2026-06-13T00:00:00Z',
    updated_at: '2026-06-13T00:00:00Z',
    ...overrides,
  }
}
