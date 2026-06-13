<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use DateTimeImmutable;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Support\RegistrationNumber;

/**
 * Format-validates request bodies into received-invoice input DTOs (→ 422).
 * Business invariants (vendor existence, editable status) live in the use case.
 */
final class ReceivedInvoiceInputMapper
{
    private const ALLOWED_TAX_RATES_BPS = [1000, 800];

    /**
     * @param array<string, mixed> $body
     */
    public static function create(array $body): CreateReceivedInvoiceInput
    {
        $f = self::validate($body);

        return new CreateReceivedInvoiceInput(
            vendorId: $f['vendor_id'],
            amount: $f['amount'],
            dueDate: $f['due_date'],
            taxBreakdown: $f['tax_breakdown'],
            registrationNumber: $f['registration_number'],
            vaultDocumentUrl: $f['vault_document_url'],
        );
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function update(array $body): UpdateReceivedInvoiceInput
    {
        $f = self::validate($body);

        return new UpdateReceivedInvoiceInput(
            vendorId: $f['vendor_id'],
            amount: $f['amount'],
            dueDate: $f['due_date'],
            taxBreakdown: $f['tax_breakdown'],
            registrationNumber: $f['registration_number'],
            vaultDocumentUrl: $f['vault_document_url'],
        );
    }

    /**
     * @param array<string, mixed> $body
     * @return array{vendor_id: string, amount: int, due_date: string, tax_breakdown: list<array{tax_rate_bps: int, taxable_amount: int, tax_amount: int}>, registration_number: ?string, vault_document_url: ?string}
     */
    private static function validate(array $body): array
    {
        $errors = [];

        $vendorId = isset($body['vendor_id']) && is_string($body['vendor_id']) ? trim($body['vendor_id']) : '';
        if ($vendorId === '') {
            $errors[] = new ValidationError('vendor_id', 'vendor_id is required.', 'required');
        }

        $amount = 0;
        if (!isset($body['amount']) || !is_int($body['amount']) || $body['amount'] < 1) {
            $errors[] = new ValidationError('amount', 'amount must be a positive integer (minimum currency units).', 'invalid_value');
        } else {
            $amount = $body['amount'];
        }

        $dueDate = isset($body['due_date']) && is_string($body['due_date']) ? trim($body['due_date']) : '';
        if (!self::isValidDate($dueDate)) {
            $errors[] = new ValidationError('due_date', 'due_date must be a valid ISO date (YYYY-MM-DD).', 'invalid_format');
        }

        $registrationNumber = isset($body['registration_number']) && is_string($body['registration_number']) && $body['registration_number'] !== ''
            ? $body['registration_number']
            : null;
        if ($registrationNumber !== null && !RegistrationNumber::isValid($registrationNumber)) {
            $errors[] = new ValidationError('registration_number', 'registration_number must match ^T[0-9]{13}$.', 'invalid_format');
        }

        $vaultDocumentUrl = isset($body['vault_document_url']) && is_string($body['vault_document_url']) && $body['vault_document_url'] !== ''
            ? $body['vault_document_url']
            : null;

        $taxBreakdown = self::validateTaxBreakdown($body['tax_breakdown'] ?? null, $errors);

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return [
            'vendor_id' => $vendorId,
            'amount' => $amount,
            'due_date' => $dueDate,
            'tax_breakdown' => $taxBreakdown,
            'registration_number' => $registrationNumber,
            'vault_document_url' => $vaultDocumentUrl,
        ];
    }

    /**
     * @param list<ValidationError> $errors
     * @return list<array{tax_rate_bps: int, taxable_amount: int, tax_amount: int}>
     */
    private static function validateTaxBreakdown(mixed $raw, array &$errors): array
    {
        if ($raw === null) {
            return [];
        }

        if (!is_array($raw) || !array_is_list($raw)) {
            $errors[] = new ValidationError('tax_breakdown', 'tax_breakdown must be an array.', 'invalid_format');

            return [];
        }

        $items = [];
        foreach ($raw as $i => $item) {
            if (!is_array($item)
                || !isset($item['tax_rate_bps'], $item['taxable_amount'], $item['tax_amount'])
                || !is_int($item['tax_rate_bps'])
                || !is_int($item['taxable_amount'])
                || !is_int($item['tax_amount'])
            ) {
                $errors[] = new ValidationError("tax_breakdown.{$i}", 'Each tax_breakdown item needs integer tax_rate_bps, taxable_amount, tax_amount.', 'invalid_format');

                continue;
            }

            if (!in_array($item['tax_rate_bps'], self::ALLOWED_TAX_RATES_BPS, true)) {
                $errors[] = new ValidationError("tax_breakdown.{$i}.tax_rate_bps", 'tax_rate_bps must be 1000 or 800.', 'invalid_value');

                continue;
            }

            $items[] = [
                'tax_rate_bps' => $item['tax_rate_bps'],
                'taxable_amount' => $item['taxable_amount'],
                'tax_amount' => $item['tax_amount'],
            ];
        }

        return $items;
    }

    private static function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
