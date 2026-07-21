import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { screen } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { PaymentDetailView } from './PaymentDetailView'

describe('PaymentDetailView', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })
  beforeEach(() => {
    localStorage.setItem('nene-payout-locale', 'en')
  })

  it('renders the payment fields after loading', async () => {
    renderWithRouterAndProviders(
      <PaymentDetailView paymentExecutionId="01PAY0000000000000000000001" />,
    )

    expect(await screen.findByText('Succeeded')).toBeInTheDocument()
    expect(screen.getByText('stripe')).toBeInTheDocument()
    expect(screen.getByText('pi_123')).toBeInTheDocument()
    // initiated_at 2026-06-14T01:00:05Z → JST 10:00 (date-time shown)
    expect(screen.getAllByText('Jun 14, 2026, 10:00 AM').length).toBeGreaterThan(0)
  })
})
