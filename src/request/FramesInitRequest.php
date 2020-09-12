<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:56:52
 */

declare(strict_types = 1);

namespace dicr\novapay\request;

use dicr\novapay\Delivery;
use dicr\novapay\NovaPayRequest;
use dicr\novapay\Product;
use dicr\validate\ValidateException;
use yii\base\Exception;
use yii\helpers\Json;

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

    /** @var Product[]|null optional payment purpose description */
    public $products;

    /** @var ?Delivery optional object holding data about delivered package */
    public $delivery;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'firstName' => 'client_first_name',
            'lastName' => 'client_last_name',
            'patronymic' => 'client_patronymic',
            'phone' => 'client_phone',
            'email' => 'client_email',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeEntities() : array
    {
        return [
            'products' => [Product::class],
            'delivery' => Delivery::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
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
                if (empty($this->metadata)) {
                    $this->metadata = null;
                } elseif (! is_array($this->metadata)) {
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
                $products = null;

                if (! empty($this->products)) {
                    if (is_array($this->products)) {
                        foreach ($this->products as $i => $prod) {
                            if (is_array($prod)) {
                                $prod = new Product($prod);
                            }

                            if ($prod instanceof Product) {
                                if (! $prod->validate()) {
                                    $this->addError($attribute, (new ValidateException($prod))->getMessage());
                                }
                            } else {
                                $this->addError($attribute, 'Товар должен быть элементом Product');
                            }

                            $products[] = $prod;
                        }
                    } else {
                        $this->addError($attribute, 'Товары должны быть массивом');
                    }
                }

                $this->products = $products;
            }],

            ['delivery', 'default'],
            ['delivery', function ($attribute) {
                if (empty($this->delivery)) {
                    $this->delivery = null;
                } else {
                    if (is_array($this->delivery)) {
                        $this->delivery = new Delivery($this->delivery);
                    }

                    if ($this->delivery instanceof Delivery) {
                        if (! $this->delivery->validate()) {
                            $this->addError($attribute, (new ValidateException($this->delivery))->getMessage());
                        }
                    } else {
                        $this->addError($attribute, 'Некорректный тип информации о доставке');
                    }
                }
            }]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function func() : string
    {
        return 'frames/init';
    }

    /**
     * Отправляет запрос.
     *
     * @return string[] id платежной сессии и url для переадресации на оплату.
     * @throws Exception
     */
    public function send() : array
    {
        $data = parent::send();

        $sessionId = (string)($data['session_id'] ?? '');
        if ($sessionId === '') {
            throw new Exception('Не получен ID сессии: ' . Json::encode($data));
        }

        $url = (string)($data['url'] ?? '');
        if ($url === '') {
            throw new Exception('Не получен URL для оплаты: ' . Json::encode($data));
        }

        return [
            'sessionId' => $sessionId,
            'url' => $url
        ];
    }
}
