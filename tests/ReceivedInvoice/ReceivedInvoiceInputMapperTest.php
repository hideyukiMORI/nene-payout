<?php

declare(strict_types=1);

namespace NenePayout\Tests\ReceivedInvoice;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\ReceivedInvoice\ReceivedInvoiceInputMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReceivedInvoiceInputMapperTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function validBody(): array
    {
        return [
            'vendor_id' => '01VENDOR000000000000000001',
            'amount' => 100000,
            'due_date' => '2026-07-31',
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return list<string>
     */
    private static function failingFields(array $body): array
    {
        try {
            ReceivedInvoiceInputMapper::create($body);
        } catch (ValidationException $e) {
            return array_map(static fn (ValidationError $x): string => $x->field, $e->errors());
        }

        self::fail('Expected ValidationException was not thrown.');
    }

    public function test_accepts_valid_minimum(): void
    {
        $input = ReceivedInvoiceInputMapper::create(self::validBody());

        self::assertSame('01VENDOR000000000000000001', $input->vendorId);
        self::assertSame(100000, $input->amount);
        self::assertSame('2026-07-31', $input->dueDate);
        self::assertSame([], $input->taxBreakdown);
        self::assertNull($input->registrationNumber);
        self::assertNull($input->vaultDocumentUrl);
    }

    public function test_vendor_id_required(): void
    {
        self::assertContains('vendor_id', self::failingFields([...self::validBody(), 'vendor_id' => '']));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function amountRejectProvider(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'string numeric' => ['100'];
        yield 'float' => [1.5];
        yield 'bool' => [true];
    }

    #[DataProvider('amountRejectProvider')]
    public function test_amount_rejects_non_positive_integers(mixed $amount): void
    {
        self::assertContains('amount', self::failingFields([...self::validBody(), 'amount' => $amount]));
    }

    public function test_amount_missing_is_rejected(): void
    {
        $body = self::validBody();
        unset($body['amount']);
        self::assertContains('amount', self::failingFields($body));
    }

    public function test_amount_one_is_the_accepted_minimum(): void
    {
        self::assertSame(1, ReceivedInvoiceInputMapper::create([...self::validBody(), 'amount' => 1])->amount);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function dueDateRejectProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'no zero padding' => ['2026-7-31'];
        yield 'invalid month' => ['2026-13-01'];
        yield 'non-existent day' => ['2026-02-30'];
        yield 'slashes' => ['2026/07/31'];
        yield 'datetime' => ['2026-07-31T00:00:00'];
    }

    #[DataProvider('dueDateRejectProvider')]
    public function test_due_date_rejects_invalid_formats(string $dueDate): void
    {
        self::assertContains('due_date', self::failingFields([...self::validBody(), 'due_date' => $dueDate]));
    }

    public function test_due_date_accepts_leap_day(): void
    {
        self::assertSame('2028-02-29', ReceivedInvoiceInputMapper::create([...self::validBody(), 'due_date' => '2028-02-29'])->dueDate);
    }

    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public static function taxBreakdownProvider(): iterable
    {
        yield 'null → empty' => [null, true];
        yield 'empty list' => [[], true];
        yield 'rate 1000 ok' => [[['tax_rate_bps' => 1000, 'taxable_amount' => 100000, 'tax_amount' => 10000]], true];
        yield 'rate 800 ok' => [[['tax_rate_bps' => 800, 'taxable_amount' => 100000, 'tax_amount' => 8000]], true];
        yield 'rate 1001 rejected' => [[['tax_rate_bps' => 1001, 'taxable_amount' => 1, 'tax_amount' => 0]], false];
        yield 'rate 0 rejected' => [[['tax_rate_bps' => 0, 'taxable_amount' => 1, 'tax_amount' => 0]], false];
        yield 'missing field rejected' => [[['tax_rate_bps' => 1000, 'taxable_amount' => 1]], false];
        yield 'non-int amount rejected' => [[['tax_rate_bps' => 1000, 'taxable_amount' => '1', 'tax_amount' => 0]], false];
        yield 'non-list rejected' => [['tax_rate_bps' => 1000], false];
    }

    #[DataProvider('taxBreakdownProvider')]
    public function test_tax_breakdown_boundaries(mixed $taxBreakdown, bool $valid): void
    {
        $body = self::validBody();
        if ($taxBreakdown !== null) {
            $body['tax_breakdown'] = $taxBreakdown;
        }

        if ($valid) {
            $input = ReceivedInvoiceInputMapper::create($body);
            self::assertSame(is_array($taxBreakdown) && array_is_list($taxBreakdown) ? $taxBreakdown : [], $input->taxBreakdown);

            return;
        }

        $hasTaxError = array_filter(self::failingFields($body), static fn (string $f): bool => str_starts_with($f, 'tax_breakdown'));
        self::assertNotEmpty($hasTaxError);
    }

    public function test_optional_vault_document_url_is_passed_through(): void
    {
        $input = ReceivedInvoiceInputMapper::create([...self::validBody(), 'vault_document_url' => 'https://vault.example/doc/1']);
        self::assertSame('https://vault.example/doc/1', $input->vaultDocumentUrl);
    }

    public function test_aggregates_multiple_errors(): void
    {
        $fields = self::failingFields(['vendor_id' => '', 'amount' => 0, 'due_date' => 'nope']);

        self::assertContains('vendor_id', $fields);
        self::assertContains('amount', $fields);
        self::assertContains('due_date', $fields);
    }
}
