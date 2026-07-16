import { afterAll, afterEach, beforeAll, describe, expect, it } from 'vitest'
import { http, HttpResponse } from 'msw'
import { fireEvent, screen, waitFor } from '@testing-library/react'
import { mswServer } from '../../../../tests/msw/server'
import { renderWithProviders } from '../../../../tests/render/render-with-providers'
import { GenerateWidgetEmbed } from './GenerateWidgetEmbed'

describe('GenerateWidgetEmbed', () => {
  beforeAll(() => {
    mswServer.listen()
  })
  afterEach(() => {
    mswServer.resetHandlers()
  })
  afterAll(() => {
    mswServer.close()
  })

  it('generates and reveals the embed snippet', async () => {
    mswServer.use(
      http.post('*/api/v1/widget-tokens', () =>
        HttpResponse.json(
          {
            token: 'tok',
            expires_at: '2026-09-16T00:00:00Z',
            embed_snippet:
              '<script src="http://x/assets/widget.js" data-payout-token="tok" async></script>',
          },
          { status: 201 },
        ),
      ),
    )

    renderWithProviders(<GenerateWidgetEmbed />)

    fireEvent.click(screen.getByRole('button', { name: 'Generate embed code' }))

    await waitFor(() => {
      expect(screen.getByDisplayValue(/data-payout-token="tok"/)).toBeInTheDocument()
    })
  })
})
