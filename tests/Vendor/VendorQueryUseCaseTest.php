<?php

declare(strict_types=1);

namespace NenePayout\Tests\Vendor;

use NenePayout\Vendor\GetVendorUseCase;
use NenePayout\Vendor\ListVendorsUseCase;
use NenePayout\Vendor\Vendor;
use NenePayout\Vendor\VendorNotFoundException;
use PHPUnit\Framework\TestCase;

final class VendorQueryUseCaseTest extends TestCase
{
    private function vendor(string $id, string $name, bool $active = true): Vendor
    {
        return new Vendor(
            name: $name,
            bankCode: '0001',
            branchCode: '001',
            accountType: '普通',
            accountNumber: '1234567',
            accountName: 'カナ',
            isActive: $active,
            organizationId: '01ORG00000000000000000001',
            id: $id,
        );
    }

    public function test_list_empty_repository_returns_zero(): void
    {
        $out = (new ListVendorsUseCase(new InMemoryVendorRepository()))->execute(null, 20, 0);

        self::assertSame(0, $out->total);
        self::assertSame([], $out->items);
    }

    public function test_list_excludes_inactive_from_total_and_items(): void
    {
        $repo = new InMemoryVendorRepository(
            $this->vendor('01A', 'Active'),
            $this->vendor('01B', 'Inactive', false),
        );

        $out = (new ListVendorsUseCase($repo))->execute(null, 20, 0);

        self::assertSame(1, $out->total);
        self::assertCount(1, $out->items);
    }

    public function test_list_applies_name_query_filter(): void
    {
        $repo = new InMemoryVendorRepository(
            $this->vendor('01A', 'Acme'),
            $this->vendor('01B', 'Globex'),
        );

        $out = (new ListVendorsUseCase($repo))->execute('Acme', 20, 0);

        self::assertSame(1, $out->total);
        self::assertSame('Acme', $out->items[0]->name);
    }

    public function test_list_pagination_boundaries(): void
    {
        $repo = new InMemoryVendorRepository(
            $this->vendor('01A', 'A'),
            $this->vendor('01B', 'B'),
            $this->vendor('01C', 'C'),
        );
        $useCase = new ListVendorsUseCase($repo);

        // total is the full count regardless of the page window.
        self::assertSame(3, $useCase->execute(null, 2, 0)->total);
        self::assertCount(2, $useCase->execute(null, 2, 0)->items);
        self::assertCount(1, $useCase->execute(null, 2, 2)->items);
        self::assertCount(0, $useCase->execute(null, 2, 5)->items);
    }

    public function test_get_returns_vendor_when_present(): void
    {
        $repo = new InMemoryVendorRepository($this->vendor('01A', 'Acme'));

        self::assertSame('Acme', (new GetVendorUseCase($repo))->execute('01A')->name);
    }

    public function test_get_throws_for_missing_or_inactive_vendor(): void
    {
        $repo = new InMemoryVendorRepository($this->vendor('01B', 'Inactive', false));
        $useCase = new GetVendorUseCase($repo);

        $this->expectException(VendorNotFoundException::class);
        $useCase->execute('01B'); // inactive surfaces as not found
    }
}
