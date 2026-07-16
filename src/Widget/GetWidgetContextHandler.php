<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Organization\OrganizationRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Bootstraps the embedded widget shell: resolves the organization from the
 * widget token (set on the holder by {@see WidgetAuthMiddleware}) and returns the
 * org name, locale, and the capability surface the widget exposes.
 */
final readonly class GetWidgetContextHandler
{
    /** @var list<string> Capability surface exposed inside the widget (Mode B). */
    private const CAPABILITIES = ['manage_invoices', 'manage_vendors', 'initiate_payment', 'view_payments'];

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private RequestScopedHolder $orgId,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organizationId = (string) $this->orgId->get();
        $organization = $this->organizations->findById($organizationId);

        return $this->response->create([
            'organization_id'   => $organizationId,
            'organization_name' => $organization?->name,
            'locale'            => 'ja',
            'capabilities'      => self::CAPABILITIES,
        ]);
    }
}
