<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);

namespace dicr\novapay\request;

use dicr\novapay\NovaPay;
use dicr\novapay\NovaPayRequest;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Получить статус сессии.
 *
 * Return current session status.
 */
class GetStatusRequest extends NovaPayRequest implements NovaPay
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
        return 'get-status';
    }

    /**
     * Отправляет запрос.
     *
     * @return string статус сессии.
     * @throws Exception
     */
    public function send() : string
    {
        $data = parent::send();

        $status = (string)($data['status'] ?? '');
        if ($status === '') {
            throw new Exception('Не получен статус сессии: ' . Json::encode($data));
        }

        return $status;
    }
}
