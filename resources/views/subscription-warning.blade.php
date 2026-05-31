@php
    use Filament\Facades\Filament;
    use App\Models\Company;
    use Carbon\Carbon;

    $company = Company::find(Filament::getTenant()?->id);
    $daysLeft = $company?->subscriptionDaysLeft();
    $endsAt = $company?->subscription?->ends_at;
    $tenantId = Filament::getTenant()?->id;

    $hoursLeft = null;
    if ($daysLeft === 0 && $endsAt) {
        $endsAtCarbon = Carbon::parse($endsAt);
        $now = Carbon::now();
        $hoursLeft = round($now->diffInHours($endsAtCarbon, false));
    }
@endphp
@if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 10)
    
    @if ($daysLeft == 0 && $hoursLeft <= 0)
        <div class="p-3 mx-3 mt-4 mb-2 text-sm text-red-900 bg-red-100 rounded-lg shadow dark:bg-red-900 dark:text-gray-800">
            <div class="font-semibold">
                ❌ Your subscription has expired
            </div>
            <div class="mt-1 text-xs leading-tight">
                Your access is limited until you renew your subscription.
                If you wish to continue using KeyFleet, feel free to reach out and we’ll help you reactivate.
                <br>
                <a href="https://www.facebook.com/profile.php?id=61577048618076" target="_blank"
                   class="text-blue-600 underline hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-400">
                    Contact Us to Renew
                </a>
            </div>
        </div>
    @else

      <div class="p-3 mx-3 mt-4 mb-2 text-sm text-yellow-900 bg-yellow-100 rounded-lg shadow dark:bg-yellow-800 dark:text-gray-800">
          <div class="font-semibold">
          
              @if($daysLeft > 0)
                ⏳ {{ $daysLeft }} day(s) left on your subscription
              @else 
                @if($hoursLeft !== null && $hoursLeft > 0)
                  ⏳ {{ $hoursLeft }} hour(s) left on your subscription
                @endif
              @endif
          </div>
          <div class="mt-1 text-xs leading-tight">
              Please renew soon to avoid service interruption.
              <a href="https://www.facebook.com/profile.php?id=61577048618076" target="_blank"
                 class="text-blue-600 underline hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-400">
                  Contact Us
              </a>
          </div>
      </div>
    
    @endif
@elseif($daysLeft == 0 && $hoursLeft <= 0)
    <div class="p-3 mx-3 mt-4 mb-2 text-sm text-red-900 bg-red-100 rounded-lg shadow dark:bg-red-900 dark:text-gray-800">
        <div class="font-semibold">
            ❌ Your subscription has expired
        </div>
        <div class="mt-1 text-xs leading-tight">
            Your access is limited until you renew your subscription.
            If you wish to continue using KeyFleet, feel free to reach out and we’ll help you reactivate.
            <br>
            <a href="https://www.facebook.com/profile.php?id=61577048618076" target="_blank"
               class="text-blue-600 underline hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-400">
                Contact Us to Renew
            </a>
        </div>
    </div>
@endif

{{-- <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}"></script> --}}
<script>

document.addEventListener('livewire:init', () => {
    Livewire.on('trigger-download', (fileName, data) => {
        console.log(fileName);
        if (!fileName) {
            console.error('QR download failed: Missing data.', { fileName, data });
            return;
        }

        try {
            const link = document.createElement('a');
            link.href = fileName.data;
            link.download = fileName.fileName;

            // iOS Safari fallback (opens image instead of forcing download)
            if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
                window.open(data, '_blank');
            } else {
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        } catch (e) {
            console.error('QR download error:', e);
        }
    });
});

window.addEventListener('copy-to-clipboard', event => {
    const text = event.detail[0].value;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        // Modern browsers
        navigator.clipboard.writeText(text)
            .then(() => console.log('Copied to clipboard:', text))
            .catch(() => fallbackCopy(text));
    } else {
        // Fallback for mobile/iOS
        fallbackCopy(text);
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        try {
            const successful = document.execCommand('copy');
            console.log(successful ? 'Copied (fallback)' : 'Failed to copy');
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }

        document.body.removeChild(textarea);
    }
});
</script>
