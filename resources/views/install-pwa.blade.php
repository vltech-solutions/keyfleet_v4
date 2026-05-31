<div id="pwa-install-section" class="hidden px-6 py-3 transition-opacity duration-500">
    <button 
        id="pwa-install-btn"
        type="button"
        class="hidden w-full inline-flex items-center justify-center gap-x-3 rounded-lg bg-primary-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 active:scale-95 transition-transform"
    >
        <x-heroicon-s-device-phone-mobile class="h-5 w-5 shrink-0" />
        <span>Install Keyfleet App</span>
    </button>

    <button 
        id="pwa-push-btn"
        type="button"
        class="hidden w-full flex items-center justify-center gap-2 rounded-lg bg-gray-800 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 active:scale-95 transition-transform"
    >
        <x-heroicon-s-bell class="h-5 w-5" />
        <span>Enable Notifications</span>
    </button>

    <div id="ios-instruction" class="hidden rounded-lg bg-gray-100 p-3 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
        <div class="flex items-center gap-x-2 mb-2">
            <x-heroicon-s-information-circle class="h-4 w-4 shrink-0 text-primary-500" />
            <span class="font-bold leading-none uppercase tracking-wider">Install Mobile App</span>
        </div>
        <p class="leading-relaxed">
            Tap the 
            <span class="inline-flex items-center px-1">
                <x-heroicon-o-share class="h-4 w-4" />
            </span> 
            icon and select <strong>"Add to Home Screen"</strong>.
        </p>
    </div>
</div>
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
    
    @if (($daysLeft == 0 && $hoursLeft <= 0) || $daysLeft < 0)
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
@elseif (($daysLeft == 0 && $hoursLeft <= 0) || $daysLeft < 0)
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


<script>
    (function() {
        const section = document.getElementById('pwa-install-section');
        const installBtn = document.getElementById('pwa-install-btn');
        const pushBtn = document.getElementById('pwa-push-btn');
        const iosInfo = document.getElementById('ios-instruction');
        let deferredPrompt;

        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

        // --- CHECK IF ALREADY SUBSCRIBED ---
        async function checkAndUpdatePushButton() {
            try {
                // Check if service worker is ready
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                
                console.log('Subscription status:', subscription ? 'Subscribed' : 'Not subscribed');
                console.log('Is standalone:', isStandalone);
                
                // Show/hide push button based on subscription status
                if (subscription) {
                    // Subscribed na -> hide button
                    pushBtn.classList.add('hidden');
                    console.log('Push button hidden - user is subscribed');
                } else {
                    // Hindi pa subscribed -> show button BUT only if standalone
                    if (isStandalone) {
                        pushBtn.classList.remove('hidden');
                        console.log('Push button shown - user not subscribed and in standalone mode');
                    } else {
                        pushBtn.classList.add('hidden');
                        console.log('Push button hidden - not in standalone mode');
                    }
                }
            } catch (error) {
                console.error('Error checking subscription:', error);
                // If error, hide button by default to be safe
                pushBtn.classList.add('hidden');
            }
        }

        // --- 1. INITIAL LOGIC ---
        // I-set muna lahat ng buttons as hidden by default
        installBtn.classList.add('hidden');
        pushBtn.classList.add('hidden');
        
        if (isStandalone) {
            section.classList.remove('hidden');
            // Check subscription status para malaman kung ipapakita ang push button
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.ready.then(() => {
                    checkAndUpdatePushButton();
                });
            }
        } else {
            if (isIOS && isSafari) {
                section.classList.remove('hidden');
                iosInfo.classList.remove('hidden');
            }
        }

        // --- 2. INSTALLATION LOGIC ---
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            section.classList.remove('hidden');
            installBtn.classList.remove('hidden');
            // Kapag nagpakita ang install button, itago muna ang push button
            pushBtn.classList.add('hidden');
        });

        installBtn.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                section.classList.add('hidden');
                // Pag na-install na, dapat magpakita na ang push button (kung hindi pa subscribed)
                if (isStandalone) {
                    setTimeout(() => {
                        checkAndUpdatePushButton();
                    }, 1000);
                }
            }
            deferredPrompt = null;
        });

        // --- 3. PUSH NOTIFICATION LOGIC ---
        pushBtn.addEventListener('click', async () => {
            try {
                // Ensure we are on HTTPS before proceeding
                if (window.location.protocol === 'http:' && window.location.hostname !== 'localhost') {
                    window.location.href = window.location.href.replace('http:', 'https:');
                    return;
                }

                const registration = await navigator.serviceWorker.ready;
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    alert('Please allow notifications to receive updates.');
                    return;
                }

                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('BO3PO4OTZNrgtxEg8XaxTeSJ6rQKQHxZPZmSulQXnzzxtdizPW8CgEXh2rxd0V1sl-83drvMILWjCn2UQ6sp4cE')
                });

                // Send to server
                const response = await fetch('/push-subscription', {
                    method: 'POST',
                    body: JSON.stringify(subscription),
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    alert('Notifications enabled!');
                    // After successful subscription, hide the button
                    pushBtn.classList.add('hidden');
                    console.log('Push button hidden after successful subscription');
                } else {
                    alert('Failed to save subscription. Please try again.');
                }
            } catch (error) {
                console.error('Push error:', error);
                alert('Failed to enable notifications. Please ensure you are using a secure connection.');
            }
        });

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
        }

        window.addEventListener('appinstalled', () => {
            section.classList.add('hidden');
            deferredPrompt = null;
            // Kapag na-install ang app, check ulit kung dapat magpakita ang push button
            setTimeout(() => {
                checkAndUpdatePushButton();
            }, 1000);
        });
        
        // Re-check when page becomes visible again
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && isStandalone) {
                checkAndUpdatePushButton();
            }
        });
    })();
</script>