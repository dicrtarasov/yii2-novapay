<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 23.08.20 01:34:09
 */

declare(strict_types = 1);
namespace dicr\novapay;

use yii\base\BaseObject;

/**
 * Информация о товаре.
 *
 * @property array $data
 */
class Product extends BaseObject
{
    /** @var string payment position title */
    public $description;

    /** @var string payment position count */
    public $count;

    /** @var string payment position total price */
    public $price;

    /**
     * Данные JSON.
     *
     * @return string[]
     */
    public function getData(): array
    {
        return [
            'description' => $this->description,
            'count' => $this->count,
            'price' => $this->price
        ];
    }

    /**
     * Установить данные JSON.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        if (isset($data['description'])) {
            $this->description = (string)$data['description'];
        }

        if (isset($data['count'])) {
            $this->count = (string)$data['count'];
        }

        if (isset($data['price'])) {
            $this->price = (string)$data['price'];
        }

        return $this;
    }
}
