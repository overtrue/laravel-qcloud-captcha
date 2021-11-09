<?php

namespace Overtrue\LaravelQcloudCaptcha;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidConfigException;

class CaptchaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(
            CaptchaManager::class,
            function () {
                $config = $this->normailzeConfig(\config('services.captcha', []));
                return new \Overtrue\LaravelQcloudCaptcha\CaptchaManager($config);
            }
        );

        $this->app->alias(CaptchaManager::class, 'captcha');
    }

    public function boot()
    {
        app('validator')->extend(
            'captcha',
            function ($attribute, $value, $parameters, $validator) {
                $app = $parameters[0] ?? null;
                $nonce = $parameters[1] ?? null;

                // captcha:nonce
                if (!$nonce && \request()->has($app) && !Captcha::has($app)) {
                    $nonce = \request()->get($app);
                    $app = null;
                }

                return (new \Overtrue\LaravelQcloudCaptcha\Rules\Captcha($app, $nonce))->passes($attribute, $value);
            }
        );
    }

    public function provides(): array
    {
        return [CaptchaManager::class, 'captcha'];
    }

    /**
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidConfigException
     */
    protected function normailzeConfig(array $config): array
    {
        $result = [
            'default' => 'main',
            'apps' => [
                'main' => [
                    //
                ],
            ],
        ];

        if (\array_key_exists('secret_id', $config)) {
            $result['apps']['main'] = $config;

            return $result;
        }

        if (\array_key_exists('apps', $config)) {
            if (empty($config['default'])) {
                $config['default'] = key($config['apps']);
            }

            return \array_merge($result, $config);
        }

        throw new InvalidConfigException('Invalid captcha config.');
    }
}
