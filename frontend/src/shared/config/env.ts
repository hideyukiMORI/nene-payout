import { z } from 'zod'

const envSchema = z.object({
  VITE_NENE_PAYOUT_API_BASE_URL: z.string().optional(),
})

const parsed = envSchema.parse({
  VITE_NENE_PAYOUT_API_BASE_URL: import.meta.env['VITE_NENE_PAYOUT_API_BASE_URL'] as
    | string
    | undefined,
})

export const env = {
  /** Empty string uses same-origin (the Vite dev proxy forwards `/api` to the API). */
  apiBaseUrl: parsed.VITE_NENE_PAYOUT_API_BASE_URL ?? '',
} as const
