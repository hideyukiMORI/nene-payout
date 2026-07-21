import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { screen } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { InvoiceDetailView } from './InvoiceDetailView'

describe('InvoiceDetailView', () => {
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

  it('renders the invoice with the resolved vendor name', async () => {
    renderWithRouterAndProviders(
      <InvoiceDetailView receivedInvoiceId="01INV0000000000000000000001" />,
    )

    // Vendor name resolved via the nested useVendor query.
    expect(await screen.findByText('仕入先株式会社')).toBeInTheDocument()
    expect(screen.getByText('¥110,000')).toBeInTheDocument()
    expect(screen.getByText('Pending')).toBeInTheDocument()
  })
})
