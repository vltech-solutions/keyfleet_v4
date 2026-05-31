<div 
    x-data="{
        map: null,
        marker: null,
        geocoder: null,
        address: $wire.entangle('{{ $address }}'),

        init() {
            this.$watch('address', (val) => this.updateMap(val));
            
            // Initial load if address exists
            if (this.address) {
                setTimeout(() => this.initializeMap(), 500);
            }
        },

        initializeMap() {
            if (typeof google === 'undefined') return;
            
            this.geocoder = new google.maps.Geocoder();
            const defaultLoc = { lat: 14.5995, lng: 120.9842 };

            this.map = new google.maps.Map(this.$refs.mapDiv, {
                center: defaultLoc,
                zoom: 15,
                disableDefaultUI: true,
                zoomControl: true,
            });

            this.marker = new google.maps.Marker({
                map: this.map,
                position: defaultLoc,
                animation: google.maps.Animation.DROP
            });

            this.updateMap(this.address);
        },

        updateMap(val) {
            if (!val || !this.geocoder) {
                if (!this.map) this.initializeMap();
                return;
            }

            this.geocoder.geocode({ address: val }, (results, status) => {
                if (status === 'OK') {
                    const loc = results[0].geometry.location;
                    this.map.setCenter(loc);
                    this.marker.setPosition(loc);
                }
            });
        }
    }"
    class="w-full"
>
    <div x-ref="mapDiv" style="height: 200px; width: 100%;" class="border shadow-inner rounded-xl bg-gray-50"></div>
</div>