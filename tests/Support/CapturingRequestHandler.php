<?php

declare(strict_types=1);

namespace NenePayout\Tests\Support;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Test double that records the request it handled and returns a fixed status.
 */
final class CapturingRequestHandler implements RequestHandlerInterface
{
    public ?ServerRequestInterface $seen = null;

    public function __construct(
        private readonly Psr17Factory $psr17 = new Psr17Factory(),
        private readonly int $status = 200,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->seen = $request;

        return $this->psr17->createResponse($this->status);
    }
}
