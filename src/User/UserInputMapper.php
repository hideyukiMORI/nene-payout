<?php

declare(strict_types=1);

namespace NenePayout\User;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Auth\Role;

/**
 * Maps and format-validates request bodies into user input DTOs. Throws
 * ValidationException (→ 422) on invalid input. Business invariants (email
 * uniqueness, existence) belong in the use case.
 *
 * Org-scoped management only assigns in-tenant roles; `superadmin` is
 * cross-tenant (terms.md §11) and cannot be created/assigned here.
 */
final class UserInputMapper
{
    private const ASSIGNABLE_ROLES = [Role::Admin->value, Role::Operator->value];

    /**
     * @param array<string, mixed> $body
     */
    public static function create(array $body): CreateUserInput
    {
        $errors = [];

        $email = self::str($body, 'email');
        $role = self::str($body, 'role');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = new ValidationError('email', 'email must be a valid email address.', 'invalid_format');
        }

        if (!in_array($role, self::ASSIGNABLE_ROLES, true)) {
            $errors[] = new ValidationError('role', 'role must be one of admin, operator.', 'invalid_value');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new CreateUserInput(email: $email, role: $role);
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function update(array $body): UpdateUserInput
    {
        $role = self::str($body, 'role');

        if (!in_array($role, self::ASSIGNABLE_ROLES, true)) {
            throw new ValidationException([
                new ValidationError('role', 'role must be one of admin, operator.', 'invalid_value'),
            ]);
        }

        return new UpdateUserInput(role: $role);
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function str(array $body, string $key): string
    {
        return isset($body[$key]) && is_string($body[$key]) ? trim($body[$key]) : '';
    }
}
