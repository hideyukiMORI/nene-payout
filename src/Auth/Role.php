<?php

declare(strict_types=1);

namespace NenePayout\Auth;

/**
 * User roles (terms.md §11). superadmin is cross-tenant; admin is full access
 * within its organization; operator creates invoices and initiates payments.
 */
enum Role: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Operator = 'operator';

    public function hasCapability(Capability $capability): bool
    {
        return match ($this) {
            self::Superadmin => true,
            self::Admin => $capability !== Capability::ManageOrganizations,
            self::Operator => match ($capability) {
                Capability::RegisterInvoice,
                Capability::InitiatePayment,
                Capability::ViewPayments => true,
                default => false,
            },
        };
    }
}
