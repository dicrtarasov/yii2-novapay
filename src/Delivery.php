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

/**
 * Информация о доставке.
 */
class Delivery extends JsonEntity
{
    /** @var float объем * вес (minimum 0.01) */
    public $volumeWeight;

    /** @var float вес (minimum 0.1) */
    public $weight;

    /** @var ?string ID НоваПошта города получателя */
    public $recipientCity;

    /** @var ?string ID отделения НоваПошта */
    public $recipientWarehouse;

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['volumeWeight', 'number', 'min' => 0.01],
            ['volumeWeight', 'filter', 'filter' => 'floatval'],

            ['weight', 'number', 'min' => 0.1],
            ['weight', 'filter', 'filter' => 'floatval'],

            ['recipientCity', 'trim'],
            ['recipientCity', 'default'],

            ['recipientWarehouse', 'trim'],
            ['recipientWarehouse', 'default']
        ];
    }
}
