@php
    $feedback = $this->voucherFeedback;
@endphp

@if ($feedback)
    <div
        @class([
            'text-sm mt-2 px-3 py-2 rounded-lg',
            'bg-red-100 text-red-800' => $feedback['type'] === 'error',
            'bg-green-100 text-green-800' => $feedback['type'] === 'success',
        ])
    >
        {{ $feedback['message'] }}
    </div>
@endif
