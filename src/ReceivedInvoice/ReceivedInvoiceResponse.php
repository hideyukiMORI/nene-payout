<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

final class ReceivedInvoiceResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(ReceivedInvoice $invoice): array
    {
        return [
            'id'                  => $invoice->id,
            'organization_id'     => $invoice->organizationId,
            'vendor_id'           => $invoice->vendorId,
            'amount'              => $invoice->amount,
            'due_date'            => $invoice->dueDate,
            'status'              => $invoice->status,
            'registration_number' => $invoice->registrationNumber,
            'tax_breakdown'       => $invoice->taxBreakdown,
            'vault_document_url'  => $invoice->vaultDocumentUrl,
            'created_at'          => $invoice->createdAt,
            'updated_at'          => $invoice->updatedAt,
        ];
    }

    /**
     * Detail view including payment history (empty until the payment slice lands).
     *
     * @param list<array<string, mixed>> $paymentExecutions
     * @return array<string, mixed>
     */
    public static function toDetailArray(ReceivedInvoice $invoice, array $paymentExecutions = []): array
    {
        return [...self::toArray($invoice), 'payment_executions' => $paymentExecutions];
    }
}
