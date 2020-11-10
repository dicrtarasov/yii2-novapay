# NovaPay API клиент для Yii2

API: see `doc`

## Конфигурация модуля

```php
'modules' => [
    'novapay' => [
        'class' => dicr\novapay\NovaPayModule::class,
        'merchantId' => 'ваш_merchant_id',
        // приватный ключ клиента указывается либо как путь к файлу (через file://, либо PEM-код)
        'clientKey' => 'file://' . __DIR__ . '/client.key'
    ]
];
```

## Использование

```php
use dicr\novapay\NovaPayModule;
use dicr\novapay\request\FramesInitRequest;
use dicr\novapay\request\FramesInitResponse;use dicr\novapay\request\GetStatusRequest;

/** @var NovaPayModule $novaPay получаем модуль */
$novaPay = Yii::$app->getModule('novapay');

// запрос на создание платежа
$request = $novaPay->createRequest([
    'class' => FramesInitRequest::class,
    'amount' => 55.55,
    'products' => [
        [
            'description' => 'Товар1',
            'price' => 11.11,
            'count' => 1
        ],
        [
            'description' => 'Товар2',
            'price' => 22.22,
            'count' => 2
        ]
    ],
    'delivery' => [
        'volumeWeight' => 0.01,
        'weight' => 0.1
    ]
]);

/** @var FramesInitResponse $ret отправляем запрос */
$ret = $request->send();

echo 'Адрес для переадресации: ' . $ret->url . "\n";

// запрос на проверку состояние платежной сессии
$request = $novaPay->createRequest([
    'class' => GetStatusRequest::class,
    'sessionId' => $ret->sessionId
]);

$status = $request->send();

echo 'Статус: ' . $status . "\n";
```
