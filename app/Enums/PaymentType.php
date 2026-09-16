<?php

namespace App\Enums;

enum PaymentType: string
{
    case OneTime = 'one_time';
    case Subscription = 'subscription';

    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-time payment',
            self::Subscription => 'Subscription',
        };
    }
}