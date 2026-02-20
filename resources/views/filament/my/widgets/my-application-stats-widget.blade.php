{{-- resources/views/filament/my/widgets/my-application-stats-widget.blade.php --}}
<div
    class="flex h-full flex-col rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-500/10">
                <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">My Applications</h3>
        </div>
        @if ($total > 0)
            <a href="{{ $listUrl }}"
               class="text-xs font-medium text-blue-600 transition hover:text-blue-500 dark:text-blue-400">
                View all →
            </a>
        @endif
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 gap-px bg-gray-100 dark:bg-white/5 sm:grid-cols-4">
        @foreach ([
            ['label' => 'Total',       'value' => $total,      'color' => 'text-gray-900 dark:text-white',   'bg' => 'bg-white dark:bg-gray-900'],
            ['label' => 'Draft',       'value' => $draft,      'color' => 'text-gray-500 dark:text-gray-400','bg' => 'bg-white dark:bg-gray-900'],
            ['label' => 'In Progress', 'value' => $inProgress, 'color' => 'text-blue-600 dark:text-blue-400','bg' => 'bg-white dark:bg-gray-900'],
            ['label' => 'Accepted',    'value' => $accepted,   'color' => 'text-green-600 dark:text-green-400','bg' => 'bg-white dark:bg-gray-900'],
        ] as $stat)
            <div class="flex flex-col px-5 py-4 {{ $stat['bg'] }}">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</span>
                <span class="mt-1 text-2xl font-bold {{ $stat['color'] }}">{{ $stat['value'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Recent applications --}}
    <div class="flex flex-1 flex-col">

        @if (count($recent) > 0)
            <div class="px-5 pb-1 pt-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Recent</p>
            </div>

            <div class="flex flex-col divide-y divide-gray-50 dark:divide-white/5">
                @foreach ($recent as $app)
                    @php
                        $badgeClasses = match ($app['color']) {
                            'blue'    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
                            'amber'   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                            'green'   => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
                            'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
                            'red'     => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
                            default   => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                        };
                    @endphp

                    <a href="{{ $app['url'] }}"
                       class="group flex items-center gap-3 px-5 py-3.5 transition hover:bg-gray-50/80 dark:hover:bg-white/5">

                        {{-- Student initial --}}
                        <div
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                            {{ strtoupper(substr($app['name'], 0, 1)) }}
                        </div>

                        {{-- Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-medium text-gray-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                    {{ $app['name'] }}
                                </p>
                                <span
                                    class="flex-shrink-0 rounded-full px-1.5 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                    {{ $app['label'] }}
                                </span>
                            </div>
                            <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">
                                {{ $app['period'] }} · {{ $app['level'] }} · #{{ $app['number'] }}
                            </p>
                        </div>

                        <svg
                            class="h-4 w-4 flex-shrink-0 text-gray-300 transition group-hover:text-blue-400 dark:text-gray-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>

                    </a>
                @endforeach
            </div>

            @if ($total > 3)
                <div class="mt-auto border-t border-gray-100 px-5 py-3 dark:border-white/10">
                    <a href="{{ $listUrl }}"
                       class="text-xs font-medium text-blue-600 transition hover:text-blue-500 dark:text-blue-400">
                        View all {{ $total }} applications →
                    </a>
                </div>
            @endif

        @else
            {{-- Empty state --}}
            <div class="flex flex-1 flex-col items-center justify-center gap-3 py-12 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 dark:bg-white/5">
                    <svg class="h-7 w-7 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No applications yet</p>
                    <p class="mt-1 text-xs text-gray-400">Start by applying for your child's admission.</p>
                </div>
                <a href="{{ $listUrl }}"
                   class="mt-1 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-500 active:scale-95">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Start Application
                </a>
            </div>
        @endif

    </div>

</div>
