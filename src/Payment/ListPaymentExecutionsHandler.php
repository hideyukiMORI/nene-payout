<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListPaymentExecutionsHandler
{
    public function __construct(
        private ListPaymentExecutionsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);
        $query = $request->getQueryParams();

        $filter = new PaymentExecutionFilter(
            status: self::str($query, 'status'),
            receivedInvoiceId: self::str($query, 'received_invoice_id'),
        );

        $result = $this->useCase->execute($filter, $pagination->limit, $pagination->offset);

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (PaymentExecution $p): array => PaymentExecutionResponse::toArray($p), $result->items),
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
