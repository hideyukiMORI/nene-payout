<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

/** ReceivedInvoice lifecycle (terms.md §4). */
enum ReceivedInvoiceStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Failed = 'failed';
    case Voided = 'voided';
}
