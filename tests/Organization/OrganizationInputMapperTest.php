<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization;

use Nene2\Validation\ValidationException;
use NenePayout\Organization\OrganizationInputMapper;
use PHPUnit\Framework\TestCase;

final class OrganizationInputMapperTest extends TestCase
{
    public function test_update_accepts_and_trims_a_valid_name(): void
    {
        $input = OrganizationInputMapper::update(['name' => '  Acme 株式会社  ']);

        self::assertSame('Acme 株式会社', $input->name);
    }

    public function test_update_rejects_an_empty_name(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationInputMapper::update(['name' => '   ']);
    }

    public function test_update_rejects_a_missing_name(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationInputMapper::update([]);
    }

    public function test_update_rejects_a_name_over_the_limit(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationInputMapper::update(['name' => str_repeat('a', 256)]);
    }
}
