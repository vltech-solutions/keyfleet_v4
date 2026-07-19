<x-layouts.guest>
    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Header -->
        <div class="max-w-7xl mx-auto text-center mb-16 px-4">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Testimonials</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white mt-4">
                What Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-400 dark:to-blue-400">Customers Say</span>
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mt-4 leading-relaxed">
                Real feedback from real businesses using Keyfleet to manage their fleets effortlessly.
            </p>
        </div>

        <!-- Stats Bar -->
        <div class="max-w-7xl mx-auto mb-12 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $testimonials->count() }}+</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Testimonials</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">4.9/5</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Average Rating</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">100+</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Happy Customers</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">98%</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Would Recommend</div>
                </div>
            </div>
        </div>

        <!-- Testimonials Grid -->
        <div class="max-w-7xl mx-auto grid gap-8 md:grid-cols-2 lg:grid-cols-3 px-4">
            @forelse ($testimonials as $testimonial)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 p-8 flex flex-col hover:-translate-y-1 group">
                    <!-- Stars -->
                    <div class="flex items-center gap-1 mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= ($testimonial->rating ?? 5) ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600 fill-current' }}" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>

                    <!-- Quote -->
                    <div class="flex-1 mb-6">
                        <svg class="w-8 h-8 text-indigo-200 dark:text-indigo-800 mb-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 1.986c-3.535.963-6.316 3.441-7.713 6.986h4.718v9.028h-6.983zm-10.017 0v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 1.986c-3.535.963-6.316 3.441-7.713 6.986h4.718v9.028h-6.983z"/>
                        </svg>
                        <p class="text-gray-700 dark:text-gray-300 text-base leading-relaxed">&ldquo;{{ $testimonial->quote }}&rdquo;</p>
                    </div>

                    <!-- Author -->
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <img
                            src="{{ $testimonial->photo ? asset('storage/' . $testimonial->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name) . '&background=4F46E5&color=fff&size=64' }}"
                            alt="{{ $testimonial->name }} photo"
                            class="w-14 h-14 rounded-full object-cover border-2 border-indigo-100 dark:border-indigo-800"
                            loading="lazy"
                        >
                        <div>
                            <p class="text-gray-900 dark:text-white font-semibold">{{ $testimonial->name }}</p>
                            <p class="text-indigo-600 dark:text-indigo-400 text-sm font-medium">{{ $testimonial->company }}</p>
                            @if($testimonial->position)
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $testimonial->position }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No testimonials available yet.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Check back soon for feedback from our customers.</p>
                </div>
            @endforelse
        </div>

        <!-- Call to Action -->
        <div class="max-w-4xl mx-auto mt-16 px-4 text-center">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-700 dark:to-blue-700 rounded-3xl p-8 md:p-12 text-white shadow-xl">
                <h3 class="text-2xl font-bold mb-2">Ready to Join Our Happy Customers?</h3>
                <p class="text-indigo-100 dark:text-indigo-200 mb-6">Start your free trial today and see why businesses love Keyfleet.</p>
                <a href="{{ route('tenant.register') }}" 
                   class="inline-block px-8 py-3 bg-white dark:bg-gray-100 text-indigo-600 dark:text-indigo-700 font-semibold rounded-full hover:bg-gray-50 dark:hover:bg-white hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                    Start Free Trial
                </a>
            </div>
        </div>
    </section>
</x-layouts.guest>