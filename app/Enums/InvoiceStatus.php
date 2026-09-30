<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Partial => 'Partial',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Void => 'Void',
        };
    }
}
