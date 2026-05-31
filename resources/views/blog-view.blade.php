<x-layouts.guest>
  <section class="py-20 px-6">
    <div class="max-w-3xl mx-auto px-4">
      <p class="text-indigo-600 text-sm font-medium mb-2">
        {{ $post->category->name ?? 'Uncategorized' }}
      </p>

      <h1 class="text-4xl font-extrabold text-gray-900 mb-4">{{ $post->title }}</h1>

      <div class="flex items-center gap-4 mb-6">
        <img
          src="{{ asset('storage/' . $post->author->photo) ?? 'https://via.placeholder.com/64' }}"
          alt="{{ $post->author->name }}"
          class="w-12 h-12 rounded-full object-cover border-2 border-indigo-600"
          loading="lazy"
        >
        <div>
          <p class="text-gray-900 font-semibold">{{ $post->author->name }}</p>
          <p class="text-gray-500 text-sm">{{ $post->published_at->format('F d, Y') }}</p>
        </div>
      </div>

      @if($post->banner)
        <img
          src="{{ asset('storage/' . $post->banner) }}"
          alt="{{ $post->title }}"
          class="rounded-xl mb-8 w-full h-auto"
          loading="lazy"
        >
      @endif

      <div class="prose max-w-none prose-indigo">
        {!! $post->content !!}
      </div>
    </div>
  </section>
</x-layouts.guest>
