<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ static::$heading }}
        </x-slot>

        <div class="space-y-3">
            @foreach ($this->getActionItems() as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="block p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <x-filament::icon
                                    :icon="$item['icon']"
                                    class="w-5 h-5 text-{{ $item['color'] }}-500"
                                />
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $item['label'] }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if ($item['count'] > 0)
                                <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-bold rounded-full bg-{{ $item['color'] }}-100 text-{{ $item['color'] }}-800 dark:bg-{{ $item['color'] }}-900 dark:text-{{ $item['color'] }}-200">
                                    {{ $item['count'] }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    ✓
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Summary --}}
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                @php
                    $total = collect($this->getActionItems())->sum('count');
                @endphp
                @if ($total > 0)
                    <span class="font-semibold text-{{ $total > 10 ? 'warning' : 'info' }}-600">
                        {{ $total }} total items requiring attention
                    </span>
                @else
                    <span class="text-success-600 dark:text-success-400">
                        ✓ All caught up!
                    </span>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
