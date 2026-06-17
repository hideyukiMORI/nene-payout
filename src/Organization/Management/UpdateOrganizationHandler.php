<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NenePayout\Organization\OrganizationResponse;
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
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['organization_id']) && is_string($params['organization_id'])
            ? $params['organization_id']
            : '';

        $input = OrganizationManagementInputMapper::update(JsonRequestBodyParser::parse($request));

        $organization = $this->useCase->execute(AuthContext::actorUserId($request), $id, $input);

        return $this->response->create(OrganizationResponse::toArray($organization));
    }
}
