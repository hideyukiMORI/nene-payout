import { Button } from '@/shared/ui/primitives/Button'

export interface ErrorStateProps {
  message: string
  retryLabel: string
  onRetry: () => void
}

export function ErrorState({ message, retryLabel, onRetry }: ErrorStateProps) {
  return (
    <div className="py-stack-lg" role="alert">
      <p className="font-sans text-body text-danger">{message}</p>
      <div className="py-stack-sm">
        <Button variant="secondary" onClick={onRetry}>
          {retryLabel}
        </Button>
      </div>
    </div>
  )
}
