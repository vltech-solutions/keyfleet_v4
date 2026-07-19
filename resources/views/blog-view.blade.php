<x-layouts.guest>
    <section class="py-16 px-6 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Back Button -->
            <a href="{{ route('blog.index') }}" 
               class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-6 group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Blog
            </a>

            <!-- Category Badge -->
            <p class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold uppercase tracking-wider mb-3">
                {{ $post->category->name ?? 'Uncategorized' }}
            </p>

            <!-- Title -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 dark:text-white leading-tight mb-6">
                {{ $post->title }}
            </h1>

            <!-- Author & Metadata -->
            <div class="flex flex-wrap items-center gap-4 mb-8 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('storage/' . ($post->author->photo ?? 'default-avatar.png')) }}"
                        alt="{{ $post->author->name ?? 'Author' }}"
                        class="w-12 h-12 rounded-full object-cover border-2 border-indigo-100 dark:border-indigo-800"
                        loading="lazy"
                    >
                    <div>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ $post->author->name ?? 'Unknown Author' }}</p>
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ $post->published_at->format('F d, Y') }}</span>
                            <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                            @if($post->reading_time)
                                <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                <span>{{ $post->reading_time }} min read</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Share Button -->
                <div class="ml-auto flex items-center gap-2">
                    <button onclick="window.print()" 
                            class="p-2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                    </button>
                    <button onclick="sharePost()" 
                            class="p-2 text-gray-400 dark:text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Banner Image -->
            @if($post->banner)
                <div class="relative overflow-hidden rounded-2xl mb-8 shadow-lg">
                    <img
                        src="{{ asset('storage/' . $post->banner) }}"
                        alt="{{ $post->title }}"
                        class="w-full h-auto max-h-[500px] object-cover"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 dark:from-black/30 to-transparent"></div>
                </div>
            @endif

            <!-- Content -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 md:p-8 lg:p-10">
                <div class="prose prose-lg max-w-none prose-indigo dark:prose-invert prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white prose-a:text-indigo-600 dark:prose-a:text-indigo-400 prose-a:no-underline hover:prose-a:underline prose-img:rounded-xl prose-img:shadow-md">
                    {!! $post->content !!}
                </div>
            </div>

            <!-- Tags (if available) -->
            @if($post->tags && $post->tags->count() > 0)
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tags:</span>
                    @foreach($post->tags as $tag)
                        <span class="px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            #{{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <script>
        function sharePost() {
            if (navigator.share) {
                navigator.share({
                    title: '{{ $post->title }}',
                    text: 'Check out this article: {{ $post->title }}',
                    url: window.location.href
                }).catch(() => {});
            } else {
                // Fallback: Copy link to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link copied to clipboard!');
                }).catch(() => {
                    // If clipboard fails, prompt the user
                    prompt('Copy this link:', window.location.href);
                });
            }
        }
    </script>
</x-layouts.guest>