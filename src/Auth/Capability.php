<?php

declare(strict_types=1);

namespace NenePayout\Auth;

/**
 * Authorization capabilities (terms.md §11 / multi-tenancy.md §5).
 */
enum Capability
{
    case ManageOrganizations;
    case ManageGatewaySettings;
    case ManageVendors;
    case ManageOrganizationSettings;
    case RegisterInvoice;
    case InitiatePayment;
    case ViewPayments;
}
