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
 * Начать обработку платежной сессии.
 * Используется для начала обработки отложенных платежных сессий (start_process = false).
 */
class ProcessRequest extends NovaPayRequest
{
    /** @var string ID ранее созданной сессии */
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
    protected function func(): string
    {
        return 'process';
    }

    /**
     * Отправляет запрос.
     *
     * @throws Exception
     */
    public function send(): void
    {
        parent::send();
    }
}
