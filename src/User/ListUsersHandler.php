<?php

declare(strict_types=1);

namespace NenePayout\User;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Nene2\Http\PaginationResponse;
use NenePayout\Auth\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListUsersHandler
{
    public function __construct(
        private ListUsersUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);

        $result = $this->useCase->execute($pagination->limit, $pagination->offset);

        return $this->response->create((new PaginationResponse(
            items: array_map(static fn (User $user): array => UserResponse::toArray($user), $result->items),
            limit: $pagination->limit,
            offset: $pagination->offset,
            total: $result->total,
        ))->toArray());
    }
}
