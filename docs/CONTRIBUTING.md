# Contributing — NeNe Payout

## Workflow

1. Create or reuse a GitHub Issue.
2. Read `docs/roadmap.md` and `docs/todo/current.md`.
3. Branch from `main`: `type/issue-number-summary`.
4. Implement the smallest useful change.
5. Check `docs/terms.md` for all new identifiers.
6. Run `composer check` (and `npm run check --prefix frontend` if frontend changed).
7. Commit with Conventional Commits — see `docs/development/commit-conventions.md`.
8. Open a PR with `Closes #n` and the self-review checklist name.
9. Merge after checks pass. Sync local `main`.

## Code review expectations

- Naming must match `docs/terms.md` exactly
- Money must be integer cents
- Every tenant query must include `organization_id`
- No raw card numbers in logs, storage, or responses
- No third-party commercial product names in docs

## Language

Repository docs and code identifiers: **English**.
Commit descriptions, PR bodies, Issues: Japanese or English.
