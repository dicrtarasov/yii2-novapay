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
use yii\helpers\Json;

/**
 * Подтверждение отправки товара.
 *
 * Confirm hold secure delivery session by seller, results in express waybill number return.
 */
class ConfirmDeliveryHoldRequest extends NovaPayRequest
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
    protected function func(): string
    {
        return 'confirm-delivery-hold';
    }

    /**
     * @inheritDoc
     */
    protected function data(): array
    {
        return [
            'session_id' => $this->sessionId
        ];
    }

    /**
     * Отправляет запрос.
     *
     * @return string номер экспресс-накладной.
     * @throws Exception
     */
    public function send(): string
    {
        $data = parent::send();

        $waybill = (string)($data['express_waybill'] ?? '');
        if ($waybill === '') {
            throw new Exception('Не получен номер экспресс-накладной: ' . Json::encode($data));
        }

        return $waybill;
    }
}
