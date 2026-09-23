<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Enums\TransactionStatus;
use App\Mail\PaymentReceiptMail;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SimulatePaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simulate-payment {invoice : The UUID or checkout token of the invoice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a successful Stripe payment for local testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $identifier = $this->argument('invoice');

        $invoice = Invoice::where('id', $identifier)
            ->orWhere('checkout_token', $identifier)
            ->first();

        if (! $invoice) {
            $this->error("Invoice not found for identifier: {$identifier}");

            return Command::FAILURE;
        }

        if ($invoice->status === InvoiceStatus::Paid) {
            $this->warn('Invoice is already marked as paid.');

            return Command::SUCCESS;
        }

        $this->info("Processing payment for Invoice {$invoice->id}...");

        // Mark as paid
        $invoice->update(['status' => InvoiceStatus::Paid]);

        // Generate simulated transaction
        Transaction::create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'stripe_payment_intent_id' => 'pi_simulated_'.Str::random(14),
            'amount' => $invoice->total_amount,
            'status' => TransactionStatus::Succeeded,
            'payment_method_type' => 'card',
        ]);

        // Send Email & PDF
        $this->info("Generating PDF and sending email receipt to {$invoice->customer->email}...");
        Mail::to($invoice->customer->email)->send(new PaymentReceiptMail($invoice));

        $this->info('✅ Successfully simulated payment!');
        $this->info('The invoice status is now Paid, a transaction was recorded, and the PDF receipt was emailed.');

        return Command::SUCCESS;
    }
}
