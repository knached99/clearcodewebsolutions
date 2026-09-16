<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Card = 'card';
    case UsBankAccount = 'us_bank_account';
    case Cashapp = 'cashapp';
    case Klarna = 'klarna';
    case Afterpay = 'afterpay_clearpay';
    case Affirm = 'affirm';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card',
            self::UsBankAccount => 'Bank account',
            self::Cashapp => 'Cash App Pay',
            self::Klarna => 'Klarna',
            self::Afterpay => 'Afterpay / Clearpay',
            self::Affirm => 'Affirm',
        };
    }

    /**
     * All payment method types offered on the custom checkout page.
     */
    public static function forCheckout(): array
    {
        return [
            self::Card,
            self::UsBankAccount,
            self::Cashapp,
            self::Klarna,
            self::Afterpay,
            self::Affirm,
        ];
    }

    /**
     * Stripe does not support Klarna/Afterpay/Affirm/Cash App on
     * recurring subscriptions — only card and us_bank_account can be
     * used when Invoice::payment_type is "subscription".
     */
    public static function subscriptionCompatible(): array
    {
        return [self::Card, self::UsBankAccount];
    }
}