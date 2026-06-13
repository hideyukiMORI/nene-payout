<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Support\RegistrationNumber;

/**
 * Maps and format-validates request bodies into vendor input DTOs. Throws
 * ValidationException (→ 422) on invalid input. Business invariants, if any,
 * belong in the use case.
 */
final class VendorInputMapper
{
    private const ACCOUNT_TYPES = ['普通', '当座'];

    /**
     * @param array<string, mixed> $body
     */
    public static function create(array $body): CreateVendorInput
    {
        $f = self::validate($body);

        return new CreateVendorInput(
            name: $f['name'],
            bankCode: $f['bank_code'],
            branchCode: $f['branch_code'],
            accountType: $f['account_type'],
            accountNumber: $f['account_number'],
            accountName: $f['account_name'],
            registrationNumber: $f['registration_number'],
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function update(array $body): UpdateVendorInput
    {
        $f = self::validate($body);

        return new UpdateVendorInput(
            name: $f['name'],
            bankCode: $f['bank_code'],
            branchCode: $f['branch_code'],
            accountType: $f['account_type'],
            accountNumber: $f['account_number'],
            accountName: $f['account_name'],
            registrationNumber: $f['registration_number'],
        );
    }

    /**
     * @param array<string, mixed> $body
     * @return array{name: string, bank_code: string, branch_code: string, account_type: string, account_number: string, account_name: string, registration_number: ?string}
     */
    private static function validate(array $body): array
    {
        $errors = [];

        $name = self::str($body, 'name');
        $bankCode = self::str($body, 'bank_code');
        $branchCode = self::str($body, 'branch_code');
        $accountType = self::str($body, 'account_type');
        $accountNumber = self::str($body, 'account_number');
        $accountName = self::str($body, 'account_name');
        $registrationNumber = self::nullableStr($body, 'registration_number');

        if ($name === '') {
            $errors[] = new ValidationError('name', 'Name is required.', 'required');
        }

        if (preg_match('/^[0-9]{4}$/', $bankCode) !== 1) {
            $errors[] = new ValidationError('bank_code', 'bank_code must be 4 digits.', 'invalid_format');
        }

        if (preg_match('/^[0-9]{3}$/', $branchCode) !== 1) {
            $errors[] = new ValidationError('branch_code', 'branch_code must be 3 digits.', 'invalid_format');
        }

        if (!in_array($accountType, self::ACCOUNT_TYPES, true)) {
            $errors[] = new ValidationError('account_type', 'account_type must be 普通 or 当座.', 'invalid_value');
        }

        if (preg_match('/^[0-9]{1,7}$/', $accountNumber) !== 1) {
            $errors[] = new ValidationError('account_number', 'account_number must be up to 7 digits.', 'invalid_format');
        }

        if ($accountName === '') {
            $errors[] = new ValidationError('account_name', 'account_name is required.', 'required');
        }

        if ($registrationNumber !== null && !RegistrationNumber::isValid($registrationNumber)) {
            $errors[] = new ValidationError('registration_number', 'registration_number must match ^T[0-9]{13}$.', 'invalid_format');
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'name' => $name,
            'bank_code' => $bankCode,
            'branch_code' => $branchCode,
            'account_type' => $accountType,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'registration_number' => $registrationNumber,
        ];
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function str(array $body, string $key): string
    {
        return isset($body[$key]) && is_string($body[$key]) ? trim($body[$key]) : '';
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function nullableStr(array $body, string $key): ?string
    {
        if (!isset($body[$key]) || !is_string($body[$key])) {
            return null;
        }

        $value = trim($body[$key]);

        return $value !== '' ? $value : null;
    }
}
