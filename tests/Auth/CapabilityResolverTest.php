<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use NenePayout\Auth\Capability;
use NenePayout\Auth\CapabilityResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CapabilityResolverTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, Capability|null}>
     */
    public static function resolutionProvider(): iterable
    {
        // Organizations — any method requires ManageOrganizations.
        yield 'orgs GET' => ['/api/v1/organizations', 'GET', Capability::ManageOrganizations];
        yield 'orgs POST' => ['/api/v1/organizations', 'POST', Capability::ManageOrganizations];
        yield 'org by id PATCH' => ['/api/v1/organizations/01O/deactivate', 'POST', Capability::ManageOrganizations];

        // Gateway settings.
        yield 'gateway GET' => ['/api/v1/gateway-settings', 'GET', Capability::ManageGatewaySettings];
        yield 'gateway PUT' => ['/api/v1/gateway-settings', 'PUT', Capability::ManageGatewaySettings];
        yield 'gateway verify POST' => ['/api/v1/gateway-settings/verify', 'POST', Capability::ManageGatewaySettings];

        // Users / audit — org settings (admin).
        yield 'users GET' => ['/api/v1/users', 'GET', Capability::ManageOrganizationSettings];
        yield 'users POST' => ['/api/v1/users', 'POST', Capability::ManageOrganizationSettings];
        yield 'audit GET' => ['/api/v1/audit-logs', 'GET', Capability::ManageOrganizationSettings];

        // Widget embed-code generation requires admin (ManageOrganizationSettings);
        // the token-gated widget runtime under /api/v1/widget/ is not capability-gated.
        yield 'widget-tokens POST' => ['/api/v1/widget-tokens', 'POST', Capability::ManageOrganizationSettings];
        yield 'widget runtime list' => ['/api/v1/widget/received-invoices', 'GET', null];
        yield 'widget runtime create' => ['/api/v1/widget/received-invoices', 'POST', null];
        yield 'widget runtime vendors' => ['/api/v1/widget/vendors', 'POST', null];

        // Vendors — reads open, mutations require ManageVendors.
        yield 'vendors GET' => ['/api/v1/vendors', 'GET', null];
        yield 'vendor GET by id' => ['/api/v1/vendors/01V', 'GET', null];
        yield 'vendors POST' => ['/api/v1/vendors', 'POST', Capability::ManageVendors];
        yield 'vendor PATCH' => ['/api/v1/vendors/01V', 'PATCH', Capability::ManageVendors];
        yield 'vendor deactivate POST' => ['/api/v1/vendors/01V/deactivate', 'POST', Capability::ManageVendors];

        // Received invoices — reads open, mutations require RegisterInvoice.
        yield 'invoices GET' => ['/api/v1/received-invoices', 'GET', null];
        yield 'invoices POST' => ['/api/v1/received-invoices', 'POST', Capability::RegisterInvoice];
        yield 'invoice PATCH' => ['/api/v1/received-invoices/01I', 'PATCH', Capability::RegisterInvoice];
        yield 'invoice void POST' => ['/api/v1/received-invoices/01I/void', 'POST', Capability::RegisterInvoice];
        yield 'invoice pdf POST' => ['/api/v1/received-invoices/01I/pdf', 'POST', Capability::RegisterInvoice];

        // Payment initiation — the /payments special case must win over RegisterInvoice.
        yield 'payment initiate POST' => ['/api/v1/received-invoices/01I/payments', 'POST', Capability::InitiatePayment];

        // Payment executions — read requires ViewPayments.
        yield 'payment executions GET' => ['/api/v1/payment-executions', 'GET', Capability::ViewPayments];
        yield 'payment execution GET by id' => ['/api/v1/payment-executions/01P', 'GET', Capability::ViewPayments];

        // No capability required.
        yield 'health' => ['/health', 'GET', null];
        yield 'auth login' => ['/api/v1/auth/login', 'POST', null];
        yield 'auth me' => ['/api/v1/auth/me', 'GET', null];
        yield 'unknown path' => ['/api/v1/unknown', 'POST', null];
    }

    #[DataProvider('resolutionProvider')]
    public function test_resolves_expected_capability(string $path, string $method, ?Capability $expected): void
    {
        self::assertSame($expected, CapabilityResolver::resolve($path, $method));
    }

    public function test_method_is_case_insensitive(): void
    {
        self::assertSame(Capability::ManageVendors, CapabilityResolver::resolve('/api/v1/vendors', 'post'));
        self::assertNull(CapabilityResolver::resolve('/api/v1/vendors', 'get'));
    }
}
