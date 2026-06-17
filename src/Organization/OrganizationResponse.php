<?php

declare(strict_types=1);

namespace NenePayout\Organization;

final class OrganizationResponse
{
    /**
     * Public/audit representation of an organization.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Organization $organization): array
    {
        return [
            'id'            => $organization->id,
            'slug'          => $organization->slug,
            'name'          => $organization->name,
            'custom_domain' => $organization->customDomain,
            'is_active'     => $organization->isActive,
            'created_at'    => $organization->createdAt,
            'updated_at'    => $organization->updatedAt,
        ];
    }
}
