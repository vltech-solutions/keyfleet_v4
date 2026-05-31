<div class="w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
    <template x-if="$wire.get('data.website.map_url')">
        <iframe 
            width="100%" 
            height="300" 
            style="border:0" 
            loading="lazy" 
            allowfullscreen 
            :src="$wire.get('data.website.map_url')">
        </iframe>
    </template>
</div>