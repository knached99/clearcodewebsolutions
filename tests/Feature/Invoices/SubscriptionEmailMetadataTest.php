<?php

namespace Tests\Feature\Invoices;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Mail\PaymentReceived;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Ticket;
use Tests\TestCase;

class SubscriptionEmailMetadataTest extends TestCase
{
    public function test_payment_received_email_includes_recurring_metadata_and_next_payment_dates(): void
    {
        $invoice = new Invoice([
            'id' => '01a0d043-c797-73fc-bb74-8a52e0a4a6eb',
            'total_amount' => 250000,
            'status' => InvoiceStatus::Paid,
            'payment_type' => PaymentType::Subscription,
            'subscription_months' => 12,
            'created_at' => now()->startOfMonth(),
            'updated_at' => now()->startOfMonth(),
        ]);

        $invoice->setRelation('customer', new Customer(['name' => 'Karam Toor']));
        $invoice->setRelation('ticket', new Ticket(['ticket_number' => 'TCKT-IPJ8F9FX']));
        $invoice->setRelation('items', collect([
            new InvoiceItem([
                'description' => 'Development Services',
                'amount' => 200000,
                'is_subscription' => true,
            ]),
            new InvoiceItem([
                'description' => 'Ongoing Maintenance',
                'amount' => 5000,
                'is_subscription' => true,
            ]),
        ]));

        $html = (new PaymentReceived($invoice))->render();

        $this->assertStringContainsString('x12', $html);
        $this->assertStringContainsString('Next payment', $html);
        $this->assertStringContainsString('Renews on', $html);
    }
}
