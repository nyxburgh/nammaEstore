<?php
namespace App\Core;

use App\Core\Interfaces\{PaymentInterface, SMSInterface, EmailInterface, WhatsAppInterface, ShippingInterface};
use App\Core\Providers\{RazorpayPaymentProvider, Msg91SmsProvider, SmtpEmailProvider, MetaWhatsAppProvider, ManualShippingProvider};

/**
 * Single place that knows which concrete provider class backs each
 * interface. Services type-hint the interface and ask this factory
 * for an instance — they never `new` a concrete provider directly.
 *
 * Switching gateways = add a case here + a new Provider class.
 * Nothing in Controllers or Services changes.
 *
 * Active provider per channel is read from config/providers.php so
 * ops can flip providers without touching code.
 */
class ProviderFactory
{
    private static array $instances = [];

    public static function payment(): PaymentInterface
    {
        return self::$instances['payment'] ??= self::makePayment(config('providers.payment.active'));
    }

    public static function sms(): SMSInterface
    {
        return self::$instances['sms'] ??= self::makeSms(config('providers.sms.active'));
    }

    public static function email(): EmailInterface
    {
        return self::$instances['email'] ??= self::makeEmail(config('providers.email.active'));
    }

    public static function whatsapp(): WhatsAppInterface
    {
        return self::$instances['whatsapp'] ??= self::makeWhatsApp(config('providers.whatsapp.active'));
    }

    public static function shipping(): ShippingInterface
    {
        return self::$instances['shipping'] ??= self::makeShipping(config('providers.shipping.active'));
    }

    private static function makePayment(string $name): PaymentInterface
    {
        $c = config("providers.payment.{$name}");
        return match ($name) {
            'razorpay' => new RazorpayPaymentProvider($c['key_id'], $c['key_secret']),
            default    => throw new \RuntimeException("Unknown payment provider: {$name}"),
        };
    }

    private static function makeSms(string $name): SMSInterface
    {
        $c = config("providers.sms.{$name}");
        return match ($name) {
            'msg91'  => new Msg91SmsProvider($c['auth_key'], $c['sender_id']),
            default  => throw new \RuntimeException("Unknown SMS provider: {$name}"),
        };
    }

    private static function makeEmail(string $name): EmailInterface
    {
        $c = config("providers.email.{$name}");
        return match ($name) {
            'smtp'  => new SmtpEmailProvider($c['host'], $c['port'], $c['username'], $c['password'], $c['from_email'], $c['from_name']),
            default => throw new \RuntimeException("Unknown email provider: {$name}"),
        };
    }

    private static function makeWhatsApp(string $name): WhatsAppInterface
    {
        $c = config("providers.whatsapp.{$name}");
        return match ($name) {
            'meta'  => new MetaWhatsAppProvider($c['phone_number_id'], $c['access_token']),
            default => throw new \RuntimeException("Unknown WhatsApp provider: {$name}"),
        };
    }

    private static function makeShipping(string $name): ShippingInterface
    {
        return match ($name) {
            'manual' => new ManualShippingProvider(),
            default  => throw new \RuntimeException("Unknown shipping provider: {$name}"),
        };
    }
}
