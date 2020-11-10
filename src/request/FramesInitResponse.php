<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 10.11.20 03:42:12
 */

declare(strict_types = 1);
namespace dicr\novapay\request;

use dicr\novapay\NovaPayResponse;

/**
 * Ответ FramesInit
 */
class FramesInitResponse extends NovaPayResponse
{
    /** @var string ID платежной сессии */
    public $sessionId;

    /** @var string URL переадресации на платежную страницу */
    public $url;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'sessionId' => 'session_id'
        ]);
    }
}
