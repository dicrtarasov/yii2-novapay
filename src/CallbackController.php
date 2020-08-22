<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license proprietary
 * @version 23.08.20 01:46:31
 */

declare(strict_types = 1);
namespace dicr\novapay;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

use function call_user_func;

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
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        if (! Yii::$app->request->isPost) {
            throw new BadRequestHttpException();
        }

        Yii::debug('NovaPay callback: ' . Yii::$app->request->rawBody);

        if (! empty($this->module->callback)) {
            $request = new CallbackRequest([
                'data' => Yii::$app->request->bodyParams
            ]);

            call_user_func($this->module->callback, $request, $this->module);
        }
    }
}
