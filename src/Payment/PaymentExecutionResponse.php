<?php

declare(strict_types=1);

namespace NenePayout\Payment;

final class PaymentExecutionResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(PaymentExecution $payment): array
    {
        return [
            'id'                  => $payment->id,
            'organization_id'     => $payment->organizationId,
            'received_invoice_id' => $payment->receivedInvoiceId,
            'amount'              => $payment->amount,
            'charge_amount'       => $payment->chargeAmount,
            'processing_fee'      => $payment->processingFee,
            'gateway'             => $payment->gateway,
            'gateway_reference'   => $payment->gatewayReference,
            'status'              => $payment->status,
            'initiated_at'        => $payment->initiatedAt,
            'completed_at'        => $payment->completedAt,
        ];
    }
}
