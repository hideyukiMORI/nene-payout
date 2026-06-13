import type { ReactNode } from 'react'
import { Navigate } from 'react-router-dom'
import { authToken } from '@/shared/api/auth-token'

/**
 * Fail-closed session gate: without a token, redirect to login. API responses
 * remain the source of truth for authorization (UI gating is UX only).
 */
export function AuthGate({ children }: { children: ReactNode }) {
  if (authToken.get() === null) {
    return <Navigate to="/login" replace />
  }

  return children
}
