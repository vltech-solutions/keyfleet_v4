<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number ?? 'INV-2024-001' }} - KeyFleet</title>
    <style>
        :root {
          --color-invoice-header: #2b3a4a;
          --color-invoice-accent: #3a4f66;
          --color-invoice-light: #f1f4f7;
          --color-invoice-border: #d1dae3;
          --color-invoice-text-light: #6b7785;
        }
        .bg-invoice-header{
          background-color: var(--color-invoice-header);
        }
        .bg-invoice-light{
          background-color: var(--color-invoice-light);
        }
        .text-invoice-accent{
          color: var(--color-invoice-accent);
        }
        .text-invoice-light{
          color: var(--color-invoice-light);
        }
        .border-invoice-border{
          border-color: var(--color-invoice-border);
        }
        .text-invoice-text-light{
          color: var(--color-invoice-text-light);
        }
        
      <x-pdf_css />
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto p-6 bg-white print:p-0 print:shadow-none">
        <div class="shadow-lg border border-invoice-border rounded-lg print:shadow-none print:border-none">
            <!-- Header -->
            <div class="bg-invoice-header text-white p-6 rounded-t-lg print:rounded-none">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white rounded-lg p-2 flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-8 h-8 text-invoice-header">
                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                <path d="M7 17h8"/>
                                <path d="M7 13h8"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">KeyFleet</h1>
                            <p class="text-blue-100 text-sm">Premium Car Rentals</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <h2 class="text-3xl font-bold">INVOICE</h2>
                        <p class="text-blue-100">{{ $invoice->invoice_number ?? 'INV-2024-001' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Company & Client Information -->
                <div class="grid md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-invoice-accent mb-3">From:</h3>
                        <div class="space-y-1 text-gray-800">
                            <p class="font-medium">{{ $company->name ?? 'KeyFleet Rentals' }}</p>
                            <p class="text-invoice-text-light">{{ $company->address ?? '123 Fleet Avenue' }}</p>
                            <p class="text-invoice-text-light">{{ $company->city ?? 'Makati City, Metro Manila' }}</p>
                            <p class="text-invoice-text-light">{{ $company->email ?? 'support@keyfleet.com' }}</p>
                            <p class="text-invoice-text-light">{{ $company->phone ?? '+63 2 8123 4567' }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-invoice-accent mb-3">Bill To:</h3>
                        <div class="space-y-1 text-gray-800">
                            <p class="font-medium">{{ $client->name ?? 'John Smith' }}</p>
                            <p class="text-invoice-text-light">{{ $client->address ?? '456 Business St., BGC, Taguig City' }}</p>
                            <p class="text-invoice-text-light">{{ $client->email ?? 'john.smith@email.com' }}</p>
                            <p class="text-invoice-text-light">{{ $client->phone ?? '+63 912 345 6789' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Details -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8 p-4 bg-invoice-light rounded-lg">
                    <div>
                        <p class="text-sm text-invoice-text-light font-medium">Invoice Date</p>
                        <p class="text-gray-800 font-semibold">{{ $invoice->date ?? now()->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-invoice-text-light font-medium">Due Date</p>
                        <p class="text-gray-800 font-semibold">{{ $invoice->due_date ?? now()->addDays(30)->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-invoice-text-light font-medium">Invoice Number</p>
                        <p class="text-gray-800 font-semibold">{{ $invoice->invoice_number ?? 'INV-2024-001' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-invoice-text-light font-medium">Rental Start Date</p>
                        <p class="text-gray-800 font-semibold">{{ $rental->start_date ?? now()->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-invoice-text-light font-medium">Rental End Date</p>
                        <p class="text-gray-800 font-semibold">{{ $rental->end_date ?? now()->addDays(5)->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-invoice-text-light font-medium">Rental Duration</p>
                        <p class="text-gray-800 font-semibold">{{ $rental->duration ?? '5' }} Days</p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-invoice-light">
                                    <th class="text-left p-3 text-invoice-accent font-semibold border-b border-invoice-border">Description</th>
                                    <th class="text-center p-3 text-invoice-accent font-semibold border-b border-invoice-border w-20">Qty</th>
                                    <th class="text-right p-3 text-invoice-accent font-semibold border-b border-invoice-border w-24">Rate</th>
                                    <th class="text-right p-3 text-invoice-accent font-semibold border-b border-invoice-border w-28">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items ?? [] as $item)
                                    <tr class="border-b border-invoice-border hover:bg-gray-50">
                                        <td class="p-3 text-gray-800">{{ $item->description }}</td>
                                        <td class="p-3 text-center text-invoice-text-light">{{ $item->quantity }}</td>
                                        <td class="p-3 text-right text-invoice-text-light">₱{{ number_format($item->rate, 0) }}</td>
                                        <td class="p-3 text-right font-medium text-gray-800">₱{{ number_format($item->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <!-- Sample data for demo -->
                                    <tr class="border-b border-invoice-border hover:bg-gray-50">
                                        <td class="p-3 text-gray-800">Toyota Camry - 5 Days Rental</td>
                                        <td class="p-3 text-center text-invoice-text-light">5</td>
                                        <td class="p-3 text-right text-invoice-text-light">₱2,500</td>
                                        <td class="p-3 text-right font-medium text-gray-800">₱12,500</td>
                                    </tr>
                                    <tr class="border-b border-invoice-border hover:bg-gray-50">
                                        <td class="p-3 text-gray-800">Fuel Charge</td>
                                        <td class="p-3 text-center text-invoice-text-light">1</td>
                                        <td class="p-3 text-right text-invoice-text-light">₱1,200</td>
                                        <td class="p-3 text-right font-medium text-gray-800">₱1,200</td>
                                    </tr>
                                    <tr class="border-b border-invoice-border hover:bg-gray-50">
                                        <td class="p-3 text-gray-800">Airport Delivery Fee</td>
                                        <td class="p-3 text-center text-invoice-text-light">1</td>
                                        <td class="p-3 text-right text-invoice-text-light">₱800</td>
                                        <td class="p-3 text-right font-medium text-gray-800">₱800</td>
                                    </tr>
                                    <tr class="border-b border-invoice-border hover:bg-gray-50">
                                        <td class="p-3 text-gray-800">Professional Driver (2 Days)</td>
                                        <td class="p-3 text-center text-invoice-text-light">2</td>
                                        <td class="p-3 text-right text-invoice-text-light">₱1,500</td>
                                        <td class="p-3 text-right font-medium text-gray-800">₱3,000</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary -->
                <div class="flex justify-end mb-8">
                    <div class="w-full md:w-80">
                        <div class="space-y-2 p-4 bg-invoice-light rounded-lg">
                            <div class="flex justify-between">
                                <span class="text-invoice-text-light">Subtotal:</span>
                                <span class="font-medium text-gray-800">₱{{ number_format($invoice->subtotal ?? 17500, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-invoice-text-light">Discount:</span>
                                <span class="font-medium text-gray-800">-₱{{ number_format($invoice->discount ?? 500, 0) }}</span>
                            </div>
                            <hr class="my-2 border-invoice-border">
                            <div class="flex justify-between text-lg">
                                <span class="font-semibold text-invoice-accent">Grand Total:</span>
                                <span class="font-bold text-invoice-accent">₱{{ number_format($invoice->grand_total ?? 17000, 0) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-invoice-text-light">Amount Paid:</span>
                                <span class="font-medium text-gray-800">₱{{ number_format($invoice->paid ?? 10000, 0) }}</span>
                            </div>
                            <hr class="my-2 border-invoice-border">
                            <div class="flex justify-between text-lg">
                                <span class="font-semibold text-red-600">Balance Due:</span>
                                <span class="font-bold text-red-600">₱{{ number_format($invoice->balance ?? 7000, 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remarks -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-invoice-accent mb-3">Remarks & Notes</h3>
                    <div class="p-4 bg-invoice-light rounded-lg">
                        <p class="text-invoice-text-light leading-relaxed">
                            {{ $invoice->remarks ?? 'Payment due within 30 days. Late payment charges apply after due date. Vehicle returned in excellent condition.' }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="pt-6 border-t border-invoice-border">
                    <div class="text-center space-y-2">
                        <h3 class="text-xl font-semibold text-invoice-accent">Thank You for Your Business!</h3>
                        <p class="text-invoice-text-light">For inquiries, contact us at {{ $company->email ?? 'support@keyfleet.com' }} or {{ $company->phone ?? '+63 2 8123 4567' }}</p>
                        <p class="text-sm text-invoice-text-light">Visit us at {{ $company->website ?? 'www.keyfleet.com' }} | Follow us @KeyFleetRentals</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Print functionality
        function printInvoice() {
            window.print();
        }
        
        // Add print button if needed
        document.addEventListener('DOMContentLoaded', function() {
            // You can add a print button here if needed
        });
    </script>
</body>
</html>