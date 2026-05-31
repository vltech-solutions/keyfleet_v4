<x-layouts.guest>
  <section class="py-20 px-6">
    <div class="max-w-7xl mx-auto text-center mb-16 px-4">
      <h1 class="text-5xl font-extrabold text-gray-900 mb-4">What Our Customers Say</h1>
      <p class="text-lg text-gray-600 max-w-3xl mx-auto">
        Real feedback from real businesses using Keyfleet to manage their fleets effortlessly.
      </p>
    </div>

    <div class="max-w-7xl mx-auto grid gap-12 md:grid-cols-2 lg:grid-cols-3 px-4">
      @forelse ($testimonials as $testimonial)
        <div class="bg-white rounded-2xl shadow-lg p-8 flex flex-col justify-between">
          <div class="mb-6">
            <p class="text-gray-700 text-lg italic">&ldquo;{{ $testimonial->quote }}&rdquo;</p>
          </div>

          <div class="flex items-center gap-4">
            <img
              src="{{ $testimonial->photo ? asset('storage/' . $testimonial->photo) : 'https://via.placeholder.com/64' }}"
              alt="{{ $testimonial->name }} photo"
              class="w-16 h-16 rounded-full object-cover border-2 border-indigo-600"
              loading="lazy"
            >
            <div>
              <p class="text-gray-900 font-semibold text-lg">{{ $testimonial->name }}</p>
              <p class="text-indigo-600 text-sm font-medium">{{ $testimonial->company }}</p>
            </div>
          </div>
        </div>
      @empty
        <p class="text-center text-gray-500 mt-12">No testimonials available yet.</p>
      @endforelse
    </div>
  </section>
</x-layouts.guest>
