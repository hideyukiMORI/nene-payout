<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListOrganizationsHandler
{
    public function __construct(
        private ListOrganizationsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);

        $result = $this->useCase->execute($pagination->limit, $pagination->offset);

        return $this->response->create((new PaginationResponse(
            items: array_map(
                static fn (Organization $org): array => OrganizationResponse::toArray($org),
                $result->items,
            ),
            limit: $pagination->limit,
            offset: $pagination->offset,
            total: $result->total,
        ))->toArray());
    }
}
