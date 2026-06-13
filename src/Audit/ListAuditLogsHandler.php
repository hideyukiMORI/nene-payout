<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * GET /api/v1/audit-logs — lists the audit trail for the resolved organization
 * (admin / superadmin via CapabilityResolver). The org is resolved upstream by
 * OrgResolverMiddleware into the request-scoped holder.
 */
final readonly class ListAuditLogsHandler
{
    public function __construct(
        private ListAuditLogsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);

        /** @var array<string, mixed> $query */
        $query = $request->getQueryParams();
        $filter = AuditLogFilterFactory::fromQueryParams($query);

        $result = $this->useCase->execute($filter, $pagination->limit, $pagination->offset);

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (AuditLog $log): array => AuditLogResponse::toArray($log), $result->items),
            limit: $pagination->limit,
            offset: $pagination->offset,
            total: $result->total,
        ))->toArray());
    }
}
