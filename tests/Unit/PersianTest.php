<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Support\Persian;
use MajidDs\Tests\TestCase;

class PersianTest extends TestCase
{
    public function test_it_converts_latin_digits_to_persian(): void
    {
        $this->assertSame('۱۲۳۴۵۶۷۸۹۰', Persian::digits('1234567890'));
        $this->assertSame('قیمت ۲۵ هزار', Persian::digits('قیمت 25 هزار'));
        $this->assertSame('۴۲', Persian::digits(42));
    }

    public function test_it_converts_arabic_indic_digits_to_persian(): void
    {
        $this->assertSame('۱۲۳', Persian::digits('١٢٣'));
    }

    public function test_it_converts_persian_digits_back_to_latin(): void
    {
        $this->assertSame('1234567890', Persian::latinDigits('۱۲۳۴۵۶۷۸۹۰'));
    }

    public function test_it_formats_numbers_with_persian_separators(): void
    {
        $this->assertSame('۲٬۵۰۰٬۰۰۰', Persian::number(2500000));
        $this->assertSame('۴٫۳', Persian::number(4.3, 1));
    }

    public function test_it_formats_money(): void
    {
        $this->assertSame('۲٬۵۰۰٬۰۰۰ تومان', Persian::money(2500000, 'toman'));
        $this->assertSame('۹۹۰ ریال', Persian::money(990, 'rial'));
        $this->assertSame('۹۹۰', Persian::money(990, 'none'));
        $this->assertSame('۹۹۰ درهم', Persian::money(990, 'درهم'));
    }

    public function test_money_uses_configured_default_currency(): void
    {
        config(['mds.currency' => 'rial']);

        $this->assertSame('۱۰۰ ریال', Persian::money(100));
    }

    public function test_it_formats_file_sizes(): void
    {
        $this->assertSame('۰ بایت', Persian::fileSize(0));
        $this->assertSame('۹۰۰ بایت', Persian::fileSize(900));
        $this->assertSame('۱۵۹ کیلوبایت', Persian::fileSize(162400));
        $this->assertSame('۱٫۵ مگابایت', Persian::fileSize(1572864));
        $this->assertSame('۲ گیگابایت', Persian::fileSize(2 * 1024 ** 3));
    }

    public function test_file_size_supports_latin_digits(): void
    {
        $this->assertSame('159 کیلوبایت', Persian::fileSize(162400, false));
        $this->assertSame('1.5 مگابایت', Persian::fileSize(1572864, false));
    }

    public function test_it_renders_relative_time(): void
    {
        $this->assertSame('لحظاتی پیش', Persian::ago(new \DateTimeImmutable('-10 seconds')));
        $this->assertSame('۵ دقیقه پیش', Persian::ago(new \DateTimeImmutable('-5 minutes')));
        $this->assertSame('۳ ساعت پیش', Persian::ago(new \DateTimeImmutable('-3 hours')));
        $this->assertSame('۲ روز پیش', Persian::ago(new \DateTimeImmutable('-2 days')));
        $this->assertSame('۲ روز دیگر', Persian::ago(new \DateTimeImmutable('+2 days +1 minute')));
    }
}
