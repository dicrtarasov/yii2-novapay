<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);
namespace dicr\tests;

use dicr\novapay\Delivery;
use dicr\novapay\NovaPay;
use dicr\novapay\NovaPayModule;
use dicr\novapay\Product;
use dicr\novapay\request\FramesInitRequest;
use dicr\novapay\request\GetStatusRequest;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class NovapayModuleTest
 */
class NovapayModuleTest extends TestCase
{
    /**
     * Модуль
     *
     * @return NovaPayModule
     */
    private static function module(): NovaPayModule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('novapay');
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testCreateRequest()
    {
        // запрос на создание платежа
        $request = self::module()->createRequest([
            'class' => FramesInitRequest::class,
            'amount' => 55.55,
            'products' => [
                new Product([
                    'description' => 'Товар1',
                    'price' => 11.11,
                    'count' => 1
                ]),
                new Product([
                    'description' => 'Товар2',
                    'price' => 22.22,
                    'count' => 2
                ])
            ],
            'delivery' => new Delivery([
                'volumeWeight' => 0.01,
                'weight' => 0.1
            ])
        ]);

        $ret = $request->send();

        self::assertIsArray($ret);
        self::assertArrayHasKey('sessionId', $ret);
        self::assertNotEmpty($ret['sessionId']);
        self::assertArrayHasKey('url', $ret);
        self::assertNotEmpty($ret['url']);

        $sessionId = $ret['sessionId'];
        $url = $ret['url'];

        echo 'sessionId: ' . $sessionId . "\n";
        echo 'url: ' . $url . "\n";

        // запрос на проверку состояние платежной сессии
        $request = self::module()->createRequest([
            'class' => GetStatusRequest::class,
            'sessionId' => $ret['sessionId']
        ]);

        $status = $request->send();

        self::assertContains($status, [
            NovaPay::STATUS_PRECREATED, NovaPay::STATUS_CREATED
        ]);

        echo 'status: ' . $status;
    }
}
