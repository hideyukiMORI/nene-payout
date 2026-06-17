<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateOrganizationHandler
{
    public function __construct(
        private UpdateOrganizationUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = OrganizationInputMapper::update(JsonRequestBodyParser::parse($request));

        $organization = $this->useCase->execute(AuthContext::actorUserId($request), $input);

        return $this->response->create(OrganizationResponse::toArray($organization));
    }
}
