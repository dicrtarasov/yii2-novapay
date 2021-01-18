<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 18.01.21 20:11:25
 */

declare(strict_types = 1);

namespace dicr\novapay\request;

use dicr\json\EntityValidator;
use dicr\novapay\Delivery;
use dicr\novapay\NovaPayRequest;
use dicr\novapay\Product;
use dicr\validate\PhoneValidator;
use yii\base\Exception;

use function is_array;
use function preg_replace;

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

    /** @var Delivery optional object holding data about delivered package */
    public $delivery;

    /**
     * @inheritDoc
     */
    public function attributeFields(): array
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
    public function attributeEntities(): array
    {
        return [
            'products' => [Product::class],
            'delivery' => Delivery::class
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
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
            ['phone', PhoneValidator::class, 'country' => 38, 'region' => 44, 'formatOnValidate' => true,
                'skipOnEmpty' => true],
            ['phone', 'filter', 'filter' => fn($val): string => '+' .
                (int)preg_replace('~[\D]+~', '', (string)$val), 'skipOnEmpty' => true],

            ['email', 'trim'],
            ['email', 'default'],
            ['email', 'email'],

            ['metadata', 'default'],
            ['metadata', function(string $attribute) {
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
            ['products', EntityValidator::class, 'class' => [Product::class]],

            ['delivery', 'required'],
            ['delivery', EntityValidator::class, 'class' => EntityValidator::class]
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
     * Отправляет запрос.
     *
     * @return FramesInitResponse
     * @throws Exception
     */
    public function send(): FramesInitResponse
    {
        return new FramesInitResponse([
            'json' => parent::send()
        ]);
    }
}
