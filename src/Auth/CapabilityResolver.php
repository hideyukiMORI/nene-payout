<?php

declare(strict_types=1);

namespace NenePayout\Auth;

/**
 * Maps an API path + method to the Capability required to perform it.
 * Returns null when no capability is required (any authenticated user).
 */
final class CapabilityResolver
{
    public static function resolve(string $path, string $method): ?Capability
    {
        $method = strtoupper($method);

        if (str_starts_with($path, '/api/v1/organizations')) {
            return Capability::ManageOrganizations;
        }

        // Singular self-service settings for the current tenant (admin, own org).
        // Must follow the plural check above so it never shadows /organizations.
        if (str_starts_with($path, '/api/v1/organization')) {
            return Capability::ManageOrganizationSettings;
        }

        if (str_starts_with($path, '/api/v1/gateway-settings')) {
            return Capability::ManageGatewaySettings;
        }

        if (str_starts_with($path, '/api/v1/users')) {
            return Capability::ManageOrganizationSettings;
        }

        if (str_starts_with($path, '/api/v1/audit-logs')) {
            return Capability::ManageOrganizationSettings;
        }

        if (str_starts_with($path, '/api/v1/vendors') && self::isMutation($method)) {
            return Capability::ManageVendors;
        }

        // Payment initiation: POST /api/v1/received-invoices/{id}/payments
        if (str_starts_with($path, '/api/v1/received-invoices') && str_ends_with($path, '/payments') && $method === 'POST') {
            return Capability::InitiatePayment;
        }

        if (str_starts_with($path, '/api/v1/received-invoices') && self::isMutation($method)) {
            return Capability::RegisterInvoice;
        }

        if (str_starts_with($path, '/api/v1/payment-executions') && $method === 'GET') {
            return Capability::ViewPayments;
        }

        return null;
    }

    private static function isMutation(string $method): bool
    {
        return !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
    }
}
