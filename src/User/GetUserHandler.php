<?php

declare(strict_types=1);

namespace NenePayout\User;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetUserHandler
{
    public function __construct(
        private GetUserUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['user_id']) && is_string($params['user_id']) ? $params['user_id'] : '';

        $user = $this->useCase->execute($id);

        return $this->response->create(UserResponse::toArray($user));
    }
}
