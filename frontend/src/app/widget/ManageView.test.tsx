import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { http, HttpResponse } from 'msw'
import { screen, waitFor } from '@testing-library/react'
import { mswServer } from '@tests/msw/server'
import { renderWithProviders } from '@tests/render/render-with-providers'
import { ManageView } from './ManageView'

describe('ManageView (widget Mode B)', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('lists the organization invoices and offers pay only on pending ones', async () => {
    mswServer.use(
      http.get('*/api/v1/widget/received-invoices', () =>
        HttpResponse.json({
          items: [
            {
              id: '01INV1',
              vendor_id: '01V',
              amount: 330000,
              due_date: '2026-06-30',
              status: 'pending',
            },
            {
              id: '01INV2',
              vendor_id: '01V',
              amount: 110000,
              due_date: '2026-04-30',
              status: 'paid',
            },
          ],
          limit: 50,
          offset: 0,
          total: 2,
        }),
      ),
    )

    renderWithProviders(<ManageView />)

    await waitFor(() => {
      expect(screen.getByText('¥330,000')).toBeInTheDocument()
    })
    expect(screen.getByText('¥110,000')).toBeInTheDocument()

    // Only the pending invoice exposes the card-payment action.
    expect(screen.getAllByRole('button', { name: 'Pay by card' })).toHaveLength(1)
  })

  it('shows the empty state when there are no invoices', async () => {
    mswServer.use(
      http.get('*/api/v1/widget/received-invoices', () =>
        HttpResponse.json({ items: [], limit: 50, offset: 0, total: 0 }),
      ),
    )

    renderWithProviders(<ManageView />)

    await waitFor(() => {
      expect(screen.getByText('No invoices to pay.')).toBeInTheDocument()
    })
  })
})
