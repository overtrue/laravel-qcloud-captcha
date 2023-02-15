<?php

namespace Overtrue\LaravelQcloudCaptcha\Rules;

use Illuminate\Contracts\Validation\Rule;

class Captcha implements Rule
{
    protected ?string $app = null;

    protected ?string $nonce = null;

    public function __construct(?string $app = null, ?string $nonce = null)
    {
        $this->app = $app;
        $this->nonce = $nonce;
    }

    public function passes($attribute, $value)
    {
        try {
            return \Overtrue\LaravelQcloudCaptcha\Captcha::validate($value, $this->app, $this->nonce);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function message()
    {
        return 'The ticket is invalid.';
    }
}
