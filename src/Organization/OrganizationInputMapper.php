<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;

/**
 * Maps and format-validates the organization-settings request body into an input
 * DTO. Throws ValidationException (→ 422) on invalid input.
 *
 * Self-service settings only expose `name`; `slug` and `custom_domain` drive
 * tenant resolution (routing) and are managed by superadmin (/organizations).
 */
final class OrganizationInputMapper
{
    private const NAME_MAX_LENGTH = 255;

    /**
     * @param array<string, mixed> $body
     */
    public static function update(array $body): UpdateOrganizationInput
    {
        $errors = [];

        $name = isset($body['name']) && is_string($body['name']) ? trim($body['name']) : '';

        if ($name === '') {
            $errors[] = new ValidationError('name', 'name must not be empty.', 'required');
        } elseif (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            $errors[] = new ValidationError('name', 'name must be at most 255 characters.', 'too_long');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new UpdateOrganizationInput(name: $name);
    }
}
