# Glossary — NeNe Payout

For canonical identifier spellings, see [`docs/terms.md`](../terms.md).
This file explains the meaning of domain concepts.

## Domain terms

| Term | Reading | Meaning |
| --- | --- | --- |
| Received invoice | 受取請求書 | An invoice sent by a vendor that the operator owes payment on |
| Vendor | 仕入先・外注先 | The company or individual who sent the invoice and will receive payment |
| Payment execution | 決済実行 | A single attempt to charge the operator's card and transfer to the vendor |
| Payment gateway | 決済代行会社 | A third-party service that processes the card charge and executes the bank transfer |
| Adapter | アダプター | Code that bridges NeNe Payout and a specific payment gateway API |
| Embeddable widget | 埋め込みウィジェット | A JavaScript snippet that renders the payment form inside an external system |
| Tokenization | トークナイゼーション | Replacing a raw card number with a gateway-issued token; raw numbers never reach Payout's server |
| Organization | 組織（テナント） | A tenant in the multi-tenant setup; all data is scoped by `organization_id` |
| Integer cents | 整数セント | All monetary amounts stored as integers (e.g. ¥1,000 = `100000`); no floats |
| Connectivity check | 疎通確認 | A test API call to the payment gateway to verify credentials and network connectivity |

## Status values

See [`docs/terms.md §4`](../terms.md) for the authoritative list.

## Relationship to NeNe Payout

| Concept | Owner | Notes |
| --- | --- | --- |
| Issuing invoices | nene-invoice | NeNe Payout does not issue invoices |
| Reconciling deposits | nene-clear | NeNe Payout does not match deposits |
| Archiving documents | nene-vault | NeNe Payout stores a reference URL only |
