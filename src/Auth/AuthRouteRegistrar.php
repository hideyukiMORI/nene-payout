<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class AuthRouteRegistrar
{
    public function __construct(
        private LoginHandler $loginHandler,
        private GetCurrentUserHandler $currentUserHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $login = $this->loginHandler;
        $me = $this->currentUserHandler;

        $router->post('/api/v1/auth/login', static fn (ServerRequestInterface $request) => $login->handle($request));
        $router->get('/api/v1/auth/me', static fn (ServerRequestInterface $request) => $me->handle($request));
    }
}
