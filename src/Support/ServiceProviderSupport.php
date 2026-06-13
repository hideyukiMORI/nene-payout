<?php

declare(strict_types=1);

namespace NenePayout\Support;

use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\ClockInterface;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\ApplicationServiceProvider;
use Psr\Container\ContainerInterface;

/**
 * Shared container-resolution helpers for domain service providers. Keeps wiring
 * factories small and consistent without repeating the same typed `get()` guards
 * in every provider.
 */
trait ServiceProviderSupport
{
    private static function query(ContainerInterface $c): DatabaseQueryExecutorInterface
    {
        $query = $c->get(DatabaseQueryExecutorInterface::class);

        if (!$query instanceof DatabaseQueryExecutorInterface) {
            throw new LogicException('Database query executor service is invalid.');
        }

        return $query;
    }

    /** @return RequestScopedHolder<string> */
    private static function orgHolder(ContainerInterface $c): RequestScopedHolder
    {
        $holder = $c->get(ApplicationServiceProvider::ORG_ID_HOLDER);

        if (!$holder instanceof RequestScopedHolder) {
            throw new LogicException('Org id holder service is invalid.');
        }

        /** @var RequestScopedHolder<string> $holder */
        return $holder;
    }

    private static function tx(ContainerInterface $c): DatabaseTransactionManagerInterface
    {
        $tx = $c->get(DatabaseTransactionManagerInterface::class);

        if (!$tx instanceof DatabaseTransactionManagerInterface) {
            throw new LogicException('Transaction manager service is invalid.');
        }

        return $tx;
    }

    private static function clock(ContainerInterface $c): ClockInterface
    {
        $clock = $c->get(ClockInterface::class);

        if (!$clock instanceof ClockInterface) {
            throw new LogicException('Clock service is invalid.');
        }

        return $clock;
    }

    private static function json(ContainerInterface $c): JsonResponseFactory
    {
        $json = $c->get(JsonResponseFactory::class);

        if (!$json instanceof JsonResponseFactory) {
            throw new LogicException('JSON response factory service is invalid.');
        }

        return $json;
    }

    private static function problemDetails(ContainerInterface $c): ProblemDetailsResponseFactory
    {
        $pd = $c->get(ProblemDetailsResponseFactory::class);

        if (!$pd instanceof ProblemDetailsResponseFactory) {
            throw new LogicException('Problem details factory service is invalid.');
        }

        return $pd;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private static function service(ContainerInterface $c, string $class): object
    {
        $service = $c->get($class);

        if (!$service instanceof $class) {
            throw new LogicException($class . ' service is invalid.');
        }

        return $service;
    }
}
