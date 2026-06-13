import type { ReactNode } from 'react'

export interface TextProps {
  as?: 'p' | 'span'
  tone?: 'primary' | 'muted'
  children: ReactNode
}

export function Text({ as = 'p', tone = 'primary', children }: TextProps) {
  const toneClass = tone === 'muted' ? 'text-muted' : 'text-primary'
  const className = `font-sans text-body ${toneClass}`

  return as === 'span' ? (
    <span className={className}>{children}</span>
  ) : (
    <p className={className}>{children}</p>
  )
}
