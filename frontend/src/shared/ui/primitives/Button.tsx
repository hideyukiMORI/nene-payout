import type { ButtonHTMLAttributes, ReactNode } from 'react'

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'danger'
  children: ReactNode
}

const VARIANT_CLASS: Record<NonNullable<ButtonProps['variant']>, string> = {
  primary: 'bg-accent text-accent-contrast',
  secondary: 'bg-surface-raised text-primary border border-border',
  danger: 'bg-danger text-accent-contrast',
}

export function Button({ variant = 'primary', children, type = 'button', ...rest }: ButtonProps) {
  return (
    <button
      type={type}
      className={`rounded-md px-inline-md py-stack-sm font-sans text-body font-medium ${VARIANT_CLASS[variant]}`}
      {...rest}
    >
      {children}
    </button>
  )
}
