<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use RuntimeException;

final class OrganizationSlugConflictException extends RuntimeException
{
    public function __construct(string $detail)
    {
        parent::__construct($detail);
    }

    public static function forSlug(string $slug): self
    {
        return new self(sprintf('An organization with slug %s already exists.', $slug));
    }

    public static function forCustomDomain(string $domain): self
    {
        return new self(sprintf('An organization with custom domain %s already exists.', $domain));
    }
}
