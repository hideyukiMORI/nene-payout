import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { http, HttpResponse } from 'msw'
import { screen, waitFor } from '@testing-library/react'
import { mswServer } from '../../../tests/msw/server'
import { renderWithProviders } from '../../../tests/render/render-with-providers'
import { PayInvoiceView } from './PayInvoiceView'

describe('PayInvoiceView', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
    window.history.pushState({}, '', '/')
  })
  afterAll(() => {
    mswServer.close()
  })

  it('shows the masked payee account and pay action for a registered pending invoice', async () => {
    window.history.pushState({}, '', '/widget?token=t&mode=pay&invoice=01INV1')
    mswServer.use(
      http.get('*/api/v1/widget/received-invoices/01INV1', () =>
        HttpResponse.json({
          id: '01INV1',
          vendor_id: '01V',
          amount: 330000,
          due_date: '2026-06-30',
          status: 'pending',
        }),
      ),
      http.get('*/api/v1/widget/vendors/01V', () =>
        HttpResponse.json({
          id: '01V',
          name: '山田製作所',
          bank_code: '0001',
          branch_code: '001',
          account_type: '普通',
          account_number: '1234567',
          account_name: 'ヤマダセイサクシヨ',
        }),
      ),
    )

    renderWithProviders(<PayInvoiceView />)

    await waitFor(() => {
      expect(screen.getByText('¥330,000')).toBeInTheDocument()
    })
    // Full payee bank account is shown, masked — never the name alone.
    await waitFor(() => {
      expect(screen.getByText(/0001-001 普通 \*\*\*4567/)).toBeInTheDocument()
    })
    expect(screen.getByRole('button', { name: 'Pay' })).toBeInTheDocument()
  })
})
