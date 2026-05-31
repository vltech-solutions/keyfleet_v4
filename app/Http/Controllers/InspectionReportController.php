<?php

namespace App\Http\Controllers;

use App\Models\BookingInspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InspectionReportController extends Controller
{
    public function download(BookingInspection $inspection)
    {
        // $inspection->load(['booking.car', 'items', 'booking.customer', 'booking.company']);

//         // Ensure S3 URLs are generated
//         $inspection->items->each(function ($item) {
//             if ($item->photo_path) {
//                 $item->temp_url = Storage::disk('s3')->temporaryUrl($item->photo_path, now()->addMinutes(15));
//             }
//         });

//         $pdf = Pdf::loadView('reports.inspection-pdf', [
//             'inspection' => $inspection,
//         ]);

//         $pdf->setOptions([
//             'isRemoteEnabled' => true,
//             'isHtml5ParserEnabled' => true,
//             'isJavascriptEnabled' => true, // Keep this for the auto-print script if they open the file later
//         ]);

//         // Force the browser to download the file instead of opening it
//         $filename = "Inspection_Report_{$inspection->booking->id}.pdf";
        
//         return $pdf->download($filename);
      
      // Check if the signature is valid (Automatic via middleware, but good to be sure)
    if (!request()->hasValidSignature()) {
        abort(403);
    }

    $inspection->load(['booking.car', 'items', 'booking.customer', 'booking.company']);

    // Process S3 URLs
    $inspection->items->each(function ($item) {
        if ($item->photo_path) {
            $item->temp_url = Storage::disk('s3')->temporaryUrl($item->photo_path, now()->addMinutes(15));
        }
    });

    $pdf = Pdf::loadView('reports.inspection-pdf', [
        'inspection' => $inspection,
    ])->setOptions([
        'isRemoteEnabled' => true,
        'isHtml5ParserEnabled' => true,
    ]);

    // return stream so the iframe can display it
    return $pdf->stream("Inspection_{$inspection->id}.pdf");
    }
}