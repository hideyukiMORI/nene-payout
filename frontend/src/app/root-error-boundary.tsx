import { Component, type ErrorInfo, type ReactNode } from 'react'

interface RootErrorBoundaryState {
  hasError: boolean
}

/**
 * Top-level crash boundary. React provides no hook equivalent, so this is the
 * one sanctioned class component (frontend-standards). The fallback is a minimal
 * last-resort screen rendered when the app (and possibly i18n) has failed.
 */
export class RootErrorBoundary extends Component<{ children: ReactNode }, RootErrorBoundaryState> {
  override state: RootErrorBoundaryState = { hasError: false }

  static getDerivedStateFromError(): RootErrorBoundaryState {
    return { hasError: true }
  }

  override componentDidCatch(error: Error, info: ErrorInfo): void {
    if (import.meta.env.DEV) {
      console.error('Unhandled application error', error, info)
    }
  }

  override render(): ReactNode {
    if (this.state.hasError) {
      return (
        <div role="alert" className="px-x-inline-md py-x-stack-lg font-sans text-body text-danger">
          Something went wrong. Please reload the page.
        </div>
      )
    }

    return this.props.children
  }
}
