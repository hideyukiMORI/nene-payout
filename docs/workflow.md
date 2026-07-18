# Workflow — NeNe Payout

Inherits [NENE2 workflow](https://github.com/hideyukiMORI/NENE2/blob/main/docs/workflow.md).

## Standard Flow

1. Create or reuse a focused GitHub Issue.
2. Confirm context in `docs/roadmap.md` and the private handoff `nene-origin/internal-docs/payout/todo/current.md` (operational logs moved to the private mirror in P3, 2026-07-18).
3. Create a branch from `main`: `type/issue-number-summary`.
4. Implement the smallest useful change.
5. Run verification: `composer check` / `npm run check --prefix frontend`.
6. Commit with Conventional Commits, include Issue number.
7. Push and create a PR with `Closes #n`.
8. Merge after CI checks pass (see [CI](development/ci.md)).
9. Return local `main` to clean state.

The same `composer check` / `npm run check` gates run in CI on every PR; see
[docs/development/ci.md](development/ci.md) for CI, Dependabot, and secret scanning.

## Commit format

```
<type>(<scope>): <日本語の説明> (#<issue>)
```

- `type` / `scope`: English
- description / body: Japanese
- Include `(#issue)` in subject
