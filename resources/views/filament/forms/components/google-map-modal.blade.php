<div x-data="{
    address: '{{ $address }}',
    map: null,
    marker: null,
    geocoder: null,

    init() {
        // We use an interval to check when the modal is actually 'visible' 
        // (meaning it has a width > 0)
        let checkExist = setInterval(() => {
            if (this.$refs.map.clientWidth > 0) {
                clearInterval(checkExist);
                this.initializeMap();
            }
        }, 100);
    },

    initializeMap() {
        if (typeof google === 'undefined') {
            this.$refs.map.innerHTML = '<p class=\'text-red-500\'>Google Maps Script not loaded.</p>';
            return;
        }

        this.geocoder = new google.maps.Geocoder();
        
        // Use the exact logic that worked for you
        const defaultLocation = { lat: 14.5995, lng: 120.9842 }; 
        
        this.map = new google.maps.Map(this.$refs.map, {
            center: defaultLocation,
            zoom: 15,
            disableDefaultUI: true,
            zoomControl: true,
        });

        this.marker = new google.maps.Marker({
            map: this.map,
            position: defaultLocation,
            animation: google.maps.Animation.DROP
        });

        if (this.address) {
            this.updateMap(this.address);
        }
    },

    updateMap(address) {
        this.geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK') {
                const location = results[0].geometry.location;
                this.map.setCenter(location);
                this.marker.setPosition(location);
                
                // Final safety trigger
                google.maps.event.trigger(this.map, 'resize');
            } else {
                console.error('Geocode failed: ' + status);
            }
        });
    }
}" 
x-init="init()" 
wire:ignore>
    {{-- Hardcode the height/width style to ensure it has space --}}
    <div x-ref="map" style="height: 450px; width: 100%; min-height: 450px; border-radius: 12px; border: 1px solid #e5e7eb;" class="shadow-sm bg-gray-50 flex items-center justify-center">
        <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-2"></div>
            <p class="text-gray-400 text-sm">Rendering Map...</p>
        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}"></script>
</div>