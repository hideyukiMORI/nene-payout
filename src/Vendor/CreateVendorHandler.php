<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateVendorHandler
{
    public function __construct(
        private CreateVendorUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = VendorInputMapper::create(JsonRequestBodyParser::parse($request));

        $vendor = $this->useCase->execute(AuthContext::actorUserId($request), $input);

        return $this->response->create(VendorResponse::toArray($vendor), 201);
    }
}
