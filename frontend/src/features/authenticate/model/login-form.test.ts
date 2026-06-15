import { describe, expect, it } from 'vitest'
import { formValuesToCredentials, loginFormSchema } from './login-form'

describe('loginFormSchema', () => {
  it('rejects an empty form with required-message keys', () => {
    const result = loginFormSchema.safeParse({ email: '', password: '' })

    expect(result.success).toBe(false)
    if (result.success) {
      throw new Error('expected failure')
    }
    const messages = result.error.issues.map((issue) => issue.message)
    expect(messages).toContain('auth.login.error.emailRequired')
    expect(messages).toContain('auth.login.error.passwordRequired')
  })

  it('rejects a malformed email', () => {
    const result = loginFormSchema.safeParse({ email: 'not-an-email', password: 'secret' })

    expect(result.success).toBe(false)
    if (result.success) {
      throw new Error('expected failure')
    }
    expect(result.error.issues.map((issue) => issue.message)).toContain(
      'auth.login.error.emailInvalid',
    )
  })

  it('accepts and maps valid credentials', () => {
    const result = loginFormSchema.safeParse({ email: ' admin@example.com ', password: 'secret' })

    expect(result.success).toBe(true)
    if (!result.success) {
      throw new Error('expected success')
    }
    expect(formValuesToCredentials(result.data)).toEqual({
      email: 'admin@example.com',
      password: 'secret',
    })
  })
})
