
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoiceData['booking']['status'] == 'quotation' ? 'QUOTATION' : 'INVOICE' }}</title>
    {{-- <link rel="stylesheet" href="{{ asset('css/pdf.min.css') }}"> --}}
    <style>
    .page-break {
        page-break-after: always;
    }
    <x-pdf_css />
    </style>
</head>
<body class="p-10">
  <div class="max-w-3xl mx-auto overflow-hidden bg-white border-gray-500 rounded shadow-lg" style="border:1px solid gainsboro;">
    <div class="flex items-center justify-between p-6 border-b border-gray-300">
      <table style="width:100%">
        <tr>
          <td>
            <img src="{{ public_path('storage/' . ($invoiceData['company']['avatar_url'] ?? 'default.png')) }}" alt="Company Logo" class="w-auto h-10 mr-4">
          </td>
          <td>
            <h2 class="text-lg font-bold">{{ $invoiceData['company']['name'] }}</h2>
            <p class="text-sm text-gray-500">{{ $invoiceData['company']['address'] }}</p>
          </td>
          <td style="text-align: right;">
            <h1 class="text-xl font-bold"> {{ $invoiceData['booking']['status'] == 'quotation' ? 'QUOTATION' : 'INVOICE' }}</h1>
            @if($invoiceData['booking']['status'] != 'quotation')
                {{-- <p class="text-sm text-gray-500">#{{ 'INV-' . str_pad($invoiceData['booking']['id'], 4, '0', STR_PAD_LEFT) }}</p> --}}
                <p class="text-xs font-bold">Booking #: {{ $invoiceData['booking']['booking_id'] }}</p>
            @endif
          </td>
        </tr>
      </table>
    </div>
    <div class="grid grid-cols-2 gap-4 px-6 py-4 text-sm">
      <table style="width:100%">
        <tr>
            <td>
              <p class="font-sans font-semibold text-gray-700">{{ $invoiceData['booking']['status'] == 'quotation' ? 'Quote' : 'Bill' }} To:</p>
            </td>
            <td style="text-align: right;">
              <p class="font-sans font-semibold text-gray-700">Issued:</p>
            </td>
        </tr>
        <tr>
          <td>
            <p>{{ $invoiceData['booking']['renter_name'] }}</p>
            <p>{{ $invoiceData['booking']['renter_address'] }}</p>
            <p>{{ $invoiceData['booking']['contact_number'] }}</p>
          </td>
          <td style="text-align: right;">
          <p>{{ \Carbon\Carbon::parse($invoiceData['booking']['created_at'])->format('F d, Y') }}</p>
          </td>
        </tr>
      </table>
    </div>
    <div class="px-6">
      <table class="w-full text-sm border-t border-b border-gray-200">
        <thead class="text-xs text-gray-600 uppercase bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left">Service</th>
            <th class="px-4 py-2 text-left">Period</th>
            <th class="px-4 py-2 text-center">Duration</th>
            <th class="px-4 py-2 text-right">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr>
              <td style="width: 30%;" class="px-4 py-3">
                  {{ $invoiceData['booking']['car']['brand']." ".$invoiceData['booking']['car']['model']." ".$invoiceData['booking']['car']['year'] }} 
                  <br/> (<b>{{ $invoiceData['booking']['car']['name'] }}</b>)
              </td>
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
              <td class="px-4 py-3 font-bold text-center">
                  {{
                      \Carbon\Carbon::parse($invoiceData['booking']['start_datetime'])->diffInHours(\Carbon\Carbon::parse($invoiceData['booking']['end_datetime'])) > 24
                          ? round(\Carbon\Carbon::parse($invoiceData['booking']['start_datetime'])->diffInDays(\Carbon\Carbon::parse($invoiceData['booking']['end_datetime']))) . ' days'
                          : \Carbon\Carbon::parse($invoiceData['booking']['start_datetime'])->diffInHours(\Carbon\Carbon::parse($invoiceData['booking']['end_datetime'])) . ' hours'
                  }}
              </td>
              <td class="px-4 py-3 text-right">
                  P {{ number_format($invoiceData['booking']['total_rent_due']-$invoiceData['booking']['extend_due'], 2) }}
              </td>
          </tr>

          f
          @if ($invoiceData['booking']['fuel_charge'] > 0 || $invoiceData['booking']['driver_fee'] > 0 || $invoiceData['booking']['out_of_bounds'] > 0 || $invoiceData['booking']['rfid'] > 0 || $invoiceData['booking']['damages'] > 0 || $invoiceData['booking']['insurance'] > 0 || $invoiceData['booking']['carwash_fee'] > 0 || $invoiceData['booking']['extend_hours'] > 0)
            <tr>
                <td class="px-4 fomt-semibold" colspan="4">Additional Charges</td>
            </tr>
            @if ($invoiceData['booking']['extend_hours'] > 0 && $invoiceData['booking']['extend_due'] > 0)
              <tr class="text-sm">
                  <td class="px-4 text-sm " colspan="3">
                      Extension ({{ $invoiceData['booking']['extend_hours'] }} hrs)
                  </td>
                  <td class="px-4 text-right">
                      P {{ number_format($invoiceData['booking']['extend_due'], 2) }}
                  </td>
              </tr>
            @endif

            @if ($invoiceData['booking']['driver_fee'] > 0)
              <tr class="text-sm">
                  <td class="px-4 text-sm " colspan="3">
                        Driver's Fee
                  </td>
                  <td class="px-4 text-right">
                      {{ number_format($invoiceData['booking']['driver_fee'], 2) }}
                  </td>
              </tr>
            @endif
            
            @if($invoiceData['booking']['fuel_charge'] > 0)
                <tr class="text-sm">
                    <td class="px-4 text-sm " colspan="3">
                        Fuel Charge
                    </td>
                    <td class="px-4 text-right">
                        {{ number_format($invoiceData['booking']['fuel_charge'], 2) }}
                    </td>
                </tr>
            @endif
            @if($invoiceData['booking']['out_of_bounds'] > 0)
                <tr class="text-sm">
                    <td class="px-4 text-sm " colspan="3">
                        Out of Geofence Charge
                    </td>
                    <td class="px-4 text-right">
                        {{ number_format($invoiceData['booking']['out_of_bounds'], 2) }}
                    </td>
                </tr>
            @endif
            @if($invoiceData['booking']['rfid'] > 0)
                <tr class="text-sm">
                    <td class="px-4 text-sm " colspan="3">
                        Used RFID Charge
                    </td>
                    <td class="px-4 text-right">
                        {{ number_format($invoiceData['booking']['rfid'], 2) }}
                    </td>
                </tr>
            @endif
            @if($invoiceData['booking']['carwash_fee'] > 0)
                <tr class="text-sm">
                    <td class="px-4 text-sm " colspan="3">
                        Carwash Fee
                    </td>
                    <td class="px-4 text-right">
                        {{ number_format($invoiceData['booking']['carwash_fee'], 2) }}
                    </td>
                </tr>
            @endif
            @if($invoiceData['booking']['damages'] > 0)
                <tr class="text-sm">
                    <td class="px-4 text-sm " colspan="3">
                        Damage Fee
                    </td>
                    <td class="px-4 text-right">
                        {{ number_format($invoiceData['booking']['damages'], 2) }}
                    </td>
                </tr>
            @endif

            @if($invoiceData['booking']['insurance'] > 0)
                <tr class="text-sm">
                    <td class="px-4 text-sm " colspan="3">
                        Insurance Fee
                    </td>
                    <td class="px-4 text-right">
                        {{ number_format($invoiceData['booking']['insurance'], 2) }}
                    </td>
                </tr>
            @endif
        @endif
      </tbody>

      </table><br/>
      <table style="width: 100%; font-size: 13px;margoin-top:15px">
        <tr>
            <td style="vertical-align: top; width: 50%;">
                <b>Destination:</b> {{ $invoiceData['booking']['destination'] ?? '-' }}<br/>
                @if($invoiceData['booking']['delivery_address'])
                    <b>Delivery Address:</b> {{ $invoiceData['booking']['delivery_address'] ?? '-' }}<br/>
                @endif

                @if($invoiceData['booking']['return_address'])
                    <b>Return Address:</b> {{ $invoiceData['booking']['return_address'] ?? '-' }}<br/>
                @endif
                @if($invoiceData['booking']['remarks'])
                    <b>Remarks:</b><br/> {{ $invoiceData['booking']['remarks'] ?? '-' }}<br/>
                @endif
            </td>
{{--             
            <td style="vertical-align: top; width: 50%;">
            @if($invoiceData['booking']['delivery_address'])
                <b>Delivery Address:</b> {{ $invoiceData['booking']['delivery_address'] ?? '-' }}<br/>
            @endif

            @if($invoiceData['booking']['return_address'])
                <b>Return Address:</b> {{ $invoiceData['booking']['return_address'] ?? '-' }}<br/>
            @endif
            </td> --}}
        </tr>
        </table>

        {{-- @if($invoiceData['booking']['security_deposit'] ?? false)
            <br/>
            <span class="text-xs">
            <b>Security Deposit:</b> P {{ number_format($invoiceData['booking']['security_deposit'], 2) }} — refundable upon return of the vehicle in good condition.
            </span>
        @endif --}}

    </div>
    <br/>
    <table style="float:right;font-size:12px;margin-right:30px;">
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
                <td><b>Security Deposit:</b></td>
                <td align="right"> {{ number_format($invoiceData['booking']['security_deposit'], 2) }}</td>
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
            <td align="right">{{ number_format($invoiceData['booking']['total_due'], 2) }}</td>
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
    <div class="py-3 text-xs text-center text-gray-500 bg-gray-50">
      Thank you for your business!
    </div>
  </div>
</body>


</html>