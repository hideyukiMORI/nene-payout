<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OrganizationsRouteRegistrar
{
    public function __construct(
        private ListOrganizationsHandler $listHandler,
        private GetOrganizationHandler $getHandler,
        private CreateOrganizationHandler $createHandler,
        private UpdateOrganizationHandler $updateHandler,
        private DeactivateOrganizationHandler $deactivateHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list = $this->listHandler;
        $get = $this->getHandler;
        $create = $this->createHandler;
        $update = $this->updateHandler;
        $deactivate = $this->deactivateHandler;

        $router->get('/api/v1/organizations', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->post('/api/v1/organizations', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->get('/api/v1/organizations/{organization_id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->patch('/api/v1/organizations/{organization_id}', static fn (ServerRequestInterface $r) => $update->handle($r));
        $router->post('/api/v1/organizations/{organization_id}/deactivate', static fn (ServerRequestInterface $r) => $deactivate->handle($r));
    }
}
