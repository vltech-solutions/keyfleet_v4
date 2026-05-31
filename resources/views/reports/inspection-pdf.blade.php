<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 40px; }
        /* Using Arial for the entire document */
        body { font-family: 'Arial', sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; background: #fff; }
        
        /* Typography & Layout */
        .w-full { width: 100%; border-collapse: collapse; }
        .uppercase { text-transform: uppercase; letter-spacing: 0.1em; }
        .text-muted { color: #71717a; font-size: 8px; }
        .font-black { font-weight: 900; }
        
        /* Header Section */
        .header { border-bottom: 3px solid #1a1a1a; padding-bottom: 15px; margin-bottom: 25px; }
        .company-name { font-size: 20px; font-weight: 900; margin: 0; color: #000; }
        .doc-label { background: {{ $inspection->booking->company->primary_color }}; color: #fff; padding: 4px 8px; font-size: 9px; font-weight: bold; margin-top: 5px; display: inline-block; }

        /* Section Headings */
        .section-head { background: #f4f4f5; padding: 6px 10px; font-weight: 900; font-size: 9px; border-top: 1px solid #1a1a1a; margin-top: 20px; }

        /* Data Grid */
        .data-grid td { padding: 12px 10px; border-bottom: 1px solid #e4e4e7; vertical-align: top; }
        .field-label { font-size: 8px; font-weight: bold; color: #a1a1aa; margin-bottom: 4px; }
        .field-value { font-size: 11px; font-weight: bold; color: #18181b; }

        /* Checkbox Styling */
        .check-row { border-bottom: 1px solid #f4f4f5; }
        .check-row td { padding: 4px 0; font-size: 10px; }
        .status-box { font-family: 'DejaVu Sans', sans-serif; font-weight: bold; text-align: right; }

        /* Finding Card */
        .finding-card { margin-top: 15px; border: 1px solid #e4e4e7; border-radius: 4px; overflow: hidden; }
        .finding-header { background: {{ $inspection->booking->company->primary_color }}; color: #fff; padding: 8px 12px; font-size: 9px; font-weight: bold; }
        .finding-body { padding: 12px; }
        .finding-photo { width: 350px; border: 1px solid #e4e4e7; border-radius: 2px; }

        /* Footer */
        .footer { position: fixed; bottom: -10px; width: 100%; border-top: 1px solid #1a1a1a; padding-top: 10px; font-size: 8px; font-weight: bold; }

        /* Signature Section */
        .signature-section { margin-top: 30px; border-top: 2px solid #1a1a1a; padding-top: 15px; }
        .signature-box { 
            border: 1px solid #e4e4e7; 
            background: #fafafa; 
            padding: 10px; 
            text-align: center; 
            width: 350px; 
            height: 100px; 
            margin-top: 10px;
        }
        .signature-img { 
            max-width: 230px; 
            max-height: 80px; 
            display: block; 
            margin: 0 auto; 
        }
        .remarks-box { 
            background: #fdfdfd; 
            border: 1px solid #e4e4e7; 
            padding: 10px; 
            font-size: 9px; 
            color: #4b5563; 
            min-height: 40px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table class="w-full">
            <tr>
                <td style="width: 60%;">
                    <h1 class="company-name">{{ strtoupper($inspection->booking->company->name ?? 'Vehicle Operations') }}</h1>
                    <span class="doc-label uppercase">{{ strtoupper($inspection->type) }} Inspection Certificate</span>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom;">
                    <div class="text-muted uppercase">Reference Number</div>
                    <div style="font-size: 14px; font-weight: 900;">#{{ date('Y') }}-{{ str_pad($inspection->id, 5, '0', STR_PAD_LEFT) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-head uppercase">Renter & Trip Info</div>
    <table class="w-full data-grid">
        <tr>
            <td style="width: 33.33%;">
                <div class="field-label uppercase">Renter</div>
                <div class="field-value">{{ $inspection->booking->renter_name }} {{ $inspection->booking->car->model }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="field-label uppercase">Contact Number</div>
                <div class="field-value">{{ $inspection->booking->contact_number }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="field-label uppercase">Address</div>
                <div class="field-value">{{ $inspection->booking->renter_address }}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 33.33%;">
                <div class="field-label uppercase">Destination</div>
                <div class="field-value">{{ $inspection->booking->destination ?? 'NOT DECLARED' }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="field-label uppercase">Delivery Address</div>
                <div class="field-value">{{ $inspection->booking->delivery_address ?: 'Garage' }}</div>
            </td>
            <td style="width: 33.33%;">
                <div class="field-label uppercase">Return Address</div>
                <div class="field-value">{{ $inspection->booking->return_address ?: 'Garage' }}</div>
            </td>
        </tr>
    </table>

    <div class="section-head uppercase">Asset & Usage Summary</div>
    <table class="w-full data-grid">
        <tr>
            <td style="width: 20%;">
                <div class="field-label uppercase">Vehicle</div>
                <div class="field-value">{{ $inspection->booking->car->brand }} {{ $inspection->booking->car->model }}</div>
            </td>
            <td style="width: 20%;">
                <div class="field-label uppercase">License Plate</div>
                <div class="field-value">{{ $inspection->booking->car->plate_number }}</div>
            </td>
            <td style="width: 20%;">
                <div class="field-label uppercase">Mileage (KM)</div>
                <div class="field-value">{{ number_format($inspection->odo) }}</div>
            </td>
            <td style="width: 20%;">
                <div class="field-label uppercase">Fuel Status</div>
                <div class="field-value">{{ $inspection->gas }}%</div>
            </td>
            <td style="width: 20%;">
                <div class="field-label uppercase">RFID Load</div>
                <div class="field-value">Autosweep: {{ $inspection->autosweep }} <br/>Easytrip: {{ $inspection->easytrip }}</div>
            </td>
        </tr>
    </table>

    <table class="w-full" style="margin-top: 10px;">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="section-head uppercase">Functional Test</div>
                <table class="w-full">
                    @foreach($inspection->functions as $key => $val)
                    <tr class="check-row">
                        <td style="color: #52525b;">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                        <td class="status-box" style="color: {{ $val ? '#005d00' : '#ef4444' }};">
                            {{ $val ? 'OK' : 'FAIL' }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="section-head uppercase">Tire Condition & Accessories</div>
                <table class="w-full">
                    @php
                        // I-define ang pagkakasunod-sunod na gusto mo
                        $orderedKeys = [
                            'front_left', 
                            'front_right', 
                            'rear_left', 
                            'rear_right', 
                            'spare_tire', // Huli sa listahan ng gulong
                            'tools_jack',  // Simula ng accessories
                            'early_warning'
                        ];
                    @endphp

                    @foreach($orderedKeys as $key)
                        @if(isset($inspection->tires[$key]))
                            @php $val = $inspection->tires[$key]; @endphp
                            <tr class="check-row">
                                <td style="color: #52525b;">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </td>
                                <td class="status-box" style="text-transform: uppercase; font-weight: bold; color: {{ in_array(strtolower($val), ['low tread', 'fail', 'missing']) ? '#ef4444' : '#005d00' }};">
                                    {{ $val }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <div class="section-head uppercase">Visual Evidence & Damage Log</div>
    
    @forelse($inspection->items as $item)
        <div class="finding-card">
            <div class="finding-body">
                <table class="w-full">
                    <tr>
                        <td style="vertical-align: top; padding-right: 15px;">
                            <div class=" uppercase">Point of Interest: Zone {{ $item->zone_id }}</div>
                            <div class="uppercase">Condition: {{ $item->condition }}</div>
                            <div class="field-label uppercase">Inspector Notes</div>
                            <div style="font-size: 10px; color: #3f3f46; margin-top: 5px;">
                                {{ $item->notes ?? '-' }}
                            </div>
                        </td>
                        <td style="width: 250px; text-align: right;">
                            @if($item->temp_url)
                                <img src="{{ $item->temp_url }}" class="finding-photo">
                            @else
                                <div style="font-size: 7px; color: #a1a1aa; border: 1px solid #e4e4e7; padding: 30px; text-align: center;">NO IMAGE RECORDED</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @empty
        <div style="padding: 40px; text-align: center; color: #a1a1aa; font-weight: bold; border: 1px solid #e4e4e7; margin-top: 15px; border-radius: 4px;">
            NO ADVERSE FINDINGS REPORTED
        </div>
    @endforelse
    @if($inspection->general_notes)
    <div class="section-head uppercase">General Remarks</div>
    <div class="remarks-box">
        {{ $inspection->general_notes }}
    </div>
    @endif

    <div class="signature-section">
        <table class="w-full">
            <tr>
                <td style="width: 60%; vertical-align: top; padding-right: 20px;">
                    <div class="field-label uppercase">Acknowledgement & Legal Consent</div>
                    <div style="font-size: 9px; color: #52525b; text-align: justify; line-height: 1.3;">
                        I, the undersigned, hereby confirm that I have personally inspected the vehicle 
                        and agree that the details recorded in this report—including mileage, fuel levels, 
                        functional tests, and visual damage—are an accurate representation of the vehicle's 
                        condition at the time of this inspection.
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <div class="field-label uppercase">Renter's Name</div>
                        <div class="field-value" style="font-size: 14px; border-bottom: 1px solid #1a1a1a; display: inline-block; min-width: 200px;">
                            {{ $inspection->signee_name ?? $inspection->booking->customer->name }}
                        </div>
                        <div class="text-muted" style="margin-top: 4px;">PRINTED NAME</div>
                    </div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: top;">
                    <div class="field-label uppercase" style="text-align: right;">Digital Signature</div>
                    <div class="signature-box" style="float: right;">
                        @if($inspection->customer_signature)
                            {{-- Using S3 Temporary URL logic if needed, or direct path if local --}}
                            <img src="{{ Storage::disk('s3')->temporaryUrl($inspection->customer_signature, now()->addMinutes(15)) }}" class="signature-img">
                        @else
                            <div style="padding-top: 30px; color: #a1a1aa; font-size: 8px;">NO SIGNATURE ON FILE</div>
                        @endif
                    </div>
                    <div style="clear: both;"></div>
                    <div class="text-muted" style="margin-top: 5px; text-align: right;">
                        TIMESTAMP: {{ $inspection->created_at->format('M d, Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- <div class="footer">
        <table class="w-full uppercase">
            <tr>
                <td style="width: 50%;">Certified by: {{ auth()->user()->name ?? 'Technical Officer' }}</td>
                <td style="width: 50%; text-align: right;">{{ $inspection->booking->company->name ?? 'Fleet' }} Systems • Page 1 of 1</td>
            </tr>
        </table>
    </div> --}}

</body>
</html>