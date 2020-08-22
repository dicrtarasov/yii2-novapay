<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 23.08.20 01:40:50
 */

declare(strict_types = 1);

namespace dicr\novapay\request;

use dicr\novapay\Delivery;
use dicr\novapay\NovaPayRequest;
use dicr\novapay\Product;
use yii\base\Exception;
use yii\helpers\Json;

use function array_map;

/**
 * Add payment to created session and optionally initialize its processing.
 */
class PaymentRequest extends NovaPayRequest
{
    /** @var string payment session id */
    public $sessionId;

    /** @var ?string optional parameter indicating order id in merchant system (for registries) */
    public $externalId;

    /** @var float сумма платежа */
    public $amount;

    /** @var ?array optional payment purpose description */
    public $products;

    /**
     * @var ?bool optional parameter indicating two-steps payment (hold and then confirm).
     * Default to false, always true if delivery params are used.
     */
    public $useHold;

    /** @var ?Delivery optional object holding data about delivered package */
    public $delivery;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['sessionId', 'trim'],
            ['sessionId', 'required'],

            ['externalId', 'trim'],
            ['externalId', 'default'],

            ['amount', 'required'],
            ['amount', 'number', 'min' => 0.01],
            ['amount', 'filter', 'filter' => 'floatval'],

            ['products', 'default'],
            ['products', 'each' => function (
                $attribute,
                /* @noinspection PhpUnusedParameterInspection */ $params,
                /* @noinspection PhpUnusedParameterInspection */ $validator,
                $current
            ) {
                if (! $current instanceof Product) {
                    $this->addError($attribute, 'Товар должен быть типом Product');
                }
            }],

            ['useHold', 'default'],
            ['useHold', 'boolean'],
            ['useHold', 'filter', 'filter' => 'boolval', 'skipOnEmpty' => true],

            ['delivery', 'default'],
            ['delivery', function ($attribute) {
                if (! $this->delivery instanceof Delivery) {
                    $this->addError($attribute, 'Некорректный тип информации о доставке');
                }
            }]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function func(): string
    {
        return 'payment';
    }

    /**
     * @inheritDoc
     */
    protected function data(): array
    {
        return [
            'session_id' => $this->sessionId,
            'external_id' => $this->externalId,
            'amount' => $this->amount,
            'products' => array_map(static function (Product $prod) {
                return $prod->data;
            }, $this->products ?: []) ?: null,
            'use_hold' => $this->useHold,
            'delivery' => $this->delivery ? $this->delivery->data : null
        ];
    }

    /**
     * Отправляет запрос.
     *
     * @return string URL для переадресации на оплату (if start_process parameter is true)
     * @throws Exception
     */
    public function send(): string
    {
        $data = parent::send();

        $url = (string)($data['url'] ?? '');
        if ($url === '') {
            throw new Exception('Не получен url для оплаты: ' . Json::encode($data));
        }

        return $url;
    }
}
