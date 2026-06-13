# Workflow — NeNe Payout

Inherits [NENE2 workflow](https://github.com/hideyukiMORI/NENE2/blob/main/docs/workflow.md).

## Standard Flow

1. Create or reuse a focused GitHub Issue.
2. Confirm context in `docs/roadmap.md` and `docs/todo/current.md`.
3. Create a branch from `main`: `type/issue-number-summary`.
4. Implement the smallest useful change.
5. Run verification: `composer check` / `npm run check --prefix frontend`.
6. Commit with Conventional Commits, include Issue number.
7. Push and create a PR with `Closes #n`.
8. Merge after checks pass.
9. Return local `main` to clean state.

## Commit format

```
<type>(<scope>): <日本語の説明> (#<issue>)
```

- `type` / `scope`: English
- description / body: Japanese
- Include `(#issue)` in subject
