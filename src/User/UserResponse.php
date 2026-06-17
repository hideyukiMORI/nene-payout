<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

final class UserResponse
{
    /**
     * Public/audit representation of a user. Never includes `password_hash`
     * (compliance: no credential material in responses or audit snapshots).
     *
     * @return array<string, mixed>
     */
    public static function toArray(User $user): array
    {
        return [
            'id'              => $user->id,
            'organization_id' => $user->organizationId,
            'email'           => $user->email,
            'role'            => $user->role,
            'status'          => $user->status,
            'created_at'      => $user->createdAt,
            'updated_at'      => $user->updatedAt,
        ];
    }
}
