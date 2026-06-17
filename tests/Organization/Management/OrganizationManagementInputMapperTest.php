<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization\Management;

use Nene2\Validation\ValidationException;
use NenePayout\Organization\Management\OrganizationManagementInputMapper;
use PHPUnit\Framework\TestCase;

final class OrganizationManagementInputMapperTest extends TestCase
{
    public function test_create_accepts_valid_input_and_trims(): void
    {
        $input = OrganizationManagementInputMapper::create([
            'slug' => 'acme-co',
            'name' => '  Acme 株式会社  ',
            'custom_domain' => 'pay.acme.example',
        ]);

        self::assertSame('acme-co', $input->slug);
        self::assertSame('Acme 株式会社', $input->name);
        self::assertSame('pay.acme.example', $input->customDomain);
    }

    public function test_create_defaults_absent_custom_domain_to_null(): void
    {
        $input = OrganizationManagementInputMapper::create(['slug' => 'acme', 'name' => 'Acme']);

        self::assertNull($input->customDomain);
    }

    public function test_create_treats_empty_custom_domain_as_null(): void
    {
        $input = OrganizationManagementInputMapper::create([
            'slug' => 'acme',
            'name' => 'Acme',
            'custom_domain' => '   ',
        ]);

        self::assertNull($input->customDomain);
    }

    public function test_create_rejects_uppercase_slug(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationManagementInputMapper::create(['slug' => 'Acme', 'name' => 'Acme']);
    }

    public function test_create_rejects_slug_with_invalid_chars(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationManagementInputMapper::create(['slug' => 'a_c_me', 'name' => 'Acme']);
    }

    public function test_create_rejects_empty_name(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationManagementInputMapper::create(['slug' => 'acme', 'name' => '']);
    }

    public function test_create_rejects_malformed_custom_domain(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationManagementInputMapper::create([
            'slug' => 'acme',
            'name' => 'Acme',
            'custom_domain' => 'not a domain',
        ]);
    }

    public function test_update_accepts_valid_input(): void
    {
        $input = OrganizationManagementInputMapper::update([
            'name' => 'Renamed',
            'custom_domain' => null,
        ]);

        self::assertSame('Renamed', $input->name);
        self::assertNull($input->customDomain);
    }

    public function test_update_rejects_empty_name(): void
    {
        $this->expectException(ValidationException::class);
        OrganizationManagementInputMapper::update(['name' => '   ']);
    }
}
