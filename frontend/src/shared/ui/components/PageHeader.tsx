import type { ReactNode } from 'react'

export interface PageHeaderProps {
  title: string
  actions?: ReactNode
}

export function PageHeader({ title, actions }: PageHeaderProps) {
  return (
    <header className="flex items-center justify-between py-stack-md">
      <h1 className="font-sans text-heading text-text-primary font-medium">{title}</h1>
      {actions}
    </header>
  )
}
