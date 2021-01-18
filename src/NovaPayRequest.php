<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 18.01.21 20:10:02
 */

declare(strict_types = 1);
namespace dicr\novapay;

use dicr\json\JsonEntity;
use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\httpclient\Client;

use function base64_encode;
use function implode;
use function openssl_error_string;
use function openssl_pkey_get_private;
use function openssl_sign;

/**
 * Абстрактный запрос Novapay.
 */
abstract class NovaPayRequest extends JsonEntity
{
    /** @var NovaPayModule */
    private $module;

    /**
     * NovapayRequest constructor.
     *
     * @param NovaPayModule $module
     * @param array $config
     */
    public function __construct(NovaPayModule $module, array $config = [])
    {
        $this->module = $module;

        parent::__construct($config);
    }

    /**
     * Функция api
     *
     * @return string
     */
    abstract protected function func(): string;

    /**
     * Возвращает ошибки SSL.
     *
     * @return string[]
     */
    private static function opensslErrors(): array
    {
        $errors = [];

        while (true) {
            $error = openssl_error_string();
            if (empty($error)) {
                break;
            }

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
        $pk = openssl_pkey_get_private($this->module->clientKey);
        if ($pk === false) {
            throw new Exception('Некорректный приватный ключ клиента');
        }

        $signature = '';
        if (openssl_sign($json, $signature, $pk) === false) {
            throw new Exception('Ошибка создания сигнатуры: ' .
                implode(";\n", self::opensslErrors())
            );
        }

        return base64_encode($signature);
    }

    /**
     * Отправляет запрос NovaPay.
     *
     * @return mixed ответ сервера (переопределяется в наследнике)
     * @throws Exception
     */
    public function send()
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        // фильтруем данные
        $data = array_filter(array_merge([
            'merchant_id' => $this->module->merchantId,
            'callback_url' => Url::to('/' . $this->module->uniqueId . '/callback', true)
        ], $this->getJson()), static fn($val): bool => $val !== null && $val !== '' && $val !== []);

        // кодируем данные в JSON
        $json = Json::encode($data);

        // HTTP-запрос
        $request = $this->module->httpClient->post($this->func(), $json, [
            'Content-Type' => 'application/json',
            'Content-Length' => StringHelper::byteLength($json),
            'X-Sign' => $this->createSign($json)
        ]);

        // отправляем запрос
        Yii::debug('Запрос: ' . $request->toString(), __METHOD__);
        $response = $request->send();
        Yii::debug('Ответ: ' . $response->toString(), __METHOD__);

        $response->format = Client::FORMAT_JSON;
        if (! $response->isOk) {
            throw new Exception(
                ! empty($response->data['errors']) ? Json::encode($response->data['errors']) :
                    $response->toString()
            );
        }

        // возвращаем ответ
        return $response->data;
    }
}
