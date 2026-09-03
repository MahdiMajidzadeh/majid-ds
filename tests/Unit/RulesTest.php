<?php

namespace MajidDs\Tests\Unit;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use MajidDs\Rules\BankCard;
use MajidDs\Rules\IranMobile;
use MajidDs\Rules\NationalId;
use MajidDs\Rules\Sheba;
use MajidDs\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RulesTest extends TestCase
{
    /**
     * Each rule, a value it accepts, a value it rejects, and the two
     * built-in messages.
     *
     * @return array<string, array{0: ValidationRule, 1: string, 2: string, 3: string, 4: string}>
     */
    public static function rules(): array
    {
        return [
            'national id' => [new NationalId, '۰۰۱۳۵۴۲۸۷۷', '0013542878', 'کد ملی معتبر نیست', 'not a valid national ID'],
            'mobile' => [new IranMobile, '+98 912 345 6789', '02122334455', 'شماره موبایل معتبر نیست', 'not a valid mobile number'],
            'sheba' => [new Sheba, 'IR06 0620 0000 0020 0000 0000 01', 'IR070620000000200000000001', 'شماره شبا معتبر نیست', 'not a valid Sheba number'],
            'bank card' => [new BankCard, '6037 9911 0000 0003', '6037991100000004', 'شماره کارت معتبر نیست', 'not a valid card number'],
        ];
    }

    #[DataProvider('rules')]
    public function test_rule_accepts_and_rejects(ValidationRule $rule, string $good, string $bad, string $fa, string $en): void
    {
        $this->assertTrue(Validator::make(['field' => $good], ['field' => $rule])->passes());

        $failed = Validator::make(['field' => $bad], ['field' => $rule]);

        $this->assertTrue($failed->fails());

        // Persian message by default, with the attribute name substituted...
        $message = $failed->errors()->first('field');
        $this->assertStringContainsString($fa, $message);
        $this->assertStringContainsString('field', $message);
        $this->assertStringNotContainsString(':attribute', $message);

        // ...English when the kit is switched to Latin output.
        config()->set('mds.persian_digits', false);

        $this->assertStringContainsString($en, Validator::make(['field' => $bad], ['field' => $rule])->errors()->first('field'));
    }

    public function test_a_custom_message_replaces_the_built_in_one(): void
    {
        $validator = Validator::make(['code' => '1'], ['code' => new NationalId('Ten digits, please.')]);

        $this->assertSame('Ten digits, please.', $validator->errors()->first('code'));
    }

    public function test_non_string_values_fail_instead_of_throwing(): void
    {
        $this->assertTrue(Validator::make(['x' => ['array']], ['x' => new IranMobile])->fails());
        $this->assertTrue(Validator::make(['x' => 9123456789], ['x' => new IranMobile])->passes());
    }
}
