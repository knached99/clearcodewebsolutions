<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { width: 100%; margin-bottom: 40px; }
        .header td { vertical-align: top; }
        .company-info { text-align: right; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #4f46e5; margin-bottom: 5px; }
        .details-table { width: 100%; margin-bottom: 40px; }
        .details-table td { vertical-align: top; width: 50%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f9fafb; text-align: left; padding: 12px; border-bottom: 2px solid #e5e7eb; color: #4b5563; font-weight: bold; }
        .items-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .items-table .amount { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #111827; }
        .total-amount { font-size: 18px; color: #4f46e5; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; background: #10b981; color: white; font-size: 11px; font-weight: bold; letter-spacing: 1px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="invoice-title">RECEIPT</div>
                <div><strong>Invoice #:</strong> {{ strtoupper(substr($invoice->id, 0, 8)) }}</div>
                <div><strong>Date:</strong> {{ $invoice->updated_at->format('M d, Y') }}</div>
                <div style="margin-top: 15px;">
                    <span class="badge">PAID</span>
                </div>
            </td>
            <td class="company-info">
                <strong style="font-size: 16px;">{{ config('app.name') }}</strong><br>
                support@clearcodewebsolutions.com<br>
                www.clearcodewebsolutions.com
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td>
                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">BILLED TO</div>
                <strong>{{ $invoice->customer->name }}</strong><br>
                {{ $invoice->customer->email }}
            </td>
            <td>
                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">PROJECT / TICKET</div>
                <strong>{{ $invoice->ticket->ticket_number ?? 'N/A' }}</strong><br>
                {{ $invoice->ticket->subject ?? 'General Services' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    {{ $item->description }}
                    @if($item->is_subscription)
                        <br><span style="color: #6b7280; font-size: 12px;">(Monthly Subscription - 12 month minimum)</span>
                    @endif
                </td>
                <td class="amount">${{ number_format($item->amountInDollars(), 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td style="text-align: right; padding-top: 16px;">Total Paid:</td>
                <td class="amount total-amount" style="padding-top: 16px;">${{ number_format($invoice->totalAmountInDollars(), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 60px; color: #6b7280; font-size: 13px;">
        Thank you for your business!<br>
        If you have any questions about this receipt, please contact us.
    </div>
</body>
</html>
