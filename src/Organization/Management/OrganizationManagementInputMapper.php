<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;

/**
 * Maps and format-validates superadmin organization-management request bodies
 * into input DTOs. Throws ValidationException (→ 422) on invalid input. Slug /
 * custom_domain uniqueness (409) is a business invariant checked in the use case.
 */
final class OrganizationManagementInputMapper
{
    private const NAME_MAX_LENGTH = 255;
    private const SLUG_MAX_LENGTH = 100;
    private const DOMAIN_MAX_LENGTH = 255;
    private const SLUG_PATTERN = '/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/';
    private const DOMAIN_PATTERN = '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/i';

    /**
     * @param array<string, mixed> $body
     */
    public static function create(array $body): CreateOrganizationInput
    {
        $errors = [];

        $slug = self::str($body, 'slug');
        $name = self::str($body, 'name');
        $customDomain = self::customDomain($body, $errors);

        if ($slug === '' || preg_match(self::SLUG_PATTERN, $slug) !== 1 || mb_strlen($slug) > self::SLUG_MAX_LENGTH) {
            $errors[] = new ValidationError('slug', 'slug must be lowercase alphanumeric with hyphens (max 100).', 'invalid_format');
        }

        if ($name === '') {
            $errors[] = new ValidationError('name', 'name must not be empty.', 'required');
        } elseif (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            $errors[] = new ValidationError('name', 'name must be at most 255 characters.', 'too_long');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new CreateOrganizationInput(slug: $slug, name: $name, customDomain: $customDomain);
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function update(array $body): UpdateOrganizationInput
    {
        $errors = [];

        $name = self::str($body, 'name');
        $customDomain = self::customDomain($body, $errors);

        if ($name === '') {
            $errors[] = new ValidationError('name', 'name must not be empty.', 'required');
        } elseif (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            $errors[] = new ValidationError('name', 'name must be at most 255 characters.', 'too_long');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new UpdateOrganizationInput(name: $name, customDomain: $customDomain);
    }

    /**
     * Optional custom_domain: absent/null/'' → null; otherwise a validated host.
     *
     * @param array<string, mixed> $body
     * @param list<ValidationError> $errors
     */
    private static function customDomain(array $body, array &$errors): ?string
    {
        // isset() is false for both an absent key and an explicit null.
        if (!isset($body['custom_domain'])) {
            return null;
        }

        if (!is_string($body['custom_domain'])) {
            $errors[] = new ValidationError('custom_domain', 'custom_domain must be a string or null.', 'invalid_type');

            return null;
        }

        $domain = trim($body['custom_domain']);

        if ($domain === '') {
            return null;
        }

        if (mb_strlen($domain) > self::DOMAIN_MAX_LENGTH || preg_match(self::DOMAIN_PATTERN, $domain) !== 1) {
            $errors[] = new ValidationError('custom_domain', 'custom_domain must be a valid hostname.', 'invalid_format');

            return null;
        }

        return $domain;
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function str(array $body, string $key): string
    {
        return isset($body[$key]) && is_string($body[$key]) ? trim($body[$key]) : '';
    }
}
