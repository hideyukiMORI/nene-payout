<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use NenePayout\Auth\Capability;
use NenePayout\Auth\CapabilityResolver;
use PHPUnit\Framework\TestCase;

final class CapabilityResolverTest extends TestCase
{
    public function test_organization_routes_require_manage_organizations(): void
    {
        self::assertSame(Capability::ManageOrganizations, CapabilityResolver::resolve('/api/v1/organizations', 'GET'));
    }

    public function test_vendor_mutations_require_manage_vendors_reads_are_open(): void
    {
        self::assertSame(Capability::ManageVendors, CapabilityResolver::resolve('/api/v1/vendors', 'POST'));
        self::assertNull(CapabilityResolver::resolve('/api/v1/vendors', 'GET'));
    }

    public function test_payment_initiation_requires_initiate_payment(): void
    {
        self::assertSame(
            Capability::InitiatePayment,
            CapabilityResolver::resolve('/api/v1/received-invoices/01INV0000000000000000001/payments', 'POST'),
        );
    }

    public function test_received_invoice_mutations_require_register_invoice(): void
    {
        self::assertSame(Capability::RegisterInvoice, CapabilityResolver::resolve('/api/v1/received-invoices', 'POST'));
    }

    public function test_payment_history_requires_view_payments(): void
    {
        self::assertSame(Capability::ViewPayments, CapabilityResolver::resolve('/api/v1/payment-executions', 'GET'));
    }

    public function test_gateway_settings_require_manage_gateway_settings(): void
    {
        self::assertSame(Capability::ManageGatewaySettings, CapabilityResolver::resolve('/api/v1/gateway-settings', 'GET'));
    }
}
