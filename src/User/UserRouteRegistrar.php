<?php

declare(strict_types=1);

namespace NenePayout\User;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserRouteRegistrar
{
    public function __construct(
        private ListUsersHandler $listHandler,
        private GetUserHandler $getHandler,
        private CreateUserHandler $createHandler,
        private UpdateUserHandler $updateHandler,
        private DeactivateUserHandler $deactivateHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list = $this->listHandler;
        $get = $this->getHandler;
        $create = $this->createHandler;
        $update = $this->updateHandler;
        $deactivate = $this->deactivateHandler;

        $router->get('/api/v1/users', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->post('/api/v1/users', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->get('/api/v1/users/{user_id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->patch('/api/v1/users/{user_id}', static fn (ServerRequestInterface $r) => $update->handle($r));
        $router->post('/api/v1/users/{user_id}/deactivate', static fn (ServerRequestInterface $r) => $deactivate->handle($r));
    }
}
