<?php

namespace App\Livewire\Teams\Invoices;

use App\Models\Invoice;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['customer', 'ticket', 'items']);
    }

    public function render()
    {
        return view('livewire.teams.invoices.invoice-show')
            ->layout('layouts.app');
    }
}
