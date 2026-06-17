<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use NenePayout\Organization\OrganizationResponse;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateOrganizationHandler
{
    public function __construct(
        private CreateOrganizationUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = OrganizationManagementInputMapper::create(JsonRequestBodyParser::parse($request));

        $organization = $this->useCase->execute(AuthContext::actorUserId($request), $input);

        return $this->response->create(OrganizationResponse::toArray($organization), 201);
    }
}
