@php
    echo cache()->rememberForever('pdf_css', fn () => file_get_contents(public_path('css/pdf.min.css')));
@endphp