<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 03.11.20 20:14:48
 */

declare(strict_types = 1);

namespace dicr\novapay;

use RuntimeException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\httpclient\Client;
use yii\web\Application;
use yii\web\JsonParser;

use function is_callable;
use function ob_end_clean;
use function ob_get_level;

/**
 * Модуль Novapay.
 *
 * @property-read Client $httpClient
 */
class NovaPayModule extends Module implements NovaPay
{
    /** @var string URL API */
    public $url = self::API_URL;

    /** @var string merchant_id */
    public $merchantId;

    /** @var string публичный ключ NovaPay (file://путь к файлу или строка в формате PEM) */
    public $serverKey = self::SERVER_PUB;

    /** @var string приватный ключ клиента (file://путь к файлу или строка в формате PEM) */
    public $clientKey;

    /** @var ?callable function(CallbackRequest $request, NovaPayModule $module) обработчик callback */
    public $callback;

    /** @inheritDoc */
    public $controllerNamespace = __NAMESPACE__;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        parent::init();

        if (empty($this->url)) {
            throw new InvalidConfigException('url');
        }

        if (empty($this->merchantId)) {
            throw new InvalidConfigException('merchantId');
        }

        if (empty($this->serverKey)) {
            throw new InvalidConfigException('serverKey');
        }

        if (empty($this->clientKey)) {
            throw new InvalidConfigException('clientKey');
        }

        // callback
        if (! empty($this->callback) && ! is_callable($this->callback)) {
            throw new InvalidConfigException('callback');
        }

        // устанавливаем парсер JSON-запросов
        if (Yii::$app instanceof Application) {
            Yii::$app->request->parsers['application/json'] = JsonParser::class;
        }
    }

    /** @var Client */
    private $_httpClient;

    /**
     * HTTP-клиент.
     *
     * @return Client
     */
    public function getHttpClient() : Client
    {
        if ($this->_httpClient === null) {
            $this->_httpClient = new Client();
            $this->_httpClient->baseUrl = $this->url;
        }

        return $this->_httpClient;
    }

    /**
     * Создает запрос.
     *
     * @param array $config
     * @return NovaPayRequest
     * @throws InvalidConfigException
     */
    public function createRequest(array $config) : NovaPayRequest
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject($config, [$this]);
    }

    /**
     * Переадресация на страницу оплаты.
     *
     * @param string $url платежный URL
     */
    public static function redirectCheckout(string $url) : void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        try {
            Yii::$app->end(0, Yii::$app->response->redirect($url));
        } catch (Throwable $ex) {
            throw new RuntimeException('Неизвестная ошибка');
        }
    }
}
