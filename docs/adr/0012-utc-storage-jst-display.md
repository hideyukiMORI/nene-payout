# ADR 0012 — Store Timestamps in UTC, Display in JST

Date: 2026-06-13
Status: Accepted

## Context

Payment dates are accounting-relevant: the **payment / settlement date (決済日)**
and `due_date` must reflect the correct **Japan calendar date** regardless of
where the operator's server runs. Relying on ambient `date()` and the host
timezone makes correctness depend on host configuration and makes time-dependent
logic untestable. The sibling product `nene-invoice` fixed the same issue
pre-launch (UTC storage, JST display).

## Decision

1. **Canonical storage is UTC.** Process timezone is forced to UTC at bootstrap;
   every stored instant (`created_at`, `updated_at`, `initiated_at`,
   `completed_at`) is a UTC instant. The JSON API returns UTC strings as-is;
   **UTC is the documented convention** for instant fields.
2. **The authoritative clock is the server, via an injectable `ClockInterface`.**
   Client-supplied time is never trusted for stored instants; tests pin a fixed
   instant.
3. **Display is JST.** User-facing output (admin UI, widget, CSV) converts UTC →
   JST.
4. **Calendar-date fields are derived in JST.** `due_date`, the "today" used by
   list filters / overdue checks, and any month-boundary bucketing are computed
   from the JST wall clock so the Japanese calendar day is correct around the UTC
   midnight boundary. Such fields are stored as JST calendar dates.

## Consequences

- 決済日 / 支払期限 remain correct Japan dates independent of host timezone; the
  rule is explicit in code, not host config.
- Time-dependent use cases are deterministically testable.
- Instant fields in API responses are UTC; clients convert to JST for display.

## Related

- Binding: `docs/explanation/payment-compliance.md` §7
