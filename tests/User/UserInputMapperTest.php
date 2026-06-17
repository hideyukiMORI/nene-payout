<?php

declare(strict_types=1);

namespace NenePayout\Tests\User;

use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\User\UserInputMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UserInputMapperTest extends TestCase
{
    /**
     * @param array<string, mixed> $body
     * @return list<string>
     */
    private static function failingFields(array $body): array
    {
        try {
            UserInputMapper::create($body);
        } catch (ValidationException $e) {
            return array_map(static fn (ValidationError $x): string => $x->field, $e->errors());
        }

        self::fail('Expected ValidationException was not thrown.');
    }

    public function test_accepts_valid_create_body(): void
    {
        $input = UserInputMapper::create(['email' => 'user@example.com', 'role' => 'operator']);

        self::assertSame('user@example.com', $input->email);
        self::assertSame('operator', $input->role);
    }

    public function test_trims_email(): void
    {
        self::assertSame('user@example.com', UserInputMapper::create(['email' => '  user@example.com  ', 'role' => 'admin'])->email);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidEmailProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'no at sign' => ['userexample.com'];
        yield 'no domain' => ['user@'];
        yield 'spaces' => ['user name@example.com'];
    }

    #[DataProvider('invalidEmailProvider')]
    public function test_rejects_invalid_email(string $email): void
    {
        self::assertContains('email', self::failingFields(['email' => $email, 'role' => 'admin']));
    }

    public function test_accepts_admin_and_operator_roles(): void
    {
        self::assertSame('admin', UserInputMapper::create(['email' => 'a@example.com', 'role' => 'admin'])->role);
        self::assertSame('operator', UserInputMapper::create(['email' => 'b@example.com', 'role' => 'operator'])->role);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function rejectedRoleProvider(): iterable
    {
        yield 'superadmin is cross-tenant only' => ['superadmin'];
        yield 'unknown role' => ['manager'];
        yield 'empty' => [''];
    }

    #[DataProvider('rejectedRoleProvider')]
    public function test_rejects_non_assignable_role(string $role): void
    {
        self::assertContains('role', self::failingFields(['email' => 'user@example.com', 'role' => $role]));
    }

    public function test_aggregates_every_invalid_field(): void
    {
        $fields = self::failingFields(['email' => 'bad', 'role' => 'superadmin']);

        self::assertContains('email', $fields);
        self::assertContains('role', $fields);
    }

    public function test_update_validates_role(): void
    {
        self::assertSame('admin', UserInputMapper::update(['role' => 'admin'])->role);

        try {
            UserInputMapper::update(['role' => 'superadmin']);
            self::fail('Expected ValidationException.');
        } catch (ValidationException $e) {
            $fields = array_map(static fn (ValidationError $x): string => $x->field, $e->errors());
            self::assertContains('role', $fields);
        }
    }
}
