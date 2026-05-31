@php
    $hasDamageReport = !empty($remarks) || !empty($photo);
@endphp

<div class="flex items-center px-2">
    @if($state)
        {{-- SUCCESS: READY / OK --}}
        <!-- <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/80 text-emerald-700 border border-emerald-200 shadow-sm dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20">
            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span class="text-[11px] font-bold uppercase tracking-wider">Ready</span>
        </div> -->

    @elseif($hasDamageReport)
        <button 
            type="button"
            wire:click="mountAction('viewDamage', { itemId: {{ $itemId }} })"
            class="group inline-flex items-center gap-1.5 text-red-600 transition-all duration-200 hover:text-red-500 active:opacity-70 dark:text-red-400"
        >
            With Damage
        </button>
    @endif
</div>