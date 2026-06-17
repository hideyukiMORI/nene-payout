<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NenePayout\Organization\OrganizationResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetOrganizationHandler
{
    public function __construct(
        private GetOrganizationUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $organization = $this->useCase->execute(self::organizationId($request));

        return $this->response->create(OrganizationResponse::toArray($organization));
    }

    private static function organizationId(ServerRequestInterface $request): string
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);

        return is_array($params) && isset($params['organization_id']) && is_string($params['organization_id'])
            ? $params['organization_id']
            : '';
    }
}
