import { expect, test, type Route } from '@playwright/test'

// Minimal @smoke — fleet T2 pilot (Issue #242).
//
// Critical path: boot the SPA → sign in → land on the authenticated dashboard
// shell. The API is stubbed (page.route) so the run is hermetic — no backend,
// DB, or Docker. This asserts the *frontend wiring* of the login flow
// (form → token store → fail-closed AuthGate → dashboard render), not the real
// backend auth, which is covered by backend + unit tests.
//
// Contracts mirrored from docs/openapi/openapi.yaml:
//   POST /api/v1/auth/login  -> { token }
//   GET  /api/v1/auth/me     -> { id, email, role, organization_id }
//   GET  /api/v1/{vendors,received-invoices,payment-executions} -> { items, total }

const json = (route: Route, body: unknown): Promise<void> =>
  route.fulfill({
    status: 200,
    contentType: 'application/json',
    body: JSON.stringify(body),
  })

const emptyList = { items: [], total: 0 }

test('@smoke sign in and reach the dashboard shell', async ({ page }) => {
  await page.route('**/api/v1/**', async (route) => {
    const url = route.request().url()
    const method = route.request().method()

    if (url.includes('/api/v1/auth/login') && method === 'POST') {
      await json(route, { token: 'smoke-token' })
      return
    }
    if (url.includes('/api/v1/auth/me')) {
      await json(route, {
        id: 'usr_smoke',
        email: 'smoke@example.com',
        role: 'admin',
        organization_id: 'org_smoke',
      })
      return
    }
    // Dashboard summary counts (received-invoices / vendors / payment-executions).
    await json(route, emptyList)
  })

  // Boot → login page.
  await page.goto('/login')
  await expect(page.locator('#login-email')).toBeVisible()

  // Sign in.
  await page.locator('#login-email').fill('smoke@example.com')
  await page.locator('#login-password').fill('correct horse battery staple')
  await page.locator('button[type="submit"]').click()

  // Land on the authenticated dashboard shell.
  await expect(page).toHaveURL(/\/dashboard$/)
  await expect(page.getByRole('navigation')).toBeVisible()
  // A main nav destination is wired (proves the authed shell, not just a token).
  await expect(page.locator('a[href="/vendors"]').first()).toBeVisible()
})
