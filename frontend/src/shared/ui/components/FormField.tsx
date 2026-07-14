import type { ReactNode } from 'react'

export interface FormFieldProps {
  /** id of the control this field labels; must match the control's `id`. */
  id: string
  label: string
  /** Localized error message, or null when valid. */
  error?: string | null
  children: ReactNode
}

/**
 * Labelled form field wrapper: associates a <label> with its control and renders
 * a validation error with an aria-describedby link for assistive tech.
 */
export function FormField({ id, label, error = null, children }: FormFieldProps) {
  const errorId = `${id}-error`

  return (
    <div className="flex flex-col gap-x-inline-sm">
      <label htmlFor={id} className="font-sans text-body font-medium text-text-primary">
        {label}
      </label>
      {children}
      {error !== null ? (
        <p id={errorId} role="alert" className="font-sans text-body text-danger">
          {error}
        </p>
      ) : null}
    </div>
  )
}
