# Contributing — NeNe Payout

## Workflow

1. Create or reuse a GitHub Issue.
2. Read `docs/roadmap.md` and the private handoff `nene-origin/internal-docs/payout/todo/current.md` (operational logs moved to the private mirror in P3, 2026-07-18).
3. Branch from `main`: `type/issue-number-summary`.
4. Implement the smallest useful change.
5. Check `docs/terms.md` for all new identifiers.
6. Run `composer check` (and `npm run check --prefix frontend` if frontend changed).
7. Commit with Conventional Commits — see `docs/development/commit-conventions.md`.
8. Open a PR with `Closes #n` and the self-review checklist name.
9. Merge after checks pass. Sync local `main`.

## Code review expectations

- Naming must match `docs/terms.md` exactly — the single source of truth; typos and 表記ゆれ block merge (ADR 0017)
- Money must be integer cents
- Every tenant query must include `organization_id`
- No raw card numbers in logs, storage, or responses
- No competitor commercial product names or comparisons in docs (integrated payment gateway names are allowed)

## Language

Repository docs and code identifiers: **English**.
Commit descriptions, PR bodies, Issues: Japanese or English.
