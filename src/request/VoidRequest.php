<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);

namespace dicr\novapay\request;

use dicr\novapay\NovaPayRequest;
use yii\base\Exception;

/**
 * Отмена платежей сессии.
 * Void paid or holding payments (paid ones can be voided only till 23:59).
 */
class VoidRequest extends NovaPayRequest
{
    /** @var string payment session id */
    public $sessionId;

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['sessionId', 'trim'],
            ['sessionId', 'required']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function func() : string
    {
        return 'void';
    }

    /**
     * Отправляет запрос.
     *
     * @throws Exception
     */
    public function send() : void
    {
        parent::send();
    }
}
