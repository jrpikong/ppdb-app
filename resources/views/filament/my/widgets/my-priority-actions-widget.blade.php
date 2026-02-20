{{-- resources/views/filament/my/widgets/my-priority-actions-widget.blade.php --}}
<div class="flex h-full flex-col rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-white/10">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-500/10">
                <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Priority Actions</h3>
        </div>
        @if ($unreadCount > 0)
            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                {{ $unreadCount }}
            </span>
        @endif
    </div>

    {{-- Action list --}}
    <div class="flex flex-1 flex-col divide-y divide-gray-50 dark:divide-white/5">
        @forelse ($actions as $action)
            @php
                $colorMap = [
                    'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'icon' => 'text-amber-500', 'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400'],
                    'red'   => ['bg' => 'bg-red-50 dark:bg-red-500/10',     'icon' => 'text-red-500',   'badge' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'],
                    'blue'  => ['bg' => 'bg-blue-50 dark:bg-blue-500/10',   'icon' => 'text-blue-500',  'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400'],
                    'green' => ['bg' => 'bg-green-50 dark:bg-green-500/10', 'icon' => 'text-green-500', 'badge' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'],
                ];
                $c = $colorMap[$action['color']] ?? $colorMap['blue'];
            @endphp

            <a href="{{ $action['url'] }}"
               class="group flex items-start gap-3.5 px-5 py-4 transition hover:bg-gray-50/80 dark:hover:bg-white/5">

                {{-- Icon --}}
                <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl {{ $c['bg'] }}">
                    <svg class="h-4.5 h-[18px] w-[18px] {{ $c['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if ($action['icon'] === 'document-text')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        @elseif ($action['icon'] === 'banknotes')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                        @elseif ($action['icon'] === 'calendar-days')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                        @elseif ($action['icon'] === 'plus-circle')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                </div>

                {{-- Text --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                            {{ $action['label'] }}
                        </p>
                        @if ($action['badge'])
                            <span class="rounded-full px-1.5 py-0.5 text-xs font-medium {{ $c['badge'] }}">
                                {{ $action['badge'] }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                        {{ $action['sub'] }}
                    </p>
                </div>

                {{-- Chevron --}}
                <svg class="mt-1 h-4 w-4 flex-shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-blue-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>

            </a>
        @empty
            <div class="flex flex-1 flex-col items-center justify-center gap-2 py-10 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 dark:bg-green-500/10">
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">All caught up!</p>
                <p class="text-xs text-gray-400">No actions required at this time.</p>
            </div>
        @endforelse
    </div>

    {{-- Notification row --}}
    @if ($unreadCount > 0)
        <div class="flex items-center gap-2 border-t border-gray-100 bg-amber-50 px-5 py-3 dark:border-white/10 dark:bg-amber-500/10">
            <svg class="h-4 w-4 flex-shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-xs font-medium text-amber-700 dark:text-amber-400">
                You have {{ $unreadCount }} unread {{ $unreadCount === 1 ? 'notification' : 'notifications' }} — check the bell icon above.
            </p>
        </div>
    @endif

</div>
