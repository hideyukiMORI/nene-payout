import type { ReactNode } from 'react'

export interface DetailRow {
  label: string
  value: ReactNode
}

export interface DetailListProps {
  rows: DetailRow[]
}

/**
 * Read-only key/value display for detail screens. Uses a description list so the
 * label/value relationship is conveyed to assistive technology.
 */
export function DetailList({ rows }: DetailListProps) {
  return (
    <dl className="flex flex-col gap-stack-sm">
      {rows.map((row) => (
        <div
          key={row.label}
          className="flex flex-col gap-inline-sm border-b border-border py-stack-sm"
        >
          <dt className="font-sans text-body font-medium text-text-muted">{row.label}</dt>
          <dd className="font-sans text-body text-text-primary">{row.value}</dd>
        </div>
      ))}
    </dl>
  )
}
