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

        // Widget embed-code generation grants broad org access — admins only.
        // (The widget runtime under /api/v1/widget/ is token-gated separately.)
        if (str_starts_with($path, '/api/v1/widget-tokens')) {
            return Capability::ManageOrganizationSettings;
        }

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

        if (str_starts_with($path, '/api/v1/payment-executions') && self::isReadMethod($method)) {
            return Capability::ViewPayments;
        }

        return null;
    }

    /**
     * 🔴 Read detection must never be a single equality against GET (#278 / concierge #212).
     * Written that way, a `grep` for the banned form is also what finds this comment, so
     * the literal is deliberately spelled out in prose rather than in code punctuation.
     *
     * HEAD is a read, and {@see \Nene2\Routing\Router} dispatches it to the GET route
     * (RFC 7231 §4.3.2), so the handler is reachable. A single equality leaves HEAD in
     * neither the read branch nor {@see self::isMutation()}, so `resolve()` returns null
     * and CapabilityMiddleware skips both the capability check and — when the token's
     * role is outside the Role enum — the organization-scope check as well. That is a
     * fail-open path: the same request is 403 under GET and reaches the handler under HEAD.
     */
    private static function isReadMethod(string $method): bool
    {
        return in_array($method, ['GET', 'HEAD'], true);
    }

    private static function isMutation(string $method): bool
    {
        return !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
    }
}
