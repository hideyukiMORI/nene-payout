<?php

declare(strict_types=1);

namespace NenePayout\Payment;

/** PaymentExecution lifecycle (terms.md §4). */
enum PaymentExecutionStatus: string
{
    case Initiated = 'initiated';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case ChargedBack = 'charged_back';
}
