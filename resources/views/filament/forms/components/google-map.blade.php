<div x-data="{
    state: $wire.entangle('data.active_map_address'),
    map: null,
    marker: null,
    geocoder: null,

    init() {
        // Initialize Google Maps
        this.geocoder = new google.maps.Geocoder();
        
        const defaultLocation = { lat: 14.5995, lng: 120.9842 }; // Default to Manila/PH or your area
        
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

        // Watch for changes in the 'active_map_address' state
        this.$watch('state', value => {
            if (value) {
                this.updateMap(value);
            }
        });
    },

    updateMap(address) {
        this.geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK') {
                const location = results[0].geometry.location;
                this.map.setCenter(location);
                this.marker.setPosition(location);
            }
        });
    }
}" 
x-init="init()" 
wire:ignore>
    <div x-ref="map" style="height: 400px; width: 100%; border-radius: 12px; border: 1px solid #e5e7eb;" class="shadow-sm"></div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}"></script>
</div>