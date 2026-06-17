export interface UserDto {
  id: string
  email: string
  role: string
  organization_id: string | null
  status: string
  created_at: string
  updated_at: string
}

export interface UserListDto {
  items: UserDto[]
  limit: number
  offset: number
  total?: number
}

export interface CreateUserDto {
  email: string
  role: string
}

export interface UpdateUserDto {
  role: string
}
