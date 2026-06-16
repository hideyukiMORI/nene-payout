import type { ReactNode } from 'react'
import { Navigate } from 'react-router-dom'
import { roleHasCapability, useCurrentUser, type Capability } from '@/entities/session'

/**
 * Route guard that redirects to /forbidden when the current user's role lacks
 * the required capability. UX only — the API stays the source of truth, so an
 * unresolved/loading session renders nothing rather than leaking content.
 */
export function RequireCapability({
  capability,
  children,
}: {
  capability: Capability
  children: ReactNode
}) {
  const { data, isPending } = useCurrentUser()

  if (isPending) {
    return null
  }

  if (!roleHasCapability(data?.role ?? null, capability)) {
    return <Navigate to="/forbidden" replace />
  }

  return children
}
