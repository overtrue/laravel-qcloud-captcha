<?php

namespace Overtrue\LaravelQcloudCaptcha;

use Overtrue\LaravelQcloudCaptcha\Exceptions\Exception;
use Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidArgumentException;
use Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidConfigException;
use Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidTicketException;
use TencentCloud\Captcha\V20190722\CaptchaClient;
use TencentCloud\Captcha\V20190722\Models\DescribeCaptchaMiniRiskResultRequest;
use TencentCloud\Captcha\V20190722\Models\DescribeCaptchaResultRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;

class CaptchaManager
{
    protected array $config = [];
    protected array $clients = [];
    protected ?string $defaultApp = null;

    public const VALIDATED_OK = 1;

    public const CHANNEL_WEB = 'web';

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->defaultApp = \key($this->config['apps']) ?? 'main';
    }

    /**
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\Exception
     */
    public function validate(string $ticket, ?string $app = null, ?string $nonce = null): array
    {
        $app = $app ?? $this->defaultApp;
        $config = $this->getAppConfig($app);

        if (!$nonce || $config['channel'] === self::CHANNEL_WEB) {
            if (empty($nonce)) {
                throw new InvalidArgumentException('Invalid $nonce for web captcha validation.');
            }
            return $this->validateWebTicket($ticket, $nonce, $app);
        }

        return $this->validateMiniAppTicket($ticket, $app);
    }

    public function has(string $app): bool
    {
        return \array_key_exists($app, $this->config['apps']);
    }

    /**
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidConfigException
     */
    public function getClient(?string $app = null)
    {
        $app = $app ?? $this->defaultApp;

        if (!$this->clients[$app]) {
            $config = $this->getAppConfig($app);

            if (empty($config['secret_id']) || empty($config['secret_key'])) {
                throw new InvalidConfigException(\sprintf('Captcha config for `%s` is invalid.', $app));
            }

            $credential = new Credential($config['secret_id'], $config['secret_key']);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint($config['endpoint'] ?? 'captcha.tencentcloudapi.com');

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);

            $this->clients[$app] = new CaptchaClient($credential, "", $clientProfile);
        }

        return $this->clients[$app];
    }

    /**
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\Exception
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidConfigException
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidTicketException
     */
    public function validateWebTicket(string $ticket, string $nonce, ?string $app = null): array
    {
        $app = $app ?? $this->defaultApp;
        $config = $this->getAppConfig($app);

        $request = new DescribeCaptchaResultRequest();
        $request->fromJsonString(
            \json_encode(
                [
                    "CaptchaType" => 9,
                    "Ticket" => $ticket,
                    "UserIp" => \request()->getClientIp(),
                    "Randstr" => $nonce,
                    "CaptchaAppId" => $config['app_id'] ?? null,
                    "AppSecretKey" => $config['app_secret_key'] ?? null,
                ]
            )
        );

        $response = \json_decode($this->getClient($app)->DescribeCaptchaResult($request)->toJsonString(), true) ?? [];

        if (empty($response['CaptchaCode'])) {
            throw new Exception('API 调用失败(empty response)');
        }

        if ($response['CaptchaCode'] !== self::VALIDATED_OK) {
            throw new InvalidTicketException($ticket, $response);
        }

        return $response;
    }

    /**
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\Exception
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidConfigException
     * @throws \Overtrue\LaravelQcloudCaptcha\Exceptions\InvalidTicketException
     */
    public function validateMiniAppTicket(string $ticket, ?string $app = null): array
    {
        $app = $app ?? $this->defaultApp;
        $config = $this->getAppConfig($app);

        $request = new DescribeCaptchaMiniRiskResultRequest();
        $request->fromJsonString(
            \json_encode(
                [
                    "CaptchaType" => 9,
                    "Ticket" => $ticket,
                    "UserIp" => \request()->getClientIp(),
                    "CaptchaAppId" => $config['app_id'] ?? null,
                    "AppSecretKey" => $config['app_secret_key'] ?? null,
                ]
            )
        );

        $response = \json_decode($this->getClient($app)->DescribeCaptchaMiniRiskResult($request)->toJsonString(), true) ?? [];

        if (empty($response['CaptchaCode'])) {
            throw new Exception('API 调用失败(empty response)');
        }

        if ($response['CaptchaCode'] !== self::VALIDATED_OK) {
            throw new InvalidTicketException($ticket, $response);
        }

        return $response;
    }

    protected function getAppConfig(?string $app = null): array
    {
        return $this->config[$app ?? $this->defaultApp] ?? [];
    }
}
