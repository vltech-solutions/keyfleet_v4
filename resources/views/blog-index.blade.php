<x-layouts.guest>
  <section class="py-20 px-6">
    <div class="max-w-7xl mx-auto text-center mb-12 px-4">
      <h1 class="text-5xl font-extrabold text-gray-900 mb-4">Insights & Ideas</h1>
      <p class="text-lg text-gray-600 max-w-3xl mx-auto mb-6">
        Explore thoughtful articles, practical tips, and stories from the minds behind our work.
      </p>


      <!-- 🔍 Search bar -->
      <!-- <form method="GET" action="{{ route('blog.index') }}" class="max-w-xl mx-auto flex items-center">
        <input
          type="text"
          name="search"
          value="{{ request('search') }}"
          class="w-full border border-gray-300 rounded-l-xl py-2 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          placeholder="Search blog posts..."
        >
        <button
          type="submit"
          class="bg-indigo-600 text-white px-4 py-2 rounded-r-xl hover:bg-indigo-700"
        >
          Search
        </button>
      </form> -->
    </div>

    <div class="max-w-7xl mx-auto grid gap-12 md:grid-cols-2 lg:grid-cols-3 px-4">
      @forelse ($posts as $post)
        <a href="{{ route('blog.show', $post->slug) }}" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition flex flex-col">
          @if($post->banner)
            <img
              src="{{ asset('storage/' . $post->banner) }}"
              alt="{{ $post->title }}"
              class="w-full h-48 object-cover"
              loading="lazy"
            >
          @endif

          <div class="p-6 flex flex-col justify-between flex-1">
            <div>
              <p class="text-indigo-600 text-xs uppercase font-medium mb-1">{{ $post->category->name ?? 'Uncategorized' }}</p>
              <h2 class="text-xl font-semibold text-gray-900 group-hover:text-indigo-700 transition">{{ $post->title }}</h2>
              <p class="text-gray-600 mt-2 text-sm line-clamp-3">{{ Str::limit(strip_tags($post->content), 120) }}</p>
            </div>

            <div class="flex items-center gap-4 mt-6">
              <img
                src="{{ asset('storage/' . $post->author->photo) ?? 'https://via.placeholder.com/64' }}"
                alt="{{ $post->author->name }}"
                class="w-10 h-10 rounded-full object-cover border-2 border-indigo-600"
                loading="lazy"
              >
              <div>
                <p class="text-gray-900 font-semibold text-sm">{{ $post->author->name }}</p>
                <p class="text-gray-500 text-xs">{{ $post->published_at->format('F d, Y') }}</p>
              </div>
            </div>
          </div>
        </a>
      @empty
        <p class="text-center text-gray-500 col-span-full">No blog posts found.</p>
      @endforelse
    </div>

    <div class="mt-12">
      {{ $posts->appends(['search' => request('search')])->links() }}
    </div>
  </section>
</x-layouts.guest>
