<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Income Flow Report</title>
    <style>
        @php
            $css = cache()->rememberForever('pdf_css', fn () => file_get_contents(public_path('css/pdf.min.css')));
        @endphp
        {!! $css !!}
    </style>
</head>
<body>
    <div class="max-w-4xl p-6 mx-auto">
        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                @if(isset($company) && $company->avatar_url)
                    <img src="{{ storage_path('app/public/' . $company->avatar_url) }}" alt="{{ $company->name }}" class="w-auto h-12">
                @endif
                <div>
                    <h2 class="text-xl font-bold">{{ isset($company) ? $company->name : 'Company' }}</h2>
                    @if(isset($company) && $company->address)
                        <p class="text-xs text-gray-600">{{ $company->address }}</p>
                    @endif
                    @if(isset($company) && $company->contacts)
                        <p class="text-xs text-gray-600">{{ $company->contacts }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold">Income Flow Report</h2>
                <p class="text-xs text-gray-600">Generated: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
            </div>
        </div>

        <hr class="my-4 border-gray-300">

        <!-- FILTER INFORMATION -->
        <div class="p-3 mb-4 text-sm bg-gray-100 rounded">
            <strong>Filters:</strong>
            @if($filter_period === 'monthly')
                {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            @elseif($filter_period === 'yearly')
                Year: {{ $year }}
            @elseif($filter_period === 'custom')
                {{ \Carbon\Carbon::parse($date_from)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($date_to)->format('M d, Y') }}
            @endif
            &nbsp;|&nbsp; <strong>Type:</strong> {{ ucfirst($type) }}
            &nbsp;|&nbsp; <strong>Fund:</strong> 
            @if($fund_type !== 'all')
                {{ \App\Models\FundType::find($fund_type)?->name ?? 'Unknown' }}
            @else
                All Funds
            @endif
            &nbsp;|&nbsp; <strong>Records:</strong> {{ number_format($records->count()) }}
        </div>

        <!-- SUMMARY TOTALS -->
        <div class="mb-4 overflow-hidden border border-gray-300 rounded">
            <table class="w-full">
                <tr>
                    <td class="px-4 py-3 text-center border-r border-gray-300" style="width: 25%;">
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Income</p>
                        <p class="text-xl font-bold text-green-600">&#8369;{{ number_format($totalIncome, 2) }}</p>
                        <p class="text-xs text-gray-400">Gross revenue collected</p>
                    </td>
                    <td class="px-4 py-3 text-center border-r border-gray-300" style="width: 25%;">
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Expenses</p>
                        <p class="text-xl font-bold text-red-600">&#8369;{{ number_format($totalExpenses, 2) }}</p>
                        <p class="text-xs text-gray-400">Cash outflow</p>
                    </td>
                    <td class="px-4 py-3 text-center border-r border-gray-300" style="width: 25%; {{ $netCashFlow >= 0 ? 'background: #f0fdf4;' : 'background: #fef2f2;' }}">
                        <p class="text-xs font-bold text-gray-500 uppercase">Net Cash Flow</p>
                        <p class="text-xl font-bold {{ $netCashFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $netCashFlow >= 0 ? '+' : '' }}&#8369;{{ number_format($netCashFlow, 2) }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $netCashFlow >= 0 ? 'Positive cash flow' : 'Negative cash flow' }}</p>
                    </td>
                    <td class="px-4 py-3 text-center" style="width: 25%;">
                        <p class="text-xs font-bold text-gray-500 uppercase">Total Transactions</p>
                        <p class="text-xl font-bold text-blue-600">{{ number_format($records->count()) }}</p>
                        <p class="text-xs text-gray-400">Combined income & expenses</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TRANSACTIONS TABLE -->
        <table class="w-full mb-4 border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 text-left text-xs font-bold border-b border-gray-300" style="width: 12%;">Date</th>
                    <th class="px-3 py-2 text-left text-xs font-bold border-b border-gray-300" style="width: 18%;">Fund Type</th>
                    <th class="px-3 py-2 text-left text-xs font-bold border-b border-gray-300" style="width: 12%;">Type</th>
                    <th class="px-3 py-2 text-left text-xs font-bold border-b border-gray-300" style="width: 43%;">Description</th>
                    <th class="px-3 py-2 text-right text-xs font-bold border-b border-gray-300" style="width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr class="border-b border-gray-200">
                        <td class="px-3 py-2 text-sm">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                        <td class="px-3 py-2 text-sm">{{ ucfirst($record->fund_name ?? 'N/A') }}</td>
                        <td class="px-3 py-2 text-sm">
                            @php
                                $isIncome = $record->type === 'income';
                                $badgeClass = $isIncome ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            @endphp
                            <span class="px-2 py-1 text-xs font-bold rounded {{ $badgeClass }}">
                                {{ ucfirst($record->type) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-sm">
                            @if($record->type === 'income' && isset($record->booking_id) && $record->booking_id)
                                {{ $record->description && $record->description != '' ? $record->description : 'Booking payment' }}
                                from <strong>{{ $record->renter_name ?? 'Unknown' }}</strong>
                            @else
                                {{ $record->description ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 py-2 text-sm text-right font-bold {{ $record->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $record->type === 'income' ? '+' : '-' }}&#8369;{{ number_format($record->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                            No transactions found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- FOOTER -->
        <div class="pt-3 text-xs text-center text-gray-400 border-t border-gray-300">
            <p>This is a system-generated report. No signature needed.</p>
            <p class="mt-1">&copy; {{ date('Y') }} {{ isset($company) ? $company->name : 'Company' }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>