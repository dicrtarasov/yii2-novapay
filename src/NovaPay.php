<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 23.08.20 02:46:44
 */

declare(strict_types = 1);
namespace dicr\novapay;

/**
 * Константы NovaPay
 */
interface NovaPay
{
    /** @var string URL рабочей API */
    public const API_URL = 'https://api-ecom.novapay.ua/v1';

    /** @var string URL для тестовой API */
    public const TEST_URL = 'https://api-qecom.novapay.ua/v1';

    /** @var string тестовый merchant_id */
    public const TEST_MERCHANT_ID = '1';

    /** @var string публичный ключ NovaPay */
    public const SERVER_PUB = 'file://' . __DIR__ . '/../ssl/novapay.pub';

    /** @var string тестовый публичный ключ NovaPay */
    public const TEST_SERVER_PUB = 'file://' . __DIR__ . '/../ssl/novapay-test.pub';

    /** @var string тестовый приватный ключ клиента */
    public const TEST_CLIENT_KEY = 'file://' . __DIR__ . '/../ssl/merchant-test.key';

    /** @var string created session */
    public const STATUS_PRECREATED = 'precreated';

    /** @var string created session */
    public const STATUS_CREATED = 'created';

    /** @var string session expired, no further actions available */
    public const STATUS_EXPIRED = 'expired';

    /** @var string session is processing, payer is entering his payment data */
    public const STATUS_PROCESSING = 'processing';

    /** @var string session amount is holded on payer account */
    public const STATUS_HOLDED = 'holded';

    /** @var string */
    public const STATUS_HOLD_CONFIRMED = 'hold_confirmed';

    /** @var string hold completion is in process */
    public const STATUS_PROCESSING_HOLD_COMPLETION = 'processing_hold_completion';

    /** @var string session is fully paid */
    public const STATUS_PAID = 'paid';

    /** @var string session payment failed */
    public const STATUS_FAILED = 'failed';

    /** @var string session amount voiding is in process */
    public const STATUS_PROCESSING_VOID = 'processing_void';

    /** @var string session payment voided */
    public const STATUS_VOIDED = 'voided';
}
