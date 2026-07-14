import { forwardRef, type ReactNode, type SelectHTMLAttributes } from 'react'

export interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  children: ReactNode
}

/**
 * Select primitive. forwardRef so it works directly with RHF `register`.
 * Visual values come from theme tokens only (frontend-standards).
 */
export const Select = forwardRef<HTMLSelectElement, SelectProps>(function Select(
  { children, ...rest },
  ref,
) {
  return (
    <select
      ref={ref}
      className="w-full rounded-x-md border border-border bg-surface-raised px-x-inline-sm py-x-stack-sm font-sans text-body text-primary"
      {...rest}
    >
      {children}
    </select>
  )
})
