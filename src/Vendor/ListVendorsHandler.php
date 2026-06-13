<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListVendorsHandler
{
    public function __construct(
        private ListVendorsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);

        $query = $request->getQueryParams();
        $nameQuery = isset($query['q']) && is_string($query['q']) && $query['q'] !== '' ? $query['q'] : null;

        $result = $this->useCase->execute($nameQuery, $pagination->limit, $pagination->offset);

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (Vendor $vendor): array => VendorResponse::toArray($vendor), $result->items),
            limit: $pagination->limit,
            offset: $pagination->offset,
            total: $result->total,
        ))->toArray());
    }
}
