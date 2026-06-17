<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NenePayout\Organization\OrganizationResponse;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DeactivateOrganizationHandler
{
    public function __construct(
        private DeactivateOrganizationUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['organization_id']) && is_string($params['organization_id'])
            ? $params['organization_id']
            : '';

        $organization = $this->useCase->execute(AuthContext::actorUserId($request), $id);

        return $this->response->create(OrganizationResponse::toArray($organization));
    }
}
