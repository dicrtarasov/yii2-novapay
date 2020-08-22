<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 23.08.20 01:31:52
 */

declare(strict_types = 1);
namespace dicr\novapay;

use yii\base\BaseObject;

/**
 * Информация о доставке.
 *
 * @property array $data данные JSON.
 */
class Delivery extends BaseObject
{
    /** @var float объем * вес (minimum 0.0004) */
    public $volumeWeight;

    /** @var float вес (minimum 0.1) */
    public $weight;

    /** @var string ID НоваПошта города получателя */
    public $recipientCity;

    /** @var string ID отделения НоваПошта */
    public $recipientWarehouse;

    /**
     * Данные JSON.
     *
     * @return array
     */
    public function getData(): array
    {
        return array_filter([
            'volume_weight' => isset($this->volumeWeight) ? (float)$this->volumeWeight : null,
            'weight' => isset($this->weight) ? (float)$this->weight : null,
            'recipient_city' => isset($this->recipientCity) ? (string)$this->recipientCity : null,
            'recipient_warehouse' => isset($this->recipientWarehouse) ? (string)$this->recipientWarehouse : null
        ], static function ($val) {
            return $val !== null && $val !== '' && $val !== [];
        });
    }

    /**
     * Установить данные из JSON.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        if (isset($data['volume_weight'])) {
            $this->volumeWeight = (float)$data['volume_weight'];
        }

        if (isset($data['weight'])) {
            $this->weight = (float)$data['weight'];
        }

        if (isset($data['recipient_city'])) {
            $this->recipientCity = (string)$data['recipient_city'];
        }

        if (isset($data['recipient_warehouse'])) {
            $this->recipientWarehouse = (string)$data['recipient_warehouse'];
        }

        return $this;
    }
}
