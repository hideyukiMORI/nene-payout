import { forwardRef, type InputHTMLAttributes } from 'react'

export type InputProps = InputHTMLAttributes<HTMLInputElement>

/**
 * Text input primitive. forwardRef so it works directly with RHF `register`.
 * Visual values come from theme tokens only (frontend-standards).
 */
export const Input = forwardRef<HTMLInputElement, InputProps>(function Input(props, ref) {
  return (
    <input
      ref={ref}
      className="w-full rounded-x-md border border-border bg-surface-raised px-x-inline-sm py-x-stack-sm font-sans text-text-primary"
      {...props}
    />
  )
})
