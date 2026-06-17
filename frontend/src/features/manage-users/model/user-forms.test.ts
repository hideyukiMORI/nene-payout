import { describe, it, expect } from 'vitest'
import type { User } from '@/entities/user'
import { toUserId } from '@/entities/user'
import {
  editFormValuesToInput,
  inviteFormValuesToInput,
  inviteUserFormSchema,
  userToRoleFormValues,
  type InviteUserFormValues,
} from './user-forms'

const VALID_INVITE: InviteUserFormValues = {
  email: 'new@example.com',
  role: 'operator',
}

function firstInviteError(values: InviteUserFormValues): string | undefined {
  const result = inviteUserFormSchema.safeParse(values)
  return result.success ? undefined : result.error.issues[0]?.message
}

describe('inviteUserFormSchema', () => {
  it('accepts a valid invite', () => {
    expect(inviteUserFormSchema.safeParse(VALID_INVITE).success).toBe(true)
  })

  it('rejects an empty email with the i18n key', () => {
    expect(firstInviteError({ ...VALID_INVITE, email: '' })).toBe(
      'admin.users.form.error.emailRequired',
    )
  })

  it('rejects a malformed email with the i18n key', () => {
    expect(firstInviteError({ ...VALID_INVITE, email: 'not-an-email' })).toBe(
      'admin.users.form.error.emailInvalid',
    )
  })

  it('rejects an unassignable role (superadmin)', () => {
    expect(inviteUserFormSchema.safeParse({ ...VALID_INVITE, role: 'superadmin' }).success).toBe(
      false,
    )
  })
})

describe('inviteFormValuesToInput', () => {
  it('maps form values to the invite input', () => {
    expect(inviteFormValuesToInput(VALID_INVITE)).toEqual({
      email: 'new@example.com',
      role: 'operator',
    })
  })
})

describe('userToRoleFormValues', () => {
  const user: User = {
    id: toUserId('01USER0000000000000000001'),
    organizationId: '01ORG00000000000000000001',
    email: 'admin@example.com',
    role: 'admin',
    status: 'active',
  }

  it('maps an assignable role through unchanged', () => {
    expect(userToRoleFormValues(user).role).toBe('admin')
  })

  it('falls back to operator for a superadmin (managed elsewhere)', () => {
    expect(userToRoleFormValues({ ...user, role: 'superadmin' }).role).toBe('operator')
  })
})

describe('editFormValuesToInput', () => {
  it('maps form values to the update-role input', () => {
    expect(editFormValuesToInput({ role: 'admin' })).toEqual({ role: 'admin' })
  })
})
