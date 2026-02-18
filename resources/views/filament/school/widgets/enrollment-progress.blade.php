<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Enrollment Progress by Level
        </x-slot>

        <div class="space-y-4">
            @forelse ($this->getLevels() as $level)
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-600 text-xs font-bold">
                                {{ $level['code'] }}
                            </span>
                            <span class="font-medium text-sm">{{ $level['name'] }}</span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold">{{ $level['enrolled'] }}</span> / {{ $level['quota'] }}
                            <span class="text-xs">({{ $level['percentage'] }}%)</span>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
                        <div
                            class="h-2.5 rounded-full transition-all duration-300"
                            style="width: {{ min($level['percentage'], 100) }}%; background-color: {{ match($level['color']) {
                                'red' => '#EF4444',
                                'orange' => '#F97316',
                                'yellow' => '#F59E0B',
                                'blue' => '#3B82F6',
                                'green' => '#10B981',
                                default => '#6B7280',
                            } }}"
                        ></div>
                    </div>

                    {{-- Status text --}}
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @if ($level['percentage'] >= 100)
                            <span class="text-red-600 dark:text-red-400 font-semibold">⚠️ Full - No slots available</span>
                        @elseif ($level['percentage'] >= 90)
                            <span class="text-orange-600 dark:text-orange-400 font-semibold">{{ $level['available'] }} slots remaining</span>
                        @else
                            <span class="text-gray-600 dark:text-gray-400">{{ $level['available'] }} slots available</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                    <p class="text-sm">No active levels found</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
