 <section class="py-12 bg-[whitesmoke] mt-[50px]">
    <div class="mb-12 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Trusted by Rental Businesses</h2>
        <p class="mt-4 text-lg text-gray-600">Companies that rely on Keyfleet</p>
    </div>

    <div class="flex items-center justify-center w-full h-full mt-6 text-base marque-container">
        <div class="box-border flex items-center w-full p-4 mt-4 overflow-hidden font-light Marquee">
            <div class="flex Marquee-content animate-marquee hover:animate-pause">
                
                @for ($i = 0; $i < 3; $i++)
                    @foreach($companies as $company)
                        <div class="inline-flex items-center justify-center p-2 mx-2 transition-all ease-in-out Marquee-tag w-52 duration-900 hover:scale-110">
                            <img src="{{ Storage::url($company->avatar_url) }}" 
                                alt="{{ $company->name }}" 
                                class="transition rounded max-h-24 filter grayscale hover:grayscale-0" />
                        </div>
                    @endforeach
                @endfor

            </div>
        </div>
    </div>
</section>