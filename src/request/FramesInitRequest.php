<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);

namespace dicr\novapay\request;

use dicr\novapay\Delivery;
use dicr\novapay\NovaPayRequest;
use dicr\novapay\Product;
use yii\base\Exception;
use yii\helpers\Json;

use function array_map;
use function is_array;

/**
 * Payments via precoded frames.
 * Комбинированный запрос на открытие сессии и создание платежа (session + payment)
 * для "Безопасной покупки" c удержанием денег до доставки посылки получателю.
 */
class FramesInitRequest extends NovaPayRequest
{
    /** @var ?string */
    public $firstName;

    /** @var ?string */
    public $lastName;

    /** @var ?string отчество */
    public $patronymic;

    /** @var ?string phone in international format */
    public $phone;

    /** @var ?string optional email address to send a payment recipe to (can be edited by payer) */
    public $email;

    /** @var ?array any data one needs to be returned in post-backs */
    public $metadata;

    /**
     * @var ?string url for receiving session status post-backs (server-server).
     * Если не задан, то модуль устанавливает свой обработчик.
     * @see NovaPayModule::callback
     */
    public $callbackUrl;

    /** @var ?string optional url for button “return to the shop” on payment status page */
    public $successUrl;

    /** @var ?string optional url for button “return to the shop” on payment status page */
    public $failUrl;

    /** @var ?string optional parameter indicating order id in merchant system (for registries) */
    public $externalId;

    /** @var float сумма платежа */
    public $amount;

    /** @var ?array optional payment purpose description */
    public $products;

    /** @var ?Delivery optional object holding data about delivered package */
    public $delivery;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['firstName', 'trim'],
            ['firstName', 'default'],

            ['lastName', 'trim'],
            ['lastName', 'default'],

            ['patronymic', 'trim'],
            ['patronymic', 'default'],

            ['phone', 'trim'],
            ['phone', 'default'],

            ['email', 'trim'],
            ['email', 'default'],
            ['email', 'email'],

            ['metadata', 'default'],
            ['metadata', function (string $attribute) {
                if (! is_array($this->metadata)) {
                    $this->addError($attribute, 'Метаданные должны быть массивом');
                }
            }],

            [['callbackUrl', 'successUrl', 'failUrl'], 'trim'],
            [['callbackUrl', 'successUrl', 'failUrl'], 'default'],
            [['callbackUrl', 'successUrl', 'failUrl'], 'url'],

            ['externalId', 'trim'],
            ['externalId', 'default'],

            ['amount', 'required'],
            ['amount', 'number', 'min' => 0.01],
            ['amount', 'filter', 'filter' => 'floatval'],

            ['products', 'default'],
            ['products', function (string $attribute) {
                if (is_array($this->products)) {
                    foreach ($this->products as $prod) {
                        if (! $prod instanceof Product) {
                            $this->addError($attribute, 'Товар должен быть элементом Product');
                        }
                    }
                } else {
                    $this->addError($attribute, 'Товары должны быть массивом');
                }
            }],

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
        return 'frames/init';
    }

    /**
     * @inheritDoc
     */
    protected function data(): array
    {
        return [
            'client_first_name' => $this->firstName,
            'client_last_name' => $this->lastName,
            'client_patronymic' => $this->patronymic,
            'client_phone' => $this->phone,
            'client_email' => $this->email,
            'metadata' => $this->metadata,
            'callback_url' => $this->callbackUrl,
            'success_url' => $this->successUrl,
            'fail_url' => $this->failUrl,
            'external_id' => $this->externalId,
            'amount' => $this->amount,
            'products' => array_map(static function (Product $prod) {
                return $prod->data;
            }, $this->products ?: []) ?: null,
            'delivery' => $this->delivery ? $this->delivery->data : null
        ];
    }

    /**
     * Отправляет запрос.
     *
     * @return string[] id платежной сессии и url для переадресации на оплату.
     * @throws Exception
     */
    public function send(): array
    {
        $data = parent::send();

        $sessionId = (string)($data['session_id'] ?? '');
        if ($sessionId === '') {
            throw new Exception('Не получен ID сессии: ' . Json::encode($data));
        }

        $url = (string)($data['url'] ?? '');
        if ($url === '') {
            throw new Exception('Не получен url для оплаты: ' . Json::encode($data));
        }

        return [
            'sessionId' => $sessionId,
            'url' => $url
        ];
    }
}
