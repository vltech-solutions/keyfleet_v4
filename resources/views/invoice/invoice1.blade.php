
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice</title>
    {{-- <link rel="stylesheet" href="{{ asset('css/pdf.min.css') }}"> --}}
    <style>
    .page-break {
        page-break-after: always;
    }
    <x-pdf_css />
    </style>
</head>
<body class="font-sans text-sm text-gray-800 bg-white">

    <div class="max-w-3xl p-6 mx-auto border border-gray-300 shadow-md">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <!-- Logo and Company Name -->
                <div class="flex items-center gap-2">
                     @if(isset($invoiceData['fromTrace']) && $invoiceData['fromTrace'])
                        <img src="{{ Storage::url($invoiceData['company']['avatar_url'] ?? 'default.png') }}" alt="Company Logo" class="w-auto h-10">
                    @else
                        <img src="{{ public_path('storage/' . ($invoiceData['company']['avatar_url'] ?? 'default.png')) }}" alt="Company Logo" class="w-auto h-10">
                    @endif
                    <div>
                        <h2 class="text-xl font-bold">{{ $invoiceData['company']['name'] }}</h2>
                        <p class="text-xs">Date: {{ \Carbon\Carbon::parse($invoiceData['booking']['created_at'])->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <div class="px-4 py-2 text-xl font-bold tracking-wide text-white" style="background-color:{{ $invoiceData['company']['primary_color'] }}">
                    {{ $invoiceData['booking']['status'] == 'quotation' ? 'Quotation' : 'Invoice' }}
                </div>
            </div>
        </div>


        <hr class="mb-4 border-gray-300">

        <!-- Bill From / To -->
        <div class="grid grid-cols-2 gap-8 mb-6">
            <div style="float:left; width:50%">
                <p class="font-bold">{{ $invoiceData['booking']['status'] == 'quotation' ? 'Quote' : 'Bill' }} from:</p>
                <p>{{ $invoiceData['company']['name'] }}</p>
                <p>{{ $invoiceData['company']['address'] }}</p>
                <p>{{ $invoiceData['company']['contacts'] }}</p>
            </div>
            <div style="float:right; width:50%">
                <p class="font-bold">{{ $invoiceData['booking']['status'] == 'quotation' ? 'Quote' : 'Bill' }} to:</p>
                <p>{{ $invoiceData['booking']['renter_name'] }}</p>
                <p>{{ $invoiceData['booking']['renter_address'] }}</p>
                <p>{{ $invoiceData['booking']['contact_number'] }}</p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Items Table -->
        <p class="text-xs font-bold">Booking #: {{ $invoiceData['booking']['booking_id'] }}</p>

        <table class="w-full mb-4 border-t border-gray-300">
            <thead>
                <tr class="border-b border-gray-300">
                    <th class="py-2 font-bold" align="left">Car</th>
                    <th class="py-2 font-bold" align="left">Destination</th>
                    <th class="py-2 font-bold" align="left">Period</th>
                    <th class="py-2 font-bold" align="left">Duration</th>
                    <th class="py-2 font-bold text-right" align="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 20%;" class="py-2">
                        {{ $invoiceData['booking']['car']['brand']." ".$invoiceData['booking']['car']['model']." ".$invoiceData['booking']['car']['year'] }} 
                        <br/> (<b>{{ $invoiceData['booking']['car']['name'] }}</b>)
                    </td>
                    <td>{{ $invoiceData['booking']['destination'] ?? '-' }}</td>
                    <td style="width: 30%;">
                        <span class="text-xs">
                            @php
                                $start = strtotime($invoiceData['booking']['start_datetime']);
                                $end = strtotime($invoiceData['booking']['end_datetime']);

                                if (date('Y-m-d', $start) == date('Y-m-d', $end)) {
                                    echo date('F j, Y g:i a', $start) . ' to ' . date('g:i a', $end);
                                } else {
                                    echo date('M d, Y h:i a', $start) . ' - ' . date('M d, Y h:i a', $end);
                                }
                            @endphp
                        </span>
                    </td>
                    <td class="py-2 font-bold">
                        {{ 
                            \Carbon\Carbon::parse($invoiceData['booking']['start_datetime'])->diffInHours(\Carbon\Carbon::parse($invoiceData['booking']['end_datetime'])) > 24
                                ? round(\Carbon\Carbon::parse($invoiceData['booking']['start_datetime'])->diffInDays(\Carbon\Carbon::parse($invoiceData['booking']['end_datetime'])))
                                    . ' days'
                                : \Carbon\Carbon::parse($invoiceData['booking']['start_datetime'])->diffInHours(\Carbon\Carbon::parse($invoiceData['booking']['end_datetime']))
                                    . ' hours'
                        }}
                    </td>
                    <td class="py-2 font-bold text-right">
                        P {{ number_format($invoiceData['booking']['total_rent_due']-$invoiceData['booking']['extend_due'], 2) }}
                    </td>
                </tr>

                

                @if ($invoiceData['booking']['fuel_charge'] > 0 || $invoiceData['booking']['driver_fee'] > 0 || $invoiceData['booking']['out_of_bounds'] > 0 || $invoiceData['booking']['rfid'] > 0 || $invoiceData['booking']['damages'] > 0 || $invoiceData['booking']['carwash_fee'] > 0 || $invoiceData['booking']['insurance'] > 0 || $invoiceData['booking']['extend_hours'] > 0)
                    <tr>
                        <td colspan="4">Additional Charges</td>
                    </tr>

                    @if ($invoiceData['booking']['extend_hours'] > 0 && $invoiceData['booking']['extend_due'] > 0)
                        <tr>
                            <td class="text-sm " colspan="2">
                                Extension
                            </td>
                            <td class="font-bold">
                                {{ $invoiceData['booking']['extend_hours'] }} hrs
                            </td>
                            <td class="font-bold">
                                P {{ number_format($invoiceData['booking']['extend_due'], 2) }}
                            </td>
                        </tr>
                    @endif

                    
                    @if($invoiceData['booking']['driver_fee'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Driver's Fee
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['driver_fee'], 2) }}
                            </td>
                        </tr>
                    @endif
                    
                    @if($invoiceData['booking']['fuel_charge'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Fuel Charge
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['fuel_charge'], 2) }}
                            </td>
                        </tr>
                    @endif
                    
                    @if($invoiceData['booking']['out_of_bounds'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Out of Geofence Charge
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['out_of_bounds'], 2) }}
                            </td>
                        </tr>
                    @endif

                    @if($invoiceData['booking']['rfid'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Used RFID Charge
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['rfid'], 2) }}
                            </td>
                        </tr>
                    @endif

                    @if($invoiceData['booking']['carwash_fee'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Carwash Fee
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['carwash_fee'], 2) }}
                            </td>
                        </tr>
                    @endif

                    @if($invoiceData['booking']['damages'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Damage Fee
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['damages'], 2) }}
                            </td>
                        </tr>
                    @endif

                    @if($invoiceData['booking']['insurance'] > 0)
                        <tr>
                            <td class="text-sm " colspan="4">
                                Insurance Fee
                            </td>
                            <td class="font-bold text-right">
                               {{ number_format($invoiceData['booking']['insurance'], 2) }}
                            </td>
                        </tr>
                    @endif

                @endif

             </tbody>
        </table>

        <table style="width: 100%; font-size: 12px;margoin-top:15px">
        <tr>
            <td style="vertical-align: top; width: 50%;">
            @if($invoiceData['booking']['delivery_address'])
                <b>Delivery Address:</b> {{ $invoiceData['booking']['delivery_address'] ?? '-' }}<br/>
            @endif

            @if($invoiceData['booking']['return_address'])
                <b>Return Address:</b> {{ $invoiceData['booking']['return_address'] ?? '-' }}<br/>
            @endif

            @if($invoiceData['booking']['remarks'])
                <b>Remarks:</b> {{ $invoiceData['booking']['remarks'] ?? '-' }}<br/>
            @endif
            </td>
        </tr>
        </table>

        <!-- Summary -->
        <table style="float:right">
            <tr>
                <td><b>Subtotal:</b></td>
                <td align="right">{{ number_format($invoiceData['booking']['total_rent_due']
                    + $invoiceData['booking']['fuel_charge']
                    + $invoiceData['booking']['out_of_bounds']
                    + $invoiceData['booking']['rfid']
                    + $invoiceData['booking']['carwash_fee']
                    + $invoiceData['booking']['damages']
                    + $invoiceData['booking']['insurance']
                    + $invoiceData['booking']['driver_fee']
                    
                , 2) }}</td>
            </tr>
            @if($invoiceData['booking']['delivery_fee'] > 0)
                <tr>
                    <td><b>Delivery Fee:</b></td>
                    <td align="right">{{ number_format($invoiceData['booking']['delivery_fee'], 2) }}</td>
                </tr>
            @endif
            @if($invoiceData['booking']['security_deposit'] > 0)
                <tr>
                    <td><b>Secuirty Deposit:</b></td>
                    <td align="right">{{ number_format($invoiceData['booking']['security_deposit'], 2) }}</td>
                </tr>
            @endif
            @if($invoiceData['booking']['discount'] > 0)
                <tr>
                    <td><b>Discount:</b></td>
                    <td align="right"> -{{ number_format($invoiceData['booking']['discount'], 2) }}</td>
                </tr>
            @endif
            <tr>
                <td><b>Grand Total:</b></td>
                <td align="right">{{ number_format($invoiceData['booking']['total_due']
                     
                     , 2) }}</td>
            </tr>
            <tr>
                <td><b>Paid:</b></td>
                <td align="right">{{ $invoiceData['booking']['balance'] == 0 ? 'P' : '' }} {{ number_format($invoiceData['booking']['paid_amount'], 2) }}</td>
            </tr>
            <tr style="display: {{ ($invoiceData['booking']['balance'] > 0) ? 'table-row' : 'none' }}">
                <td><b>Balance:</b></td>
                <td align="right" style="color:{{ ($invoiceData['booking']['balance'] > 0) ? 'red' : 'black' }}">P {{ number_format($invoiceData['booking']['balance'], 2) }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>    
        <br/>
        <center>
            {{-- <small><b>-- This is a system generated invoice. No signature needed. --</b></small><br/> --}}
            <small><b>-- Thank you for your business! --</b></small>
        </center>
    </div>
</body>
</html>
