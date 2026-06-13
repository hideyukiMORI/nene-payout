<?php

declare(strict_types=1);

namespace NenePayout\Auth;

use Nene2\Auth\TokenIssuerInterface;
use Nene2\Http\ClockInterface;

final readonly class LoginUseCase
{
    private const TOKEN_TTL_SECONDS = 86400; // 24 hours

    public function __construct(
        private UserRepositoryInterface $users,
        private TokenIssuerInterface $tokenIssuer,
        private ClockInterface $clock,
    ) {
    }

    /**
     * @throws InvalidCredentialsException
     */
    public function execute(LoginInput $input): LoginOutput
    {
        $user = $this->users->findByEmail($input->email);

        if ($user === null || !password_verify($input->password, $user->passwordHash)) {
            throw new InvalidCredentialsException();
        }

        $role = Role::tryFrom($user->role);

        if ($role === null || $user->status !== 'active') {
            throw new InvalidCredentialsException();
        }

        // superadmin belongs to no organization; admin/operator carry their org id.
        $organizationId = $role === Role::Superadmin ? null : $user->organizationId;

        $now = $this->clock->now()->getTimestamp();
        $expiresAt = $now + self::TOKEN_TTL_SECONDS;

        $token = $this->tokenIssuer->issue([
            'sub'    => $user->email,
            'uid'    => $user->id,
            'role'   => $role->value,
            'org_id' => $organizationId,
            'iat'    => $now,
            'exp'    => $expiresAt,
        ]);

        return new LoginOutput(
            token: $token,
            expiresAt: $expiresAt,
            email: $user->email,
            role: $role->value,
            organizationId: $organizationId,
        );
    }
}
