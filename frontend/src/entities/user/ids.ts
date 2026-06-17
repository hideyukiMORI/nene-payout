declare const userIdBrand: unique symbol

export type UserId = string & { readonly [userIdBrand]: never }

export function toUserId(value: string): UserId {
  return value as UserId
}
