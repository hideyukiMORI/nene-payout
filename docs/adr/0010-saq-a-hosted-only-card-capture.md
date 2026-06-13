# ADR 0010 — Hosted-Only Card Capture (PCI DSS SAQ-A)

Date: 2026-06-13
Status: Accepted

## Context

Operators are Japanese SMBs running their own deployment (Tier A shared hosting /
Tier B Docker — ADR 0003). We must not push PCI DSS card-data scope onto these
operators. Any design where a card PAN touches the application, its database, or
the operator's server is rejected. The sibling product `nene-invoice` made the
same SAQ-A choice on the receiver side.

## Decision

- The card PAN **MUST NOT** pass through the application, its database, or the
  operator's server. Only **gateway-hosted redirect or processor-hosted iframe**
  capture (tokenization) is permitted.
- Payout stores only opaque references (gateway session id, payment intent /
  token, `gateway_reference`) and webhook payloads — never PAN, never CVV.
- A self-host operator who enables card payment **MUST** remain at **PCI DSS
  SAQ-A**. Any future adapter that would raise that scope requires a new ADR.
- Card tokens, gateway API keys, and webhook secrets are never logged; sensitive
  tokens are hashed (SHA-256) before any storage that requires them.

## Consequences

- No PCI DSS burden imposed on operators by default.
- The widget and admin payment flow are constrained to hosted gateway UIs.
- Gateway adapters that only offer raw-PAN APIs cannot be used.

## Related

- Binding: `docs/explanation/payment-compliance.md` §3
- Deployment tiers: `docs/adr/0003-dual-tier-deployment.md`
