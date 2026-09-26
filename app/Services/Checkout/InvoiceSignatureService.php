<?php

namespace App\Services\Checkout;

use App\Enums\PaymentType;
use App\Models\Invoice;
use Illuminate\Support\Carbon;

class InvoiceSignatureService
{
    public function requiresSignature(Invoice $invoice): bool
    {
        if ($invoice->payment_type === PaymentType::Subscription) {
            return true;
        }

        if ($invoice->items()->where('is_subscription', true)->exists()) {
            return true;
        }

        return false;
    }

    public function hasSignedAgreement(Invoice $invoice): bool
    {
        return ! empty($invoice->signature_data)
            && ! empty($invoice->signature_signed_at);
    }

    public function saveSignature(Invoice $invoice, string $signatureData): void
    {
        $signature = trim($signatureData);

        if ($signature === '') {
            throw new \InvalidArgumentException('A signature is required before payment can continue.');
        }

        $invoice->forceFill([
            'signature_data' => $signature,
            'signature_signed_at' => Carbon::now(),
            'agreement_version' => 'v1',
        ])->save();
    }
}
