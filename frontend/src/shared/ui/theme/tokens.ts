/**
 * Read-only TS accessors for theme tokens (for programmatic use such as charts).
 * Values must mirror `themes/*.css` — never a second source of truth.
 */
export const tokens = {
  color: {
    accent: 'var(--color-accent)',
    danger: 'var(--color-danger)',
    textPrimary: 'var(--color-text-primary)',
    textMuted: 'var(--color-text-muted)',
  },
} as const
