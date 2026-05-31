@php
    $fuel = $record->gas ?? 0;
    $totalBars = 10;
    $activeBars = round(($fuel / 100) * $totalBars);
@endphp
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        
        /* Branded Header Section */
        .header-container { width: 100%; border-bottom: 2px solid {{ $company->primary_color ?? '#eee' }}; padding-bottom: 15px; margin-bottom: 20px; }
        .logo-cell { width: 50%; vertical-align: middle; }
        .company-info-cell { width: 50%; text-align: right; vertical-align: middle; }
        .company-logo { max-height: 70px; width: auto; }
        .company-name { font-size: 18px; font-weight: bold; color: {{ $company->primary_color ?? '#111' }}; margin: 0; text-transform: uppercase; }
        .company-sub-text { font-size: 9px; color: #666; margin: 2px 0; }

        /* Titles */
        .report-title { text-align: center; margin-bottom: 20px; }
        .report-title h2 { margin: 0; font-size: 16px; letter-spacing: 1px; }
        .section-title { 
            background: {{ $company->primary_color ?? '#f8f9fa' }}; 
            color: {{ $company->primary_color ? '#ffffff' : '#666666' }}; 
            padding: 6px 10px; 
            font-weight: bold; 
            margin-top: 20px; 
            text-transform: uppercase; 
            font-size: 10px; 
        }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #eee; padding: 10px; text-align: left; vertical-align: top; }
        th { background: #fbfbfb; color: #777; font-size: 9px; text-transform: uppercase; }

        .label { font-size: 9px; color: #999; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .value { font-size: 11px; font-weight: bold; color: #333; }
        .plate-no { font-family: monospace; background: #f0f0f0; padding: 2px 5px; border-radius: 3px; border: 1px solid #ddd; }
        
        .photo { width: 100%; max-width: 180px; height: auto; border-radius: 6px; border: 1px solid #eee; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .pickup { background: #fef3c7; color: #92400e; }
        .return { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <table class="header-container" style="border:none;">
        <tr>
            <td class="logo-cell" style="border:none;">
                @if($companyLogo && file_exists($companyLogo))
                    <img src="{{ $companyLogo }}" class="company-logo">
                @else
                    <div style="font-size: 24px; font-weight: bold; color: {{ $company->primary_color ?? '#111' }};">
                        {{ $company->name }}
                    </div>
                @endif
            </td>
            <td class="company-info-cell" style="border:none;">
                <p class="company-name">{{ $company->name }}</p>
                <p class="company-sub-text">{{ $company->address }}</p>
                <p class="company-sub-text">Contact: {{ $company->contacts }}</p>
            </td>
        </tr>
    </table>

    <div class="report-title">
        <h2>VEHICLE INSPECTION REPORT</h2>
        <p style="margin: 5px 0; color: #888; font-size: 10px;">Booking Reference: #{{ $booking->id }}</p>
    </div>

    <div class="section-title">Booking Information</div>
    <table style="margin-top: 0;">
        <tr>
            <td width="25%">
                <span class="label">Renter</span>
                <span class="value">{{ $booking->renter_name ?? 'Walk-in' }}</span>
            </td>
            <td width="35%">
                <span class="label">Vehicle</span>
                <span class="value">{{ $booking->car->brand }} {{ $booking->car->model }} ({{ $booking->car->year }})</span>
            </td>
            <td width="15%">
                <span class="label">Plate Number</span>
                <span class="value plate-no">{{ $booking->car->plate_number }}</span>
            </td>
            <td width="25%">
                <span class="label">Duration</span>
                <span class="value">
                    {{ \Carbon\Carbon::parse($booking->start_datetime)->format('M d') }} - {{ \Carbon\Carbon::parse($booking->end_datetime)->format('M d, Y') }}
                    <br><small style="color: #2563eb;">{{ \Carbon\Carbon::parse($booking->start_datetime)->diffInDays(\Carbon\Carbon::parse($booking->end_datetime)) }} Days Rental</small>
                </span>
            </td>
        </tr>
    </table>

    <div class="section-title">Inspection Overview</div>
    <table style="margin-top: 0;">
        <tr>
            <td>
                <span class="label">Type</span>
                <span class="badge {{ $record->type }}">{{ strtoupper($record->type) }}</span>
            </td>
            <td>
                <span class="label">Odometer</span>
                <span class="value">{{ number_format($record->odo, 0) }} KM</span>
            </td>
           
            <td>
                <span class="label">Date Inspected</span>
                <span class="value">{{ date('F d, Y h:i A', strtotime($record->created_at)) }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Autosweep Balance</span>
                <span class="value">{{ number_format($record->autosweep, 2) }}</span>
            </td>
            <td>
                <span class="label">Easytrip Balance</span>
                <span class="value">{{ number_format($record->easytrip, 2) }}</span>
            </td>
            <td colspan="2">
                <span class="label">Inspected By</span>
                <span class="value">{{ $record->inspected_by ?? 'Authorized Personnel' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="label">Fuel Level</span>

                <div style="margin-top:4px;">
                    <!-- Percentage -->
                    <div style="font-size:11px; font-weight:bold; margin-bottom:3px;">
                        {{ $fuel }}%
                    </div>

                    <!-- Bars -->
                    <table cellpadding="0" cellspacing="2">
                        <tr>
                            @for ($i = 1; $i <= $totalBars; $i++)
                                @php
                                    if ($i > $activeBars) {
                                        $color = '#E5E7EB'; // gray
                                    } elseif ($i <= 2) {
                                        $color = '#EF4444'; // red
                                    } elseif ($i <= 5) {
                                        $color = '#F59E0B'; // amber
                                    } else {
                                        $color = '#10B981'; // green
                                    }
                                @endphp

                                <td style="
                                    width:14px;
                                    height:6px;
                                    background-color: {{ $color }};
                                    border-radius:2px;
                                "></td>
                            @endfor
                        </tr>
                    </table>

                    <!-- E / F -->
                    <div style="font-size:9px; color:#6B7280; margin-top:2px;">
                        E <span style="float:right;">F</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Reported Issues & Damages</div>
    @if($items->count() > 0)
        <table style="margin-top: 0;">
            <thead>
                <tr>
                    <th style="width: 25%;">Item Name</th>
                    <th style="width: 45%;">Inspector Remarks</th>
                    <th style="width: 30%;">Evidence Photo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="font-weight: bold;">{{ $item->checklistItem->item }}</td>
                    <td>{{ $item->remarks ?? 'No remarks provided.' }}</td>
                    <td style="text-align: center;">
                        @if($item->base64_image)
                            <img src="{{ $item->base64_image }}" class="photo">
                        @else
                            <span style="color: #ccc; font-style: italic; font-size: 8px;">No photo evidence</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 20px; text-align: center; border: 1px dashed #ccc; margin-top: 10px; border-radius: 8px;">
            <p style="color: #166534; font-weight: bold; margin: 0;">No issues recorded. Unit is in good condition.</p>
        </div>
    @endif

    <div style="margin-top: 60px;">
        <table style="border: none;">
            <tr>
                <td style="border: none; text-align: center; width: 50%;">
                    <div style="margin-bottom: 5px;">
                        {{ $booking->renter_name }}
                    </div>
                    <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto; padding-top: 5px; font-weight: bold;">
                        Customer Signature
                    </div>
                </td>
                <td style="border: none; text-align: center; width: 50%;">
                    <div style="margin-bottom: 5px;">
                        {{ $record->inspected_by }}
                    </div>
                    <div style="border-top: 1px solid #333; width: 200px; margin: 0 auto; padding-top: 5px; font-weight: bold;">
                        Authorized Inspector
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="position: fixed; bottom: -30px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 9px; color: #aaa;">
        Generated by {{ $company->name }} System - {{ date('Y-m-d H:i:s') }}
    </div>
</body>
</html>