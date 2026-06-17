<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class OrganizationRouteRegistrar
{
    public function __construct(
        private GetOrganizationHandler $getHandler,
        private UpdateOrganizationHandler $updateHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $get = $this->getHandler;
        $update = $this->updateHandler;

        $router->get('/api/v1/organization', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->patch('/api/v1/organization', static fn (ServerRequestInterface $r) => $update->handle($r));
    }
}
