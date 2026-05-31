<script>
    window.addEventListener('trigger-pdf-print', event => {
        // Livewire v3 sometimes nests data under event.detail[0] 
        // depending on how dispatch() was called.
        const data = event.detail.pdf || event.detail[0]?.pdf;
        
        if (!data) {
            console.error('PDF data not found in event', event.detail);
            return;
        }

        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = data;
        
        document.body.appendChild(iframe);
        
        iframe.onload = function() {
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 3000);
            }, 1000); // Increased delay for mobile/PWA rendering
        };
    });
</script>

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
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            // navigator.serviceWorker.register('/sw.js');
          navigator.serviceWorker.register('/sw.js')
          .then(() => console.log('SW registered'))
          .catch(err => console.log('SW failed', err));
        });
    }
  
  	async function subscribeToPush() {
      const registration = await navigator.serviceWorker.ready;

      // Request permission from the user
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') return;

      // Create subscription
      const subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array('BO3PO4OTZNrgtxEg8XaxTeSJ6rQKQHxZPZmSulQXnzzxtdizPW8CgEXh2rxd0V1sl-83drvMILWjCn2UQ6sp4cE')
      });

      // Send to server
      await fetch('/push-subscription', {
          method: 'POST',
          body: JSON.stringify(subscription),
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
      });
}

// Helper for VAPID key
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}
  
</script>