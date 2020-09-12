<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);

namespace dicr\novapay;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\httpclient\Client;
use yii\web\Application;
use yii\web\JsonParser;

use function array_merge;
use function is_callable;

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

    /** @var array конфиг HTTP-клиента */
    public $httpClientConfig = [];

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
     * @throws InvalidConfigException
     */
    public function getHttpClient(): Client
    {
        if (! isset($this->_httpClient)) {
            $this->_httpClient = Yii::createObject(array_merge([
                'class' => Client::class,
                'baseUrl' => $this->url,
            ], $this->httpClientConfig ?: []));
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
}
