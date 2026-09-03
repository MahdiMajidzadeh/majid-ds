<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Support\Iran;
use MajidDs\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class IranTest extends TestCase
{
    public function test_digits_keeps_latin_digits_from_any_keyboard(): void
    {
        $this->assertSame('09123456789', Iran::digits('۰۹۱۲ ۳۴۵-۶۷۸۹'));
        $this->assertSame('123', Iran::digits('a١b٢c٣'));
        $this->assertSame('', Iran::digits(null));
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public static function nationalIds(): array
    {
        return [
            'valid' => ['0013542877', true],
            'valid, Persian digits' => ['۰۰۱۳۵۴۲۸۷۷', true],
            'valid, spaced' => ['001 354 2877', true],
            'wrong check digit' => ['0013542878', false],
            'too short' => ['013542877', false],
            'too long' => ['00013542877', false],
            'letters' => ['00135428x7', false],
            'repeated digits pass the arithmetic but are never issued' => ['1111111111', false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('nationalIds')]
    public function test_national_id(string $value, bool $valid): void
    {
        $this->assertSame($valid, Iran::nationalId($value));
    }

    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function mobiles(): array
    {
        return [
            'local' => ['09123456789', '09123456789'],
            'without the leading zero' => ['9123456789', '09123456789'],
            'international +98' => ['+98 912 345 6789', '09123456789'],
            'international 0098' => ['0098-912-3456789', '09123456789'],
            'Persian digits' => ['۰۹۱۲۳۴۵۶۷۸۹', '09123456789'],
            'landline is not a mobile' => ['02122334455', null],
            'too short' => ['0912345678', null],
            'too long' => ['091234567890', null],
            'empty' => ['', null],
        ];
    }

    #[DataProvider('mobiles')]
    public function test_mobile(string $value, ?string $normalized): void
    {
        $this->assertSame($normalized, Iran::normalizeMobile($value));
        $this->assertSame($normalized !== null, Iran::mobile($value));
    }

    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function shebas(): array
    {
        return [
            'canonical' => ['IR060620000000200000000001', 'IR060620000000200000000001'],
            'spaced, as the input masks it' => ['IR06 0620 0000 0020 0000 0000 01', 'IR060620000000200000000001'],
            'without the country code' => ['060620000000200000000001', 'IR060620000000200000000001'],
            'lowercase' => ['ir060620000000200000000001', 'IR060620000000200000000001'],
            'Persian digits' => ['IR۰۶۰۶۲۰۰۰۰۰۰۰۲۰۰۰۰۰۰۰۰۰۰۱', 'IR060620000000200000000001'],
            'wrong check digits' => ['IR070620000000200000000001', null],
            'one digit changed' => ['IR060620000000200000000002', null],
            'too short' => ['IR06062000000020000000001', null],
            'another country' => ['DE89370400440532013000', null],
            'empty' => ['', null],
        ];
    }

    #[DataProvider('shebas')]
    public function test_sheba(string $value, ?string $normalized): void
    {
        $this->assertSame($normalized, Iran::normalizeSheba($value));
        $this->assertSame($normalized !== null, Iran::sheba($value));
    }

    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function cards(): array
    {
        return [
            'plain' => ['6037991100000003', '6037991100000003'],
            'grouped, as the input masks it' => ['6037 9911 0000 0003', '6037991100000003'],
            'Persian digits' => ['۶۰۳۷۹۹۱۱۰۰۰۰۰۰۰۳', '6037991100000003'],
            'fails Luhn' => ['6037991100000004', null],
            'fifteen digits' => ['603799110000000', null],
            'seventeen digits' => ['60379911000000030', null],
            'empty' => ['', null],
        ];
    }

    #[DataProvider('cards')]
    public function test_bank_card(string $value, ?string $normalized): void
    {
        $this->assertSame($normalized, Iran::normalizeBankCard($value));
        $this->assertSame($normalized !== null, Iran::bankCard($value));
    }
}
