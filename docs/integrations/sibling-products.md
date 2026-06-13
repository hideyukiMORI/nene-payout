# Sibling Product Integration — NeNe Payout

## Dependency direction

```
NeNe Payout → (HTTP read-only) → nene-vault (document link)
NeNe Payout → (HTTP read-only) → nene-invoice (vendor cross-reference)
NeNe Suite  → (orchestrator)   → NeNe Payout
```

NeNe Payout does **not** write to sibling databases.

## nene-vault

Link a received invoice's PDF to a nene-vault document by URL.
Payout stores `vault_document_url` as a reference string. No shared table.

## nene-invoice

Optionally cross-reference a vendor in nene-invoice's client list.
Payout stores `invoice_client_url` as a reference string. No shared table.

## nene-suite

When NeNe Suite orchestrates Payout:
- Suite reads `NENE_PAYOUT_PORT` to build the launcher link
- Payout reads `NENE_SUITE_MODE=1` to adjust behavior (future)
