import { afterAll, afterEach, beforeAll, beforeEach, describe, expect, it } from 'vitest'
import { screen } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { renderWithRouterAndProviders } from '@tests/render/render-with-providers'
import { VendorDetailView } from './VendorDetailView'

describe('VendorDetailView', () => {
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

  it('renders the vendor fields after loading', async () => {
    renderWithRouterAndProviders(<VendorDetailView vendorId="01VENDOR000000000000000001" />)

    expect(await screen.findByText('仕入先株式会社')).toBeInTheDocument()
    expect(screen.getByText('シイレサキ')).toBeInTheDocument()
    expect(screen.getByRole('link', { name: 'Edit' })).toBeInTheDocument()
  })
})
