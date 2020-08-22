<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 23.08.20 01:38:07
 */

declare(strict_types = 1);
namespace dicr\novapay;

use yii\base\BaseObject;

/**
 * Запрос от сервера NovaPay с уведомлением о статусе платежа.
 *
 * @property-write array $data
 */
class CallbackRequest extends BaseObject
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
     * Установить данные JSON.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->sessionId = (string)$data['id'];
        $this->status = (string)$data['status'];

        if (isset($data['metadata'])) {
            $this->metadata = (array)$data['metadata'];
        }

        if (isset($data['client_first_name'])) {
            $this->firstName = (string)$data['client_first_name'];
        }

        if (isset($data['client_last_name'])) {
            $this->lastName = (string)$data['client_last_name'];
        }

        if (isset($data['client_patronymic'])) {
            $this->patronymic = (string)$data['client_patronymic'];
        }

        if (isset($data['client_phone'])) {
            $this->phone = (string)$data['client_phone'];
        }

        if (isset($data['external_id'])) {
            $this->externalId = (string)$data['external_id'];
        }

        if (isset($data['delivery'])) {
            $this->delivery = new Delivery([
                'data' => $data['delivery']
            ]);
        }

        if (isset($data['products'])) {
            $this->products = array_map(static function (array $data) {
                return new Product(['data' => $data]);
            }, $data['products']);
        }

        if (isset($data['delivery_amount'])) {
            $this->deliveryAmount = (float)$data['delivery_amount'];
        }

        if (isset($data['delivery_status_code'])) {
            $this->deliveryStatusCode = (int)$data['delivery_status_code'];
        }

        if (isset($data['delivery_status_text'])) {
            $this->deliveryStatusText = (string)$data['delivery_status_text'];
        }

        return $this;
    }
}
