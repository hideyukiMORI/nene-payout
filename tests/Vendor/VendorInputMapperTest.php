<?php

declare(strict_types=1);

namespace NenePayout\Tests\Vendor;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Vendor\VendorInputMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VendorInputMapperTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private static function validBody(): array
    {
        return [
            'name' => '仕入先株式会社',
            'bank_code' => '0001',
            'branch_code' => '001',
            'account_type' => '普通',
            'account_number' => '1234567',
            'account_name' => 'シイレサキ',
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return list<string>
     */
    private static function failingFields(array $body): array
    {
        try {
            VendorInputMapper::create($body);
        } catch (ValidationException $e) {
            return array_map(static fn (ValidationError $x): string => $x->field, $e->errors());
        }

        self::fail('Expected ValidationException was not thrown.');
    }

    public function test_accepts_valid_minimum_and_defaults_registration_number_to_null(): void
    {
        $input = VendorInputMapper::create(self::validBody());

        self::assertSame('仕入先株式会社', $input->name);
        self::assertSame('0001', $input->bankCode);
        self::assertSame('001', $input->branchCode);
        self::assertSame('普通', $input->accountType);
        self::assertSame('1234567', $input->accountNumber);
        self::assertNull($input->registrationNumber);
    }

    public function test_trims_string_fields(): void
    {
        $input = VendorInputMapper::create([...self::validBody(), 'name' => '  Acme  ', 'account_name' => '  カナ  ']);

        self::assertSame('Acme', $input->name);
        self::assertSame('カナ', $input->accountName);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function bankCodeProvider(): iterable
    {
        yield '3 digits (too short)' => ['123', 'bank_code'];
        yield '5 digits (too long)' => ['12345', 'bank_code'];
        yield 'non-digit' => ['12a4', 'bank_code'];
        yield 'empty' => ['', 'bank_code'];
    }

    #[DataProvider('bankCodeProvider')]
    public function test_bank_code_boundaries(string $bankCode, string $field): void
    {
        self::assertContains($field, self::failingFields([...self::validBody(), 'bank_code' => $bankCode]));
    }

    public function test_bank_code_exactly_four_is_accepted(): void
    {
        self::assertSame('9999', VendorInputMapper::create([...self::validBody(), 'bank_code' => '9999'])->bankCode);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function branchCodeRejectProvider(): iterable
    {
        yield '2 digits' => ['12'];
        yield '4 digits' => ['1234'];
        yield 'non-digit' => ['a12'];
    }

    #[DataProvider('branchCodeRejectProvider')]
    public function test_branch_code_rejects(string $branchCode): void
    {
        self::assertContains('branch_code', self::failingFields([...self::validBody(), 'branch_code' => $branchCode]));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function accountNumberRejectProvider(): iterable
    {
        yield 'empty (0 digits)' => [''];
        yield '8 digits (too long)' => ['12345678'];
        yield 'non-digit' => ['12a'];
    }

    #[DataProvider('accountNumberRejectProvider')]
    public function test_account_number_rejects(string $accountNumber): void
    {
        self::assertContains('account_number', self::failingFields([...self::validBody(), 'account_number' => $accountNumber]));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function accountNumberAcceptProvider(): iterable
    {
        yield '1 digit (min)' => ['1'];
        yield '7 digits (max)' => ['1234567'];
    }

    #[DataProvider('accountNumberAcceptProvider')]
    public function test_account_number_accepts_boundaries(string $accountNumber): void
    {
        self::assertSame($accountNumber, VendorInputMapper::create([...self::validBody(), 'account_number' => $accountNumber])->accountNumber);
    }

    public function test_account_type_accepts_only_futsu_or_toza(): void
    {
        self::assertSame('普通', VendorInputMapper::create([...self::validBody(), 'account_type' => '普通'])->accountType);
        self::assertSame('当座', VendorInputMapper::create([...self::validBody(), 'account_type' => '当座'])->accountType);
        self::assertContains('account_type', self::failingFields([...self::validBody(), 'account_type' => 'ordinary']));
        self::assertContains('account_type', self::failingFields([...self::validBody(), 'account_type' => '']));
    }

    public function test_name_required(): void
    {
        self::assertContains('name', self::failingFields([...self::validBody(), 'name' => '']));
        self::assertContains('name', self::failingFields([...self::validBody(), 'name' => '   ']));
    }

    /**
     * @return iterable<string, array{string|null, bool}>
     */
    public static function registrationNumberProvider(): iterable
    {
        yield 'null' => [null, true];
        yield 'empty string treated as null' => ['', true];
        yield 'T + 13 digits' => ['T1234567890123', true];
        yield 'T + 12 digits' => ['T123456789012', false];
        yield 'T + 14 digits' => ['T12345678901234', false];
        yield 'lowercase t' => ['t1234567890123', false];
        yield 'missing T' => ['1234567890123', false];
    }

    #[DataProvider('registrationNumberProvider')]
    public function test_registration_number_boundaries(?string $value, bool $valid): void
    {
        $body = self::validBody();
        if ($value !== null) {
            $body['registration_number'] = $value;
        }

        if ($valid) {
            $input = VendorInputMapper::create($body);
            self::assertSame($value === '' || $value === null ? null : $value, $input->registrationNumber);

            return;
        }

        self::assertContains('registration_number', self::failingFields($body));
    }

    public function test_aggregates_every_invalid_field(): void
    {
        $fields = self::failingFields([
            'name' => '',
            'bank_code' => '12',
            'branch_code' => '12',
            'account_type' => 'x',
            'account_number' => '',
            'account_name' => '',
            'registration_number' => 'bad',
        ]);

        foreach (['name', 'bank_code', 'branch_code', 'account_type', 'account_number', 'account_name', 'registration_number'] as $field) {
            self::assertContains($field, $fields);
        }
    }

    public function test_update_uses_the_same_validation(): void
    {
        try {
            VendorInputMapper::update([...self::validBody(), 'bank_code' => '1']);
            self::fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $fields = array_map(static fn (ValidationError $x): string => $x->field, $e->errors());
            self::assertContains('bank_code', $fields);
        }
    }

    public function test_update_accepts_valid_body(): void
    {
        self::assertSame('Acme', VendorInputMapper::update([...self::validBody(), 'name' => 'Acme'])->name);
    }
}
