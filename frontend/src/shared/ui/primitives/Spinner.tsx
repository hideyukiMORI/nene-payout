export interface SpinnerProps {
  label: string
}

export function Spinner({ label }: SpinnerProps) {
  return (
    <output className="font-sans text-body text-muted" aria-live="polite">
      {label}
    </output>
  )
}
