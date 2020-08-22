<?php

/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 23.08.20 02:07:43
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
    'basePath' => __DIR__,
    'components' => [
        'cache' => yii\caching\ArrayCache::class,
        'urlManager' => [
            'hostInfo' => 'https://localhost'
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
    ]
]);
