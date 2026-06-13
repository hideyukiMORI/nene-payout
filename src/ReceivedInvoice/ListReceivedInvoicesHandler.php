<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListReceivedInvoicesHandler
{
    public function __construct(
        private ListReceivedInvoicesUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);
        $query = $request->getQueryParams();

        $filter = new ReceivedInvoiceFilter(
            status: self::str($query, 'status'),
            vendorId: self::str($query, 'vendor_id'),
            dueFrom: self::str($query, 'due_from'),
            dueTo: self::str($query, 'due_to'),
        );

        $result = $this->useCase->execute($filter, $pagination->limit, $pagination->offset);

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (ReceivedInvoice $i): array => ReceivedInvoiceResponse::toArray($i), $result->items),
            limit: $pagination->limit,
            offset: $pagination->offset,
            total: $result->total,
        ))->toArray());
    }

    /**
     * @param array<string, mixed> $query
     */
    private static function str(array $query, string $key): ?string
    {
        return isset($query[$key]) && is_string($query[$key]) && $query[$key] !== '' ? $query[$key] : null;
    }
}
