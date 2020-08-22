<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);
namespace dicr\novapay;

use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\httpclient\Client;

use function base64_encode;
use function implode;
use function openssl_error_string;
use function openssl_pkey_free;
use function openssl_pkey_get_private;
use function openssl_sign;

/**
 * Абстрактный запрос Novapay.
 */
abstract class NovaPayRequest extends Model
{
    /** @var NovaPayModule */
    private $_module;

    /**
     * NovapayRequest constructor.
     *
     * @param NovaPayModule $module
     * @param array $config
     */
    public function __construct(NovaPayModule $module, array $config = [])
    {
        $this->_module = $module;

        parent::__construct($config);
    }

    /**
     * Функция api
     *
     * @return string
     */
    abstract protected function func(): string;

    /**
     * Данные для JSON.
     *
     * @return array
     */
    protected function data(): array
    {
        return $this->attributes;
    }

    /**
     * Возвращает ошибки SSL.
     *
     * @return string[]
     */
    private static function opensslErrors(): array
    {
        $errors = [];

        while ($error = openssl_error_string()) {
            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Создает подпись данным.
     *
     * @param string $json данные в строке json
     * @return string
     * @throws Exception
     */
    private function createSign(string $json): string
    {
        $pk = openssl_pkey_get_private($this->_module->clientKey);
        if ($pk === false) {
            throw new Exception('Некорректный приватный ключ клиента');
        }

        try {
            $signature = '';

            if (openssl_sign($json, $signature, $pk) === false) {
                throw new Exception('Ошибка создания сигнатуры: ' .
                    implode(";\n", self::opensslErrors())
                );
            }

            return base64_encode($signature);
        } finally {
            openssl_pkey_free($pk);
        }
    }

    /**
     * Отправляет запрос NovaPay.
     *
     * @return mixed ответ сервера
     * @throws Exception
     */
    public function send()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        // фильтруем данные
        $data = array_filter($this->data(), static function ($val) {
            return $val !== null && $val !== '' && $val !== [];
        });

        // добавляем merchant_id из модуля
        if (empty($data['merchant_id'])) {
            $data['merchant_id'] = $this->_module->merchantId;
        }

        // добавляем callback_url модуля (только для режима web-приложения)
        if (! isset($data['callback_url'])) {
            $data['callback_url'] = Url::to($this->_module->uniqueId . '/callback', true);
        }

        // кодируем данные в JSON
        $json = Json::encode($data);

        // HTTP-запрос
        $request = $this->_module->httpClient->post($this->func(), $json, [
            'Content-Type' => 'application/json',
            'Content-Length' => StringHelper::byteLength($json),
            'X-Sign' => $this->createSign($json)
        ]);

        Yii::debug('Отправка запроса: ' . $request->toString());

        // отправляем запрос
        $response = $request->send();
        if (! $response->isOk) {
            throw new Exception('Ошибка: ' . $response->content);
        }

        // возвращаем ответ
        $response->format = Client::FORMAT_JSON;

        return $response->data;
    }
}
