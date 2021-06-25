<?php

namespace Overtrue\LaravelQcloudCaptcha;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool has(string $app)
 * @method static array validate(string $ticket, ?string $app = null, ?string $nonce = null)
 * @method static array validateWebTicket(string $ticket, string $nonce, ?string $app = null)
 * @method static array validateMiniAppTicket(string $ticket, ?string $app = null)
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaptchaManager::class;
    }
}
