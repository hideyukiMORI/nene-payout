import { z } from 'zod'
import {
  ASSIGNABLE_ROLES,
  type AssignableRole,
  type InviteUserInput,
  type UpdateUserRoleInput,
  type User,
} from '@/entities/user'
import type { MessageKey } from '@/shared/i18n'

/**
 * Invite form schema. Mirrors the backend `CreateUserRequest` (email + role);
 * a server 409 (duplicate email) / 422 stays the safety net. Error messages are
 * i18n keys resolved by the view.
 */
export const inviteUserFormSchema = z.object({
  email: z
    .string()
    .trim()
    .min(1, 'admin.users.form.error.emailRequired' satisfies MessageKey)
    .refine(
      (value) => z.email().safeParse(value).success,
      'admin.users.form.error.emailInvalid' satisfies MessageKey,
    ),
  role: z.enum(ASSIGNABLE_ROLES),
})

export type InviteUserFormValues = z.infer<typeof inviteUserFormSchema>

export const EMPTY_INVITE_USER_FORM_VALUES: InviteUserFormValues = {
  email: '',
  role: 'operator',
}

export function inviteFormValuesToInput(values: InviteUserFormValues): InviteUserInput {
  return {
    email: values.email,
    role: values.role,
  }
}

/** Edit form schema. Mirrors the backend `UpdateUserRequest` (role only). */
export const editUserRoleFormSchema = z.object({
  role: z.enum(ASSIGNABLE_ROLES),
})

export type EditUserRoleFormValues = z.infer<typeof editUserRoleFormSchema>

function coerceAssignableRole(role: string): AssignableRole {
  return (ASSIGNABLE_ROLES as readonly string[]).includes(role)
    ? (role as AssignableRole)
    : 'operator'
}

/** Builds edit defaults from an existing user (a superadmin falls back to operator in the picker). */
export function userToRoleFormValues(user: User): EditUserRoleFormValues {
  return {
    role: coerceAssignableRole(user.role),
  }
}

export function editFormValuesToInput(values: EditUserRoleFormValues): UpdateUserRoleInput {
  return {
    role: values.role,
  }
}
