<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 03.11.20 20:37:57
 */

declare(strict_types = 1);

/** среда разработки */
defined('YII_ENV') || define('YII_ENV', 'dev');

/** режим отладки */
defined('YII_DEBUG') || define('YII_DEBUG', true);

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

/** @noinspection PhpUnhandledExceptionInspection */
new yii\console\Application([
    'id' => 'test',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => [
            'class' => yii\caching\FileCache::class
        ],
        'log' => [
            'targets' => [
                ['class' => yii\log\FileTarget::class]
            ]
        ],
        'urlManager' => [
            'hostInfo' => 'https://dicr.org'
        ]
    ],
    'modules' => [
        'novapay' => [
            'class' => dicr\novapay\NovaPayModule::class,
            'url' => dicr\novapay\NovaPay::TEST_URL,
            'merchantId' => dicr\novapay\NovaPay::TEST_MERCHANT_ID,
            'serverKey' => dicr\novapay\NovaPay::TEST_SERVER_PUB,
            'clientKey' => dicr\novapay\NovaPay::TEST_CLIENT_KEY
        ]
    ],
    'bootstrap' => ['log']
]);
