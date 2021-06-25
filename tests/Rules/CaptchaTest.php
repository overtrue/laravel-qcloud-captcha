<?php

namespace Tests\Rules;

use Illuminate\Support\Str;
use Overtrue\LaravelQcloudCaptcha\Captcha;
use Overtrue\LaravelQcloudCaptcha\Rules\Captcha as CaptchaRule;
use Tests\TestCase;

class CaptchaTest extends TestCase
{
    public function test_it_can_validate_tickets()
    {
        // web
        $ticket = Str::random(32);
        $nonce = Str::random(10);
        $rule = new CaptchaRule(null, $nonce);

        Captcha::swap(\Mockery::mock());
        Captcha::shouldReceive('validate')->with($ticket, null, $nonce)->andReturnTrue();
        $this->assertTrue($rule->passes('ticket', $ticket));

        // web with app
        Captcha::shouldReceive('validate')->with($ticket, 'login', $nonce)->andReturnTrue();
        $rule = new CaptchaRule('login', $nonce);
        $this->assertTrue($rule->passes('ticket', $ticket));
    }

    public function test_it_can_validate_mini_app_tickets()
    {
        $rule = new CaptchaRule();

        Captcha::swap(\Mockery::mock());

        $ticket = Str::random(32);
        Captcha::shouldReceive('validate')->with($ticket, null, null)->andReturnTrue();
        $this->assertTrue($rule->passes('ticket', $ticket));
    }
}
