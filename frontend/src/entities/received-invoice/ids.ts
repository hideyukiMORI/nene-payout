declare const receivedInvoiceIdBrand: unique symbol

export type ReceivedInvoiceId = string & { readonly [receivedInvoiceIdBrand]: never }

export function toReceivedInvoiceId(value: string): ReceivedInvoiceId {
  return value as ReceivedInvoiceId
}
