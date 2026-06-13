<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class VendorRouteRegistrar
{
    public function __construct(
        private ListVendorsHandler $listHandler,
        private GetVendorHandler $getHandler,
        private CreateVendorHandler $createHandler,
        private UpdateVendorHandler $updateHandler,
        private DeactivateVendorHandler $deactivateHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list = $this->listHandler;
        $get = $this->getHandler;
        $create = $this->createHandler;
        $update = $this->updateHandler;
        $deactivate = $this->deactivateHandler;

        $router->get('/api/v1/vendors', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->post('/api/v1/vendors', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->get('/api/v1/vendors/{vendor_id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->patch('/api/v1/vendors/{vendor_id}', static fn (ServerRequestInterface $r) => $update->handle($r));
        $router->post('/api/v1/vendors/{vendor_id}/deactivate', static fn (ServerRequestInterface $r) => $deactivate->handle($r));
    }
}
