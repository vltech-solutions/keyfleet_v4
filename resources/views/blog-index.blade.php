<x-layouts.guest>
    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Header -->
        <div class="max-w-7xl mx-auto text-center mb-16 px-4">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Blog</span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white mt-4">
                Insights & <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-400 dark:to-blue-400">Ideas</span>
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mt-4 leading-relaxed">
                Explore thoughtful articles, practical tips, and stories from the minds behind our work.
            </p>

            <!-- Search Bar (Optional - Uncomment to enable) -->
            {{-- <div class="max-w-xl mx-auto mt-8">
                <form method="GET" action="{{ route('blog.index') }}" class="flex items-center bg-white dark:bg-gray-800 rounded-full shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-transparent">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="w-full rounded-l-full py-3 px-6 focus:outline-none bg-transparent dark:text-white dark:placeholder-gray-400"
                        placeholder="Search blog posts..."
                    >
                    <button
                        type="submit"
                        class="bg-indigo-600 dark:bg-indigo-500 text-white px-6 py-3 rounded-r-full hover:bg-indigo-700 dark:hover:bg-indigo-600 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search
                    </button>
                </form>
            </div> --}}
        </div>

        <!-- Blog Grid -->
        <div class="max-w-7xl mx-auto grid gap-8 md:grid-cols-2 lg:grid-cols-3 px-4">
            @forelse ($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" 
                   class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden hover:-translate-y-2 border border-gray-100 dark:border-gray-700 flex flex-col">
                    
                    <!-- Image Container -->
                    <div class="relative overflow-hidden bg-gray-100 dark:bg-gray-700">
                        @if($post->banner)
                            <img
                                src="{{ asset('storage/' . $post->banner) }}"
                                alt="{{ $post->title }}"
                                class="w-full h-56 object-cover transition-transform duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                        @else
                            <div class="w-full h-56 flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/30 dark:to-blue-950/30">
                                <svg class="w-16 h-16 text-indigo-300 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Category Badge -->
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 text-xs font-medium text-white bg-indigo-600 dark:bg-indigo-500 rounded-full shadow-md">
                                {{ $post->category->name ?? 'Uncategorized' }}
                            </span>
                        </div>
                        
                        <!-- Reading Time Badge -->
                        <div class="absolute bottom-4 right-4 px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-full shadow-md">
                            {{ $post->reading_time ?? '5 min read' }}
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex-1">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2">
                                {{ $post->title }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm leading-relaxed line-clamp-3">
                                {{ Str::limit(strip_tags($post->content), 120) }}
                            </p>
                        </div>

                        <!-- Author Section -->
                        <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <img
                                src="{{ asset('storage/' . ($post->author->photo ?? 'default-avatar.png')) }}"
                                alt="{{ $post->author->name ?? 'Author' }}"
                                class="w-10 h-10 rounded-full object-cover border-2 border-indigo-100 dark:border-indigo-800"
                                loading="lazy"
                            >
                            <div class="flex-1 min-w-0">
                                <p class="text-gray-900 dark:text-white font-semibold text-sm truncate">
                                    {{ $post->author->name ?? 'Unknown Author' }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $post->published_at->format('M d, Y') }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No blog posts found.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Check back soon for new content.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($posts->hasPages())
            <div class="max-w-7xl mx-auto mt-12 px-4">
                <div class="flex justify-center">
                    {{ $posts->appends(['search' => request('search')])->links() }}
                </div>
            </div>
        @endif
    </section>
</x-layouts.guest>