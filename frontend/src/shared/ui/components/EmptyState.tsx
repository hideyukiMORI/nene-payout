export interface EmptyStateProps {
  message: string
}

export function EmptyState({ message }: EmptyStateProps) {
  return (
    <div className="py-x-stack-lg font-sans text-body text-muted" role="status">
      {message}
    </div>
  )
}
