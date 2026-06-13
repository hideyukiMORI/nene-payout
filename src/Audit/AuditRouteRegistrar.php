<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuditRouteRegistrar
{
    public function __construct(
        private ListAuditLogsHandler $listHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list = $this->listHandler;

        $router->get('/api/v1/audit-logs', static fn (ServerRequestInterface $request) => $list->handle($request));
    }
}
