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

use function array_merge;
use function is_array;

/**
 * Creates payment session.
 */
class SessionRequest extends NovaPayRequest
{
    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string отчество */
    public $patronymic;

    /** @var string phone in international format */
    public $phone;

    /** @var ?string optional email address to send a payment recipe to (can be edited by payer) */
    public $email;

    /** @var ?array any data one needs to be returned in post-backs */
    public $metadata;

    /**
     * @var ?string url for receiving session status post-backs (server-server).
     * Если не задан, то модуль устанавливает свой обработчик.
     * @see NovaPayModule::callback
     */
    public $callbackUrl;

    /** @var ?string optional url for button “return to the shop” on payment status page */
    public $successUrl;

    /** @var ?string optional url for button “return to the shop” on payment status page */
    public $failUrl;

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return array_merge(parent::attributeFields(), [
            'firstName' => 'client_first_name',
            'lastName' => 'client_last_name',
            'patronymic' => 'client_patronymic',
            'phone' => 'client_phone',
            'email' => 'client_email',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['firstName', 'trim'],
            ['firstName', 'required'],

            ['lastName', 'trim'],
            ['lastName', 'required'],

            ['patronymic', 'trim'],
            ['patronymic', 'required'],

            ['phone', 'trim'],
            ['phone', 'required'],

            ['email', 'trim'],
            ['email', 'default'],
            ['email', 'email'],

            ['metadata', 'default'],
            ['metadata', function (string $attribute) {
                if (! is_array($this->metadata)) {
                    $this->addError($attribute, 'Метаданные должны быть массивом');
                }
            }],

            [['callbackUrl', 'successUrl', 'failUrl'], 'trim'],
            [['callbackUrl', 'successUrl', 'failUrl'], 'default'],
            [['callbackUrl', 'successUrl', 'failUrl'], 'url']
        ];
    }

    /**
     * @inheritDoc
     */
    protected function func() : string
    {
        return 'session';
    }

    /**
     * Отправляет запрос.
     *
     * @return string ID сессии
     * @throws Exception
     */
    public function send() : string
    {
        $data = parent::send();

        $sessionId = (string)($data['id'] ?? '');
        if ($sessionId === '') {
            throw new Exception('Не получены данные сессии: ' . Json::encode($data));
        }

        return $sessionId;
    }
}
