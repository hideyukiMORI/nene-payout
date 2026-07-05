<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use LogicException;
use Nene2\Auth\LocalBearerTokenVerifier;
use Nene2\Auth\TokenIssuerInterface;
use Nene2\Auth\TokenVerifierInterface;
use Nene2\Config\AppConfig;
use Nene2\Config\AppEnvironment;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\ClockInterface;
use Nene2\Http\JsonResponseFactory;
use Psr\Container\ContainerInterface;

final readonly class AuthServiceProvider implements ServiceProviderInterface
{
    /**
     * Development-only fallback secret, used **only** in local/test when
     * NENE2_LOCAL_JWT_SECRET is unset. Production must set its own secret —
     * see {@see self::resolveJwtSecret()}. This value is not secret, so signing
     * real tokens with it would be a full authentication bypass.
     */
    private const DEFAULT_DEV_SECRET = 'nene-payout-dev-secret';

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                LocalBearerTokenVerifier::class,
                static function (ContainerInterface $container): LocalBearerTokenVerifier {
                    $config = $container->get(AppConfig::class);

                    if (!$config instanceof AppConfig) {
                        throw new LogicException('Application config service is invalid.');
                    }

                    return new LocalBearerTokenVerifier(self::resolveJwtSecret($config));
                },
            )
            ->set(
                TokenVerifierInterface::class,
                static function (ContainerInterface $container): TokenVerifierInterface {
                    $verifier = $container->get(LocalBearerTokenVerifier::class);

                    if (!$verifier instanceof TokenVerifierInterface) {
                        throw new LogicException('Token verifier service is invalid.');
                    }

                    return $verifier;
                },
            )
            ->set(
                TokenIssuerInterface::class,
                static function (ContainerInterface $container): TokenIssuerInterface {
                    $issuer = $container->get(LocalBearerTokenVerifier::class);

                    if (!$issuer instanceof TokenIssuerInterface) {
                        throw new LogicException('Token issuer service is invalid.');
                    }

                    return $issuer;
                },
            )
            ->set(
                UserRepositoryInterface::class,
                static function (ContainerInterface $container): UserRepositoryInterface {
                    $query = $container->get(DatabaseQueryExecutorInterface::class);

                    if (!$query instanceof DatabaseQueryExecutorInterface) {
                        throw new LogicException('Database query executor service is invalid.');
                    }

                    return new PdoUserRepository($query);
                },
            )
            ->set(
                LoginUseCase::class,
                static function (ContainerInterface $container): LoginUseCase {
                    $users = $container->get(UserRepositoryInterface::class);
                    $issuer = $container->get(TokenIssuerInterface::class);
                    $clock = $container->get(ClockInterface::class);

                    if (!$users instanceof UserRepositoryInterface) {
                        throw new LogicException('User repository service is invalid.');
                    }

                    if (!$issuer instanceof TokenIssuerInterface) {
                        throw new LogicException('Token issuer service is invalid.');
                    }

                    if (!$clock instanceof ClockInterface) {
                        throw new LogicException('Clock service is invalid.');
                    }

                    return new LoginUseCase($users, $issuer, $clock);
                },
            )
            ->set(
                LoginHandler::class,
                static function (ContainerInterface $container): LoginHandler {
                    $useCase = $container->get(LoginUseCase::class);
                    $response = $container->get(JsonResponseFactory::class);

                    if (!$useCase instanceof LoginUseCase) {
                        throw new LogicException('Login use case service is invalid.');
                    }

                    if (!$response instanceof JsonResponseFactory) {
                        throw new LogicException('JSON response factory service is invalid.');
                    }

                    return new LoginHandler($useCase, $response);
                },
            )
            ->set(
                GetCurrentUserHandler::class,
                static function (ContainerInterface $container): GetCurrentUserHandler {
                    $response = $container->get(JsonResponseFactory::class);

                    if (!$response instanceof JsonResponseFactory) {
                        throw new LogicException('JSON response factory service is invalid.');
                    }

                    return new GetCurrentUserHandler($response);
                },
            )
            ->set(
                AuthRouteRegistrar::class,
                static function (ContainerInterface $container): AuthRouteRegistrar {
                    $login = $container->get(LoginHandler::class);
                    $me = $container->get(GetCurrentUserHandler::class);

                    if (!$login instanceof LoginHandler) {
                        throw new LogicException('Login handler service is invalid.');
                    }

                    if (!$me instanceof GetCurrentUserHandler) {
                        throw new LogicException('Current user handler service is invalid.');
                    }

                    return new AuthRouteRegistrar($login, $me);
                },
            )
            ->set(
                InvalidCredentialsExceptionHandler::class,
                static function (ContainerInterface $container): InvalidCredentialsExceptionHandler {
                    $problemDetails = $container->get(ProblemDetailsResponseFactory::class);

                    if (!$problemDetails instanceof ProblemDetailsResponseFactory) {
                        throw new LogicException('Problem details factory service is invalid.');
                    }

                    return new InvalidCredentialsExceptionHandler($problemDetails);
                },
            )
            ->set(
                BearerAuthMiddleware::class,
                static function (ContainerInterface $container): BearerAuthMiddleware {
                    $problemDetails = $container->get(ProblemDetailsResponseFactory::class);
                    $verifier = $container->get(TokenVerifierInterface::class);

                    if (!$problemDetails instanceof ProblemDetailsResponseFactory) {
                        throw new LogicException('Problem details factory service is invalid.');
                    }

                    if (!$verifier instanceof TokenVerifierInterface) {
                        throw new LogicException('Token verifier service is invalid.');
                    }

                    return new BearerAuthMiddleware($problemDetails, $verifier);
                },
            )
            ->set(
                CapabilityMiddleware::class,
                static function (ContainerInterface $container): CapabilityMiddleware {
                    $problemDetails = $container->get(ProblemDetailsResponseFactory::class);

                    if (!$problemDetails instanceof ProblemDetailsResponseFactory) {
                        throw new LogicException('Problem details factory service is invalid.');
                    }

                    return new CapabilityMiddleware($problemDetails);
                },
            );
    }

    /**
     * Resolves the HMAC secret for local bearer tokens, failing closed.
     *
     * The same secret signs operator and service tokens, so a predictable value
     * is a full authentication bypass (a forged superadmin token). In production
     * the secret is therefore mandatory: if NENE2_LOCAL_JWT_SECRET is unset (or
     * empty) we refuse to boot rather than silently fall back to the public dev
     * constant. Local/test may use the dev fallback for convenience.
     */
    private static function resolveJwtSecret(AppConfig $config): string
    {
        $secret = $config->localJwtSecret;

        if ($secret !== null && $secret !== '') {
            return $secret;
        }

        if ($config->environment === AppEnvironment::Production) {
            throw new LogicException(
                'NENE2_LOCAL_JWT_SECRET must be set in production. '
                . 'Generate one with: php -r "echo bin2hex(random_bytes(32));"',
            );
        }

        return self::DEFAULT_DEV_SECRET;
    }
}
