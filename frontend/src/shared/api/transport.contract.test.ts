import { describe, it } from 'vitest'
import { runTransportContract } from '@hideyukimori/nene2-client/testing'
import {
  buildTransportConfig,
  createApiTransport,
  createProductTokenStore,
} from '@/shared/api/client'

/**
 * L2 transport contract (`@hideyukimori/nene2-client/testing`, fleet #232 / #280).
 *
 * The package's own unit tests never reached this repository — they live outside
 * `files`, so `node_modules/@hideyukimori/nene2-client/` ships zero test files.
 * What could actually be wrong here is *our wiring*, and this suite is the only
 * thing that measures it. It registers as ordinary `describe`/`it`, so it runs
 * under `npm test` → `coverage:check` → `npm run check` rather than a side job
 * nobody watches.
 *
 * `surface: 'adapter'` is the honest answer for this product: features call
 * `apiClient`, a facade over `createApiTransport` which maps `Nene2ClientError`
 * to `AppError`. Declaring `'transport'` here would let the contract build a
 * clean transport of its own, and C1-2 / C3-9 / C4-11 / C4-12 / C5-13 would go
 * green without ever touching our adapter (measured in nene2-js #125).
 *
 * C2-5 is *not* exempted: this product uses the fleet sessionStorage bearer
 * store, so the L1 `localStorage` ban applies unchanged and there is no
 * recognised difference to declare. The widget surface
 * (`app/widget/widget-client.ts`) authenticates with `X-Widget-Token` over raw
 * `fetch` and never touches this transport (ADR 0021) — a separate surface, not
 * an exemption from this one.
 */
runTransportContract({
  product: 'nene-payout',
  surface: 'adapter',
  runner: { describe, it },
  optional: ['C5-13'],
  // C4 measures silent re-authentication. This product has no refresh path to
  // recover *to*: `AuthRouteRegistrar` registers only `auth/login` and
  // `auth/me`, and there is no `refresh` endpoint in the OpenAPI contract, so
  // `recoverAuth` cannot be wired from the frontend alone. Whether to add one
  // is a session-lifetime decision with compliance and audit-resolution
  // implications — tracked as #281, which is also what closes this exemption.
  exemptions: [
    {
      caseId: 'C4-11',
      reason: 'no refresh endpoint exists, so recoverAuth (ADR 0008) is unwired by design',
      ref: 'nene-payout#281',
    },
    {
      caseId: 'C4-12',
      reason: 'no refresh endpoint exists, so recoverAuth (ADR 0008) is unwired by design',
      ref: 'nene-payout#281',
    },
  ],
  createWiring: ({ storage }) => {
    // Built through the product's own factory, and `storage` is forwarded: the
    // contract passes a throwing Storage to force C2-6, and a store that ignored
    // it could not be checked for failing closed.
    const tokenStore = createProductTokenStore(storage)

    return {
      config: buildTransportConfig(tokenStore),
      seedToken: (token) => {
        tokenStore.setToken(token)
      },
      createTransport: (config) => createApiTransport(config),
    }
  },
})
