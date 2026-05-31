<div style="--col-span-default: 1 / -1;" class="col-[--col-span-default]">
    <section x-data="{
        isCollapsed:  true ,
        }" x-on:collapse-section.window="if ($event.detail.id == $el.id) isCollapsed = true" x-on:expand="isCollapsed = false" x-on:open-section.window="if ($event.detail.id == $el.id) isCollapsed = false" x-on:toggle-section.window="if ($event.detail.id == $el.id) isCollapsed = ! isCollapsed" x-bind:class="isCollapsed &amp;&amp; 'fi-collapsed'" class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" id="booking-details">
        <!--[if BLOCK]><![endif]-->        
        <header x-on:click="isCollapsed = ! isCollapsed" class="fi-section-header flex flex-col gap-3 cursor-pointer px-6 py-4">
            <div class="flex items-center gap-3">
                <!--[if BLOCK]><![endif]-->                    
                <svg class="fi-section-header-icon self-start text-gray-400 dark:text-gray-500 fi-color-{$iconColor} h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"></path>
                </svg>
                <!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]-->                    
                <div class="grid flex-1 gap-y-1">
                    <!--[if BLOCK]><![endif]-->                            
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Booking Details
                    </h3>
                    <!--[if ENDBLOCK]><![endif]-->
                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                </div>
                <!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]-->                    
                <button style="--c-300:var(--gray-300);--c-400:var(--gray-400);--c-500:var(--gray-500);--c-600:var(--gray-600);" class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 -m-2 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 fi-color-gray rotate-180" type="button" wire:loading.attr="disabled" x-on:click.stop="isCollapsed = ! isCollapsed" x-bind:class="{ 'rotate-180': ! isCollapsed }">
                    <svg class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <!--[if ENDBLOCK]><![endif]-->
            </div>
            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
        </header>
        <!--[if ENDBLOCK]><![endif]-->
        <div x-bind:aria-expanded="(! isCollapsed).toString()" x-bind:class="{
            'invisible absolute h-0 overflow-hidden border-none': isCollapsed,
            }" class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10" aria-expanded="true">
            <div class="fi-section-content p-6">
                <dl>
                    <div style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-lg: repeat(3, minmax(0, 1fr));" class="grid grid-cols-[--cols-default] lg:grid-cols-[--cols-lg] fi-fo-component-ctn gap-6">
                        <!--[if BLOCK]><![endif]-->            
                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                            <div>
                                <dl>
                                    <div style="--cols-default: repeat(1, minmax(0, 1fr)); --cols-md: repeat(1, minmax(0, 1fr));" class="grid grid-cols-[--cols-default] md:grid-cols-[--cols-md] fi-fo-component-ctn gap-6">
                                        <!--[if BLOCK]><![endif]-->            
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Rented Car
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-image flex items-center gap-x-2.5  flex justify-center">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="flex flex-wrap gap-1.5">
                                                                    <!--[if BLOCK]><![endif]-->                    <img src="{{ \Storage::url($booking->car->image) }}" style="height: 8rem;" class="max-w-none object-cover object-center ring-white dark:ring-gray-900">

                                                                    
                                                                    <!--[if ENDBLOCK]><![endif]-->
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </dl>
                            </div>
                        </div>
                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                            <div>
                                <dl>
                                    <div style="--cols-default: repeat(2, minmax(0, 1fr)); --cols-md: repeat(2, minmax(0, 1fr));" class="grid grid-cols-[--cols-default] md:grid-cols-[--cols-md] fi-fo-component-ctn gap-6">
                                        <!--[if BLOCK]><![endif]-->            
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Booking
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5  ">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-gray-950 dark:text-white font-bold " style="">
                                                                                        {{-- Toyota Innova 2024 (White) --}}
                                                                                        {{ $booking->car->brand.' '.$booking->car->model.' '.$booking->car->year.' (' . $booking->car->color . ')' }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Plate Number
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex gap-1.5 flex-wrap ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    <span style="" class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 bg-gray-50 text-gray-600 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20 fi-color-gray">
                                                                                <span class="grid">
                                                                                <span class="truncate">
                                                                                {{ $booking->car->plate_number }}
                                                                                </span>
                                                                                </span>
                                                                                </span>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Pickup
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5  ">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-gray-950 dark:text-white  " style="">
                                                                                        {{ date('M d, Y h:i A',strtotime($booking->start_datetime)) }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Return
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5  ">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-gray-950 dark:text-white  " style="">
                                                                                        {{ date('M d, Y h:i A',strtotime($booking->end_datetime)) }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </dl>
                            </div>
                        </div>
                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                            <div>
                                <dl>
                                    <div style="--cols-default: repeat(2, minmax(0, 1fr)); --cols-md: repeat(2, minmax(0, 1fr));" class="grid grid-cols-[--cols-default] md:grid-cols-[--cols-md] fi-fo-component-ctn gap-6">
                                        <!--[if BLOCK]><![endif]-->            
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Renter Name
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5  ">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-gray-950 dark:text-white font-bold " style="">
                                                                                        {{ $booking->renter_name }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Total Due
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5  ">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-gray-950 dark:text-white  " style="">
                                                                                        ₱{{ number_format($booking->total_due,2) }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Paid Amount
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5  ">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-gray-950 dark:text-white  " style="">
                                                                                        ₱{{ number_format($booking->paid_amount,2) }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="--col-span-default: span 1 / span 1;" class="col-[--col-span-default]">
                                            <div class="fi-in-entry-wrp">
                                                <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                <div class="grid gap-y-2">
                                                    <!--[if BLOCK]><![endif]-->            
                                                    <div class="flex items-center gap-x-3 justify-between ">
                                                        <!--[if BLOCK]><![endif]-->                    
                                                        <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
                                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                            Balance
                                                            </span>
                                                        </dt>
                                                        <!--[if ENDBLOCK]><![endif]-->
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                    <!--[if ENDBLOCK]><![endif]-->
                                                    <div class="grid auto-cols-fr gap-y-2">
                                                        <dd class="">
                                                            <!--[if BLOCK]><![endif]-->                    
                                                            <div class="fi-in-text w-full">
                                                                <!--[if BLOCK]><![endif]-->            
                                                                <div class="fi-in-affixes flex">
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex ">
                                                                            <!--[if BLOCK]><![endif]-->                            
                                                                            <div class="flex max-w-max" style="">
                                                                                <!--[if BLOCK]><![endif]-->                                    
                                                                                <div class="fi-in-text-item inline-flex items-center gap-1.5 fi-color-custom fi-color-danger">
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                    <div class="text-sm leading-6 text-custom-600 dark:text-custom-400  " style="--c-400:var(--danger-400);--c-600:var(--danger-600);">
                                                                                        ₱{{ number_format($booking->balance,2) }}
                                                                                    </div>
                                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                                </div>
                                                                                <!--[if ENDBLOCK]><![endif]-->
                                                                            </div>
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if ENDBLOCK]><![endif]-->
                                                                            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                        </div>
                                                                    </div>
                                                                    <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                                </div>
                                                                <!--[if ENDBLOCK]><![endif]-->
                                                            </div>
                                                            <!--[if ENDBLOCK]><![endif]-->
                                                        </dd>
                                                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </dl>
                            </div>
                        </div>
                        <!--[if ENDBLOCK]><![endif]-->
                    </div>
                </dl>
            </div>
            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
        </div>
    </section>
</div>