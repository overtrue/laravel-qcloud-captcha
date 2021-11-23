Laravel Qcloud Captcha
---

[![CI](https://github.com/overtrue/laravel-qcloud-captcha/actions/workflows/ci.yml/badge.svg)](https://github.com/overtrue/laravel-qcloud-captcha/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/overtrue/laravel-qcloud-captcha/v/stable.svg)](https://packagist.org/packages/overtrue/laravel-qcloud-captcha)
[![Latest Unstable Version](https://poser.pugx.org/overtrue/laravel-qcloud-captcha/v/unstable.svg)](https://packagist.org/packages/overtrue/laravel-qcloud-captcha)
[![Total Downloads](https://poser.pugx.org/overtrue/laravel-qcloud-captcha/downloads)](https://packagist.org/packages/overtrue/laravel-qcloud-captcha)
[![License](https://poser.pugx.org/overtrue/laravel-qcloud-captcha/license)](https://packagist.org/packages/overtrue/laravel-qcloud-captcha)

腾讯云验证码是为网页、App 及小程序开发者提供的安全验证服务，目前网页及 App 支持以 Web 前端接入、App 端接入（iOS 和 Android）方式接入验证码服务，小程序开发者可以使用 小程序插件接入
方式接入验证码服务，基于腾讯多年的大数据积累和人工智能决策引擎，构建智能分级验证模型，最大程度保护业务安全的同时，提供更精细化的用户体验。

- :book: [官方 API](https://cloud.tencent.com/document/product/1110/36334)

[![Sponsor me](https://raw.githubusercontent.com/overtrue/overtrue/master/sponsor-me-button-s.svg)](https://github.com/sponsors/overtrue)

## Installing

```shell
$ composer require overtrue/laravel-qcloud-captcha -vvv
```

### Config

请在 `config/services.php` 中配置以下内容：

```php
    //...
    // 验证码服务
    'captcha' => [
        // 腾讯云 API 秘钥：SecretId，SecretKey
        // https://console.cloud.tencent.com/cam/capi
        'secret_id' => env('CAPTCHA_SECRET_ID'),
        'secret_key' => env('CAPTCHA_SECRET_KEY'),
        
        // 验证码服务秘钥：CaptchaAppId，AppSecretKey
        'app_id' => env('CAPTCHA_APP_ID'), 
        'app_secret_key' => env('CAPTCHA_APP_SECRET_KEY'),
        
        // 以下非必填
        'channel' =>  'web', // 渠道：web/miniapp，默认 web
        'endpoint' => env('CAPTCHA_ENDPOINT'), // 默认: captcha.tencentcloudapi.com
    ],
```

或者配置成多场景：

```php
    //...
    // 验证码服务
    'captcha' => [
        // 默认 app 名称
        'default' => 'login',
        
        // app 配置
        'apps' => [
            'login' => [
                'secret_id' => env('CAPTCHA_LOGIN_SECRET_ID'),
                'secret_key' => env('CAPTCHA_LOGIN_SECRET_KEY'),
                'app_id' => env('CAPTCHA_LOGIN_APP_ID'), 
                'app_secret_key' => env('CAPTCHA_LOGIN_APP_SECRET_KEY'),
            ],
            'register' => [
                'secret_id' => env('CAPTCHA_REGISTER_SECRET_ID'),
                'secret_key' => env('CAPTCHA_REGISTER_SECRET_KEY'),
                'app_id' => env('CAPTCHA_REGISTER_APP_ID'), 
                'app_secret_key' => env('CAPTCHA_REGISTER_APP_SECRET_KEY'),
            ],
            //...
        ],
    ],
```

## API

### 获取检查结果

调用对应 API 返回 ticket 校验结果，返回值结构请参考官方 API 文档。

> 接口请求频率限制：1000次/秒。

```php
use Overtrue\LaravelQcloudCaptcha\Captcha;

array Captcha::validate(string $ticket, ?string $app = null, ?string $nonce = null);
array Captcha::validateWebTicket(string $ticket, ?string $app = null, ?string $nonce = null);
array Captcha::validateMiniAppTicket(string $ticket, ?string $app = null);
```

验证失败将抛出以下异常：

- `Overtrue\LaravelQcloudCaptcha\InvalidTicketException`
  - `$ticket` - (string) 被检测的 ticket
  - `$response` - (array) API 原始返回值

## 使用表单校验规则

```php
$this->validate($request, [
	'ticket' => 'required|captcha:randstr',
	'avatar' => 'required|url|ims',
	'description' => 'required|tms:strict',
	'logo_url' => 'required|url|ims:logo',
]);
```

**规则参数**：

**Web** 类型验证码校验时需要传入回调时的 **前端回调函数返回的随机字符串** 对应的表单名称，例如请求后端时使用 `randstr` 作为表单名称提交随机字符串：

```php
// 请求内容
[
    'ticket' => 'xxxxxxxxx',
    'randstr' => 'xxxxxxxxx',
],

//1. 使用默认应用，并以请求中 `randstr` 作为随机字符串校验
'ticket' => 'required|captcha:randstr', 

//2. 使用 `login` 应用，并以请求中 `randstr` 作为随机字符串校验
'ticket' => 'required|captcha:login,randstr',
```

**小程序** 验证码没有随机字符串参数：

```php
// 请求内容
[
    'ticket' => 'xxxxxxxxx',
],

//1. 使用默认应用
'ticket' => 'required|captcha', 

//2. 使用 `login` 应用
'ticket' => 'required|captcha:login',
```

## :heart: Sponsor me 

[![Sponsor me](https://raw.githubusercontent.com/overtrue/overtrue/master/sponsor-me.svg)](https://github.com/sponsors/overtrue)

如果你喜欢我的项目并想支持它，[点击这里 :heart:](https://github.com/sponsors/overtrue)

## Project supported by JetBrains

Many thanks to Jetbrains for kindly providing a license for me to work on this and other open-source projects.

[![](https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg)](https://www.jetbrains.com/?from=https://github.com/overtrue)


## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/overtrue/laravel-package/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/overtrue/laravel-package/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any
new code contributions must be accompanied by unit tests where applicable._

## PHP 扩展包开发

> 想知道如何从零开始构建 PHP 扩展包？
>
> 请关注我的实战课程，我会在此课程中分享一些扩展开发经验 —— [《PHP 扩展包实战教程 - 从入门到发布》](https://learnku.com/courses/creating-package)

## License

MIT
