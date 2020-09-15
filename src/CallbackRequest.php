<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);
namespace dicr\novapay;

use dicr\helper\JsonEntity;

use function array_merge;

/**
 * Запрос от сервера NovaPay с уведомлением о статусе платежа.
 */
class CallbackRequest extends JsonEntity
{
    /** @var string платежная сессия */
    public $sessionId;

    /** @var string статус сессии (STATUS_*) */
    public $status;

    /** @var ?array метаданные, переданные во время создания сессии */
    public $metadata;

    /** @var ?string имя */
    public $firstName;

    /** @var ?string фамилия */
    public $lastName;

    /** @var ?string отчество */
    public $patronymic;

    /** @var ?string телефон */
    public $phone;

    /** @var ?string номер заказа магазина */
    public $externalId;

    /** @var ?Delivery информация о доставке */
    public $delivery;

    /** @var ?Product[] товары в чеке на оплату */
    public $products;

    /** @var ?float стоимость доставки */
    public $deliveryAmount;

    /**
     * @var ?int статус доставки
     * used if secure payment used. Statuses is taken from
     * @link https://devcenter.novaposhta.ua/docs/services/556eef34a0fe4f02049c664e/operations/55702cbba0fe4f0cf4fc53ee
     */
    public $deliveryStatusCode;

    /** @var ?string статус доставки в текстовом виде */
    public $deliveryStatusText;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'sessionId' => 'id',
            'firstName' => 'client_first_name',
            'lastName' => 'client_last_name',
            'patronymic' => 'client_patronymic',
            'phone' => 'client_phone'
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
}
