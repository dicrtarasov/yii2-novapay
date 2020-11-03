<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 03.11.20 20:37:19
 */

declare(strict_types = 1);
namespace dicr\novapay;

use dicr\json\JsonEntity;

/**
 * Информация о товаре.
 */
class Product extends JsonEntity
{
    /** @var string payment position title */
    public $description;

    /** @var int payment position count */
    public $count;

    /** @var float payment position total price */
    public $price;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['description', 'trim'],
            ['description', 'required'],

            ['price', 'trim'],
            ['price', 'required'],
            ['price', 'number', 'min' => 0.01],
            ['price', 'filter', 'filter' => 'floatval'],

            ['count', 'trim'],
            ['count', 'required'],
            ['count', 'integer', 'min' => 1],
            ['count', 'filter', 'filter' => 'intval']
        ];
    }
}
