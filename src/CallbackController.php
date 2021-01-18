<?php
/*
 * @copyright 2019-2021 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 18.01.21 20:09:08
 */

declare(strict_types = 1);
namespace dicr\novapay;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

use function base64_decode;
use function call_user_func;
use function openssl_error_string;
use function openssl_pkey_get_public;
use function openssl_verify;

/**
 * Контроллер обработки оповещений о статусах платежей от NovaPay.
 *
 * @property-read NovaPayModule $module
 */
class CallbackController extends Controller
{
    /** @inheritDoc */
    public $enableCsrfValidation = false;

    /**
     * Обработка запроса от NovaPay.
     *
     * @throws BadRequestHttpException|ServerErrorHttpException
     */
    public function actionIndex(): void
    {
        if (! Yii::$app->request->isPost) {
            throw new BadRequestHttpException();
        }

        Yii::debug('Callback: ' . Yii::$app->request->rawBody, __METHOD__);

        // проверяем подпись
        $this->verifySign(
            Yii::$app->request->rawBody,
            Yii::$app->request->headers->get('x-sign')
        );

        if (! empty($this->module->callback)) {
            $request = new CallbackRequest([
                'json' => Yii::$app->request->bodyParams
            ]);

            call_user_func($this->module->callback, $request, $this->module);
        }
    }

    /**
     * Проверка сигнатуры запроса.
     *
     * @param string $data
     * @param string $sign
     * @return bool true
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    private function verifySign(string $data, string $sign): bool
    {
        $key = openssl_pkey_get_public($this->module->serverKey);
        if ($key === false) {
            throw new ServerErrorHttpException('Некорректный публичный ключ сервера NovaPay');
        }

        $ret = openssl_verify($data, base64_decode($sign), $key);
        if ($ret === 0) {
            throw new BadRequestHttpException('Некорректная сигнатура');
        }

        if ($ret === 2) {
            throw new ServerErrorHttpException('Ошибка SSL: ' . openssl_error_string());
        }

        return true;
    }
}
