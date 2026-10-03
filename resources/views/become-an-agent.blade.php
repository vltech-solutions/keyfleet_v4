<x-layouts.guest>
    @php
        $commissionRate = $program
            ? (float) $program->commission_rate
            : 0;

        $commissionRatePercent = $commissionRate <= 1
            ? $commissionRate * 100
            : $commissionRate;

        $commissionMonths = $program?->commission_duration_months ?? 0;
        $holdingDays = $program?->holding_period_days ?? 0;

        /*
         * Replace this when your actual Agent registration route exists.
         *
         * Example:
         * route('agent.register')
         */
        $agentRegistrationUrl = url('/agent/register');
    @endphp

    {{-- ============================================================
        HERO
    ============================================================ --}}
    <section
        class="relative overflow-hidden bg-white transition-colors duration-300 dark:bg-gray-950"
    >
        {{-- Decorative background --}}
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-500/10"
            ></div>

            <div
                class="absolute -right-32 top-0 h-96 w-96 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-500/10"
            ></div>

            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.08),transparent_35%)]"
            ></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid items-center gap-14 lg:grid-cols-2">
                {{-- Left --}}
                <div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-300"
                    >
                        <span class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"
                            ></span>
                        </span>

                        KeyFleet Agent Program
                    </div>

                    <h1
                        class="mt-6 text-4xl font-extrabold tracking-tight text-gray-950 dark:text-white sm:text-5xl lg:text-6xl"
                    >
                        May kilala kang
                        <span
                            class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-indigo-400"
                        >
                            car rental business?
                        </span>
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-lg leading-8 text-gray-600 dark:text-gray-400"
                    >
                        Help car rental businesses discover KeyFleet and earn
                        commission when your successful referrals become paying
                        subscribers.
                    </p>

                    @if ($program)
                        <div
                            class="mt-8 inline-flex flex-wrap items-center gap-x-3 gap-y-2 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 dark:border-green-900/60 dark:bg-green-950/30"
                        >
                            <span class="text-3xl font-extrabold text-green-600 dark:text-green-400">
                                {{ number_format($commissionRatePercent, 0) }}%
                            </span>

                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">
                                    Recurring Commission
                                </p>

                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    for up to the first
                                    {{ $commissionMonths }}
                                    paid subscription months
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="#"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-700 to-indigo-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition duration-200 hover:-translate-y-0.5 hover:from-blue-800 hover:to-indigo-700 hover:shadow-xl active:scale-95"
                        >
                            Become a KeyFleet Agent

                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 7l5 5m0 0-5 5m5-5H6"
                                />
                            </svg>
                        </a>

                        <a
                            href="#how-it-works"
                            class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-7 py-3.5 text-sm font-semibold text-gray-700 transition duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-blue-800 dark:hover:bg-blue-950/30 dark:hover:text-blue-300"
                        >
                            How It Works
                        </a>
                    </div>

                    <div
                        class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm text-gray-500 dark:text-gray-400"
                    >
                        <div class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 text-green-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            No rental fleet required
                        </div>

                        <div class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 text-green-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            Personal Agent Portal
                        </div>

                        <div class="flex items-center gap-2">
                            <svg
                                class="h-5 w-5 text-green-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            Track referrals & earnings
                        </div>
                    </div>
                </div>

                {{-- Right --}}
                <div class="relative">
                    <div
                        class="relative overflow-hidden rounded-[2rem] border border-gray-200 bg-gray-50 p-5 shadow-2xl shadow-gray-200/50 dark:border-white/10 dark:bg-gray-900 dark:shadow-black/20 sm:p-7"
                    >
                        <div
                            class="absolute right-0 top-0 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl"
                        ></div>

                        <div class="relative">
                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400"
                                    >
                                        Agent Portal
                                    </p>

                                    <h2 class="mt-1 text-xl font-bold text-gray-950 dark:text-white">
                                        Your referrals. Your earnings.
                                    </h2>
                                </div>

                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-950"
                                >
                                    <p class="text-xs text-gray-400">
                                        Referrals
                                    </p>

                                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                                        12
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-950"
                                >
                                    <p class="text-xs text-gray-400">
                                        Active Subscribers
                                    </p>

                                    <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                                        8
                                    </p>
                                </div>

                                <div
                                    class="col-span-2 rounded-2xl border border-green-200 bg-green-50 p-5 dark:border-green-900/50 dark:bg-green-950/25"
                                >
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-medium text-green-700 dark:text-green-300">
                                                Example Commission
                                            </p>

                                            <p class="mt-2 text-3xl font-extrabold text-green-700 dark:text-green-400">
                                                ₱24,360
                                            </p>
                                        </div>

                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400"
                                        >
                                            <svg
                                                class="h-6 w-6"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 10v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </div>
                                    </div>

                                    <p class="mt-2 text-xs text-green-700/70 dark:text-green-300/70">
                                        Illustrative only. Actual earnings depend on eligible paid subscriptions.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-950"
                            >
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        Recent Referrals
                                    </span>

                                    <span class="text-xs text-gray-400">
                                        Example
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Sunrise Car Rental
                                            </p>

                                            <p class="text-xs text-gray-400">
                                                Paying subscriber
                                            </p>
                                        </div>

                                        <span
                                            class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-300"
                                        >
                                            Active
                                        </span>
                                    </div>

                                    <div class="h-px bg-gray-100 dark:bg-white/5"></div>

                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Metro Fleet Services
                                            </p>

                                            <p class="text-xs text-gray-400">
                                                Awaiting activation
                                            </p>
                                        </div>

                                        <span
                                            class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                                        >
                                            Pending
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute -bottom-5 -left-5 hidden rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-xl dark:border-white/10 dark:bg-gray-900 sm:block"
                    >
                        <p class="text-xs text-gray-400">
                            Simple referral model
                        </p>

                        <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                            Refer → Subscribe → Earn
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
        WHAT YOU ACTUALLY REFER
    ============================================================ --}}
    <section class="bg-gray-50 py-20 transition-colors duration-300 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-flex rounded-full bg-blue-100 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                >
                    Who You Refer
                </span>

                <h2
                    class="mt-4 text-3xl font-extrabold text-gray-950 dark:text-white sm:text-4xl"
                >
                    You're referring businesses,
                    <span class="text-blue-600 dark:text-blue-400">
                        not renters.
                    </span>
                </h2>

                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    KeyFleet Agents introduce car rental businesses to the
                    KeyFleet platform. When a valid referral becomes a paying
                    subscriber, the Agent may earn recurring commission.
                </p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                {{-- Card --}}
                <div
                    class="group rounded-3xl border border-gray-200 bg-white p-7 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-gray-950"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">
                        Car Rental Businesses
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Introduce legitimate car rental operators that could
                        benefit from KeyFleet's rental management platform.
                    </p>
                </div>

                {{-- Card --}}
                <div
                    class="group rounded-3xl border border-gray-200 bg-white p-7 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-gray-950"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m3.5-3.5 3-3a4 4 0 015.656 5.656l-1.5 1.5"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">
                        Use Your Referral
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Share your Agent referral link or other approved referral
                        method so KeyFleet can correctly attribute the business
                        to you.
                    </p>
                </div>

                {{-- Card --}}
                <div
                    class="group rounded-3xl border border-gray-200 bg-white p-7 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-gray-950"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 10v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">
                        Earn From Subscriptions
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Commission is based on eligible subscription payments,
                        not on individual car rental bookings made by the
                        referred business.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
        HOW IT WORKS
    ============================================================ --}}
    <section
        id="how-it-works"
        class="bg-white py-20 transition-colors duration-300 dark:bg-gray-950"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-flex rounded-full bg-indigo-100 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                >
                    How It Works
                </span>

                <h2 class="mt-4 text-3xl font-extrabold text-gray-950 dark:text-white sm:text-4xl">
                    Start referring in four simple steps
                </h2>
            </div>

            <div class="relative mt-14 grid gap-5 lg:grid-cols-4">
                @foreach ([
                    [
                        'number' => '01',
                        'title' => 'Join as an Agent',
                        'description' => 'Create your KeyFleet Agent account and access your personal Agent Portal.',
                    ],
                    [
                        'number' => '02',
                        'title' => 'Refer a Business',
                        'description' => 'Introduce a qualified car rental business using your referral link or approved referral process.',
                    ],
                    [
                        'number' => '03',
                        'title' => 'They Subscribe',
                        'description' => 'Your referral signs up for KeyFleet and successfully becomes a paying subscriber.',
                    ],
                    [
                        'number' => '04',
                        'title' => 'You Earn',
                        'description' => 'Eligible subscription payments generate commission based on the active Agent Program.',
                    ],
                ] as $step)
                    <div
                        class="relative rounded-3xl border border-gray-200 bg-gray-50 p-6 transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:bg-white hover:shadow-xl dark:border-white/10 dark:bg-gray-900 dark:hover:border-blue-900/60 dark:hover:bg-gray-900"
                    >
                        <span
                            class="text-4xl font-black text-blue-100 dark:text-blue-950"
                        >
                            {{ $step['number'] }}
                        </span>

                        <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">
                            {{ $step['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                            {{ $step['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
        COMMISSION
    ============================================================ --}}
    @if ($program)
        <section class="bg-gray-50 py-20 transition-colors duration-300 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-[2rem] border border-blue-200 bg-gradient-to-br from-blue-700 to-indigo-800 shadow-xl dark:border-blue-900/50"
                >
                    <div class="grid lg:grid-cols-2">
                        <div class="p-8 text-white sm:p-10 lg:p-12">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-200">
                                Commission Program
                            </p>

                            <h2 class="mt-4 text-3xl font-extrabold sm:text-4xl">
                                Earn as your referrals grow with KeyFleet.
                            </h2>

                            <p class="mt-4 max-w-xl leading-7 text-blue-100">
                                Eligible commissions are based on successfully
                                paid base subscription amounts from qualified
                                referrals.
                            </p>

                            <div class="mt-8 grid grid-cols-2 gap-4">
                                <div
                                    class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur"
                                >
                                    <p class="text-sm text-blue-100">
                                        Commission Rate
                                    </p>

                                    <p class="mt-2 text-4xl font-extrabold">
                                        {{ number_format($commissionRatePercent, 0) }}%
                                    </p>
                                </div>

                                <div
                                    class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur"
                                >
                                    <p class="text-sm text-blue-100">
                                        Eligible Period
                                    </p>

                                    <p class="mt-2 text-4xl font-extrabold">
                                        {{ $commissionMonths }}
                                    </p>

                                    <p class="mt-1 text-sm text-blue-100">
                                        paid months
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-8 dark:bg-gray-950 sm:p-10 lg:p-12">
                            <h3 class="text-xl font-bold text-gray-950 dark:text-white">
                                Example
                            </h3>

                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Example only. Actual amounts depend on the
                                referred customer's eligible subscription.
                            </p>

                            <div class="mt-6 space-y-4">
                                <div
                                    class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-white/5"
                                >
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        Eligible subscription payment
                                    </span>

                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        ₱2,000
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-white/5"
                                >
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        Commission rate
                                    </span>

                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($commissionRatePercent, 0) }}%
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between"
                                >
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                                        Example commission
                                    </span>

                                    <span class="text-2xl font-extrabold text-green-600 dark:text-green-400">
                                        ₱{{ number_format(2000 * ($commissionRatePercent / 100), 2) }}
                                    </span>
                                </div>
                            </div>

                            <p
                                class="mt-6 rounded-xl bg-gray-50 px-4 py-3 text-xs leading-5 text-gray-500 dark:bg-white/5 dark:text-gray-400"
                            >
                                Multi-month subscription payments may consume
                                multiple eligible commission months. Commission
                                eligibility is based on paid subscription
                                coverage, not on the number of payment
                                transactions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================================================
        AGENT PORTAL BENEFITS
    ============================================================ --}}
    <section class="bg-white py-20 transition-colors duration-300 dark:bg-gray-950">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <span
                    class="inline-flex rounded-full bg-blue-100 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                >
                    Your Agent Portal
                </span>

                <h2 class="mt-4 text-3xl font-extrabold text-gray-950 dark:text-white sm:text-4xl">
                    Everything you need to track your referrals.
                </h2>

                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    No spreadsheets and no guessing. Your Agent Portal gives you
                    visibility into the status of your referrals and commissions.
                </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Referral Tracking', 'description' => 'See the businesses you referred and their current referral status.'],
                    ['title' => 'Subscriber Status', 'description' => 'Know when an eligible referral becomes an active paying subscriber.'],
                    ['title' => 'Commission History', 'description' => 'Review your earned, pending, payable and paid commissions.'],
                    ['title' => 'Payout Tracking', 'description' => 'Monitor your commission payout history from one place.'],
                ] as $feature)
                    <div
                        class="rounded-3xl border border-gray-200 bg-gray-50 p-6 transition duration-200 hover:-translate-y-1 hover:bg-white hover:shadow-lg dark:border-white/10 dark:bg-gray-900 dark:hover:bg-gray-900"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>

                        <h3 class="mt-5 font-bold text-gray-900 dark:text-white">
                            {{ $feature['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                            {{ $feature['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
        COMMISSION FLOW
    ============================================================ --}}
    @if ($program)
        <section class="bg-gray-50 py-20 transition-colors duration-300 dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-950 dark:text-white">
                        How commission becomes payable
                    </h2>

                    <p class="mt-3 text-gray-500 dark:text-gray-400">
                        Commission goes through a verification period before it
                        becomes available for payout.
                    </p>
                </div>

                <div class="mt-12 grid gap-4 md:grid-cols-4">
                    @foreach ([
                        ['title' => 'Payment Received', 'subtitle' => 'Subscriber successfully pays'],
                        ['title' => 'Pending', 'subtitle' => 'Commission is recorded'],
                        ['title' => 'Holding Period', 'subtitle' => $holdingDays . ' calendar days'],
                        ['title' => 'Payable', 'subtitle' => 'Eligible for payout'],
                    ] as $index => $status)
                        <div class="relative">
                            <div
                                class="h-full rounded-2xl border border-gray-200 bg-white p-5 text-center dark:border-white/10 dark:bg-gray-950"
                            >
                                <div
                                    class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white"
                                >
                                    {{ $index + 1 }}
                                </div>

                                <p class="mt-4 font-semibold text-gray-900 dark:text-white">
                                    {{ $status['title'] }}
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    {{ $status['subtitle'] }}
                                </p>
                            </div>

                            @if (! $loop->last)
                                <div
                                    class="absolute -right-3 top-1/2 z-10 hidden -translate-y-1/2 text-gray-300 dark:text-gray-700 md:block"
                                >
                                    →
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================================================
        FAQ
    ============================================================ --}}
    <section
        x-data="{ active: null }"
        class="bg-white py-20 transition-colors duration-300 dark:bg-gray-950"
    >
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <span
                    class="inline-flex rounded-full bg-indigo-100 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                >
                    FAQ
                </span>

                <h2 class="mt-4 text-3xl font-extrabold text-gray-950 dark:text-white">
                    Common questions
                </h2>
            </div>

            @php
                $faqs = [
                    [
                        'question' => 'Do I need my own car rental business to become an Agent?',
                        'answer' => 'No. An Agent refers qualified car rental businesses to KeyFleet. You do not need to own a rental fleet yourself.',
                    ],
                    [
                        'question' => 'Do I earn from every booking made by the car rental company?',
                        'answer' => 'No. Agent commission is based on eligible KeyFleet subscription payments made by the referred business, not on the rental bookings they process.',
                    ],
                    [
                        'question' => 'What happens if the subscriber pays for several months at once?',
                        'answer' => 'A multi-month payment may cover several commission-eligible subscription months. For example, a valid 3-month subscription payment generally consumes three of the referral\'s eligible paid subscription months.',
                    ],
                    [
                        'question' => 'How long can I earn commission from one referral?',
                        'answer' => $program
                            ? "Under the current program, eligible commission may be earned for up to the first {$commissionMonths} paid subscription months."
                            : 'Commission duration is based on the active KeyFleet Agent Program.',
                    ],
                    [
                        'question' => 'What happens if a payment is refunded?',
                        'answer' => 'Commission related to refunded, reversed, or charged-back subscription payments may also be reversed in accordance with the Agent Program Terms.',
                    ],
                    [
                        'question' => 'Can two Agents claim the same business?',
                        'answer' => 'Referral attribution is determined by KeyFleet\'s referral records and applicable Agent Program Terms. A valid referral should not generate duplicate commission ownership.',
                    ],
                ];
            @endphp

            <div class="mt-10 space-y-3">
                @foreach ($faqs as $index => $faq)
                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-900"
                    >
                        <button
                            type="button"
                            @click="active = active === {{ $index }} ? null : {{ $index }}"
                            class="flex w-full items-center justify-between gap-6 px-6 py-5 text-left"
                        >
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ $faq['question'] }}
                            </span>

                            <svg
                                class="h-5 w-5 shrink-0 text-gray-400 transition duration-200"
                                :class="active === {{ $index }} ? 'rotate-180' : ''"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                />
                            </svg>
                        </button>

                        <div
                            x-show="active === {{ $index }}"
                            x-collapse
                            x-cloak
                        >
                            <p
                                class="border-t border-gray-200 px-6 py-5 text-sm leading-7 text-gray-500 dark:border-white/5 dark:text-gray-400"
                            >
                                {{ $faq['answer'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
        FINAL CTA
    ============================================================ --}}
    <section class="bg-gray-50 px-4 py-20 dark:bg-gray-900 sm:px-6 lg:px-8">
        <div
            class="mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-700 via-blue-700 to-indigo-800 px-6 py-12 text-center shadow-2xl shadow-blue-900/20 sm:px-10 lg:py-16"
        >
            <span
                class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-blue-100"
            >
                Become a KeyFleet Agent
            </span>

            <h2 class="mx-auto mt-5 max-w-3xl text-3xl font-extrabold text-white sm:text-4xl">
                Help car rental businesses grow while building your own referral income.
            </h2>

            <p class="mx-auto mt-4 max-w-2xl leading-7 text-blue-100">
                Join the KeyFleet Agent Network and start introducing qualified
                car rental businesses to the platform.
            </p>

            <div class="mt-8 flex justify-center">
                <a
                    href="https://www.facebook.com/profile.php?id=61577048618076"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-3.5 text-sm font-bold text-blue-700 shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-blue-50 hover:shadow-xl active:scale-95"
                >
                    Message Us Now

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 7l5 5m0 0-5 5m5-5H6"
                        />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-xs text-blue-200">
                Agent commissions are subject to the KeyFleet Agent Program Terms
                and applicable referral eligibility rules.
            </p>
        </div>
    </section>
</x-layouts.guest>