<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 10.11.20 03:45:24
 */

declare(strict_types = 1);
namespace dicr\novapay;

use dicr\json\JsonEntity;

/**
 * Ответ NovaPay.
 */
abstract class NovaPayResponse extends JsonEntity
{
    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return [];
    }
}
