<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

final class VendorResponse
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(Vendor $vendor): array
    {
        return [
            'id'                  => $vendor->id,
            'organization_id'     => $vendor->organizationId,
            'name'                => $vendor->name,
            'bank_code'           => $vendor->bankCode,
            'branch_code'         => $vendor->branchCode,
            'account_type'        => $vendor->accountType,
            'account_number'      => $vendor->accountNumber,
            'account_name'        => $vendor->accountName,
            'registration_number' => $vendor->registrationNumber,
            'created_at'          => $vendor->createdAt,
            'updated_at'          => $vendor->updatedAt,
        ];
    }
}
