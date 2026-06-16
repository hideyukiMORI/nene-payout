/** Wire DTOs for the auth endpoints (mirrors docs/openapi/openapi.yaml). */

export interface LoginRequestDto {
  email: string
  password: string
}

export interface LoginResponseDto {
  token: string
}

export interface CurrentUserDto {
  id: string | null
  email: string | null
  role: string | null
  organization_id: string | null
}
