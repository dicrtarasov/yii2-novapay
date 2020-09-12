<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:57:27
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

    /** @var Product[]|null optional payment purpose description */
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
            ['sessionId', 'trim'],
            ['sessionId', 'required'],

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

            ['useHold', 'default'],
            ['useHold', 'boolean'],
            ['useHold', 'filter', 'filter' => 'boolval', 'skipOnEmpty' => true],

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
        return 'payment';
    }

    /**
     * Отправляет запрос.
     *
     * @return string URL для переадресации на оплату (if start_process parameter is true)
     * @throws Exception
     */
    public function send() : string
    {
        $data = parent::send();

        $url = (string)($data['url'] ?? '');
        if ($url === '') {
            throw new Exception('Не получен URL для оплаты: ' . Json::encode($data));
        }

        return $url;
    }
}
