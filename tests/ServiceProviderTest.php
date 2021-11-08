<?php

namespace Tests;

use Illuminate\Contracts\Support\DeferrableProvider;
use Overtrue\LaravelQcloudCaptcha\CaptchaManager;
use Overtrue\LaravelQcloudCaptcha\CaptchaServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_services_are_registered()
    {
        $this->assertInstanceOf(DeferrableProvider::class, new CaptchaServiceProvider($this->app));

        \config(
            [
                'services' => [
                    'captcha' => [
                        'secret_id' => 'mock-secret-id',
                        'secret_key' => 'mock-secret-key',
                        'app_id' => 'mock-app-id',
                        'app_secret_key' => 'mock-app-secret-key',
                    ],
                ],
            ]
        );

        $this->assertInstanceOf(CaptchaManager::class, app('captcha'));
    }
}
