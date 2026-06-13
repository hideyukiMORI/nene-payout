# ADR 0017 — Terminology Single Source of Truth & Zero-Typo Enforcement

Date: 2026-06-13
Status: Accepted

## Context

Identifier drift (typos, wrong case, camelCase vs snake_case, `Nene` vs `NeNe`,
two spellings for one concept) is a recurring source of bugs, broken contracts,
and review churn. Earlier drafts in this very repository referenced framework
objects that did not exist (corrected in ADR 0016), which is exactly the failure
this rule prevents. The product must present one consistent vocabulary to
developers, accountants, and integrators.

## Decision

- **`docs/terms.md` is the single source of truth (唯一の真実)** for every
  identifier and canonical spelling. It is the **only** file allowed to define a
  canonical spelling; all other docs (including `glossary.md`) defer to it.
- **Zero typos, strict enforcement (binding):**
  1. Every identifier in code, API/JSON, DB, tests, OpenAPI, docs, commit scopes,
     and branch names must match a `terms.md` entry **character-for-character**.
  2. Introducing or renaming an identifier must update `terms.md` in the **same
     PR**; unregistered names are defects.
  3. Typos and 表記ゆれ are **merge blockers — no exceptions**. Reviewers must
     reject them; there is no "fix later".
  4. One spelling per concept; renames remove the old spelling everywhere in the
     same PR.
- Enforcement is reviewer-driven now (self-review + PR review). A CI term-lint
  check is a planned follow-up; adopting it does not weaken the rule in the
  meantime.

## Consequences

- Consistent, contract-safe vocabulary across the whole repository.
- Adding/renaming an identifier carries a mandatory `terms.md` update.
- Reviewers have explicit authority and obligation to block on any mismatch.
- A future CI check can mechanize the gate without changing the policy.

## Related

- Single source of truth: `docs/terms.md`
- NENE2 coding conventions binding: `docs/adr/0016-nene2-coding-conventions-binding.md`
- Self-review: `docs/development/self-review.md`
