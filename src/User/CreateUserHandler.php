<?php

declare(strict_types=1);

namespace NenePayout\User;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateUserHandler
{
    public function __construct(
        private CreateUserUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = UserInputMapper::create(JsonRequestBodyParser::parse($request));

        $user = $this->useCase->execute(AuthContext::actorUserId($request), $input);

        return $this->response->create(UserResponse::toArray($user), 201);
    }
}
