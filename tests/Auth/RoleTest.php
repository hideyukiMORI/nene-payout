<?php

declare(strict_types=1);

namespace NenePayout\Tests\Auth;

use NenePayout\Auth\Capability;
use NenePayout\Auth\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function test_superadmin_has_every_capability(): void
    {
        foreach (Capability::cases() as $capability) {
            self::assertTrue(Role::Superadmin->hasCapability($capability), $capability->name);
        }
    }

    public function test_admin_has_all_except_manage_organizations(): void
    {
        self::assertFalse(Role::Admin->hasCapability(Capability::ManageOrganizations));
        self::assertTrue(Role::Admin->hasCapability(Capability::ManageGatewaySettings));
        self::assertTrue(Role::Admin->hasCapability(Capability::ManageVendors));
        self::assertTrue(Role::Admin->hasCapability(Capability::ManageOrganizationSettings));
        self::assertTrue(Role::Admin->hasCapability(Capability::RegisterInvoice));
        self::assertTrue(Role::Admin->hasCapability(Capability::InitiatePayment));
        self::assertTrue(Role::Admin->hasCapability(Capability::ViewPayments));
    }

    public function test_operator_has_only_invoice_and_payment_capabilities(): void
    {
        self::assertTrue(Role::Operator->hasCapability(Capability::RegisterInvoice));
        self::assertTrue(Role::Operator->hasCapability(Capability::InitiatePayment));
        self::assertTrue(Role::Operator->hasCapability(Capability::ViewPayments));

        self::assertFalse(Role::Operator->hasCapability(Capability::ManageOrganizations));
        self::assertFalse(Role::Operator->hasCapability(Capability::ManageGatewaySettings));
        self::assertFalse(Role::Operator->hasCapability(Capability::ManageVendors));
        self::assertFalse(Role::Operator->hasCapability(Capability::ManageOrganizationSettings));
    }
}
