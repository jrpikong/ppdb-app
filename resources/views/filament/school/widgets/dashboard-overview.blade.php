<x-filament-widgets::widget>
    @php
        $categories = $this->getCategories();
    @endphp

    <div class="mt-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg shadow-primary-500/20 dark:shadow-primary-500/10">
                <x-filament::icon
                    icon="heroicon-o-squares-2x2"
                    class="h-6 w-6 text-white"
                />
            </div>
            <div>
                <h2 class="text-lg font-medium tracking-tight text-gray-950 dark:text-white">
                    Quick Actions & Overview
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Click any item to navigate to the respective page
                </p>
            </div>
            <div class="ms-auto hidden items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 dark:bg-primary-500/10 sm:flex">
                <div class="h-1.5 w-1.5 rounded-full bg-primary-500 animate-pulse"></div>
                <span class="text-xs font-semibold text-primary-700 dark:text-primary-400">
                    {{ collect($categories)->sum(fn ($c) => count($c['features'])) }} items
                </span>
            </div>
        </div>

        {{-- Category Cards Grid --}}
        <div class="columns-1 gap-6 space-y-6 md:columns-2 xl:columns-3">
            @foreach ($categories as $category)
                <div class="break-inside-avoid overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    {{-- Gradient accent bar --}}
                    @if ($category['color'] === 'blue')
                        <div class="h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                    @elseif ($category['color'] === 'amber')
                        <div class="h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
                    @elseif ($category['color'] === 'rose')
                        <div class="h-1 bg-gradient-to-r from-rose-500 to-pink-500"></div>
                    @elseif ($category['color'] === 'emerald')
                        <div class="h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                    @elseif ($category['color'] === 'violet')
                        <div class="h-1 bg-gradient-to-r from-violet-500 to-purple-500"></div>
                    @elseif ($category['color'] === 'cyan')
                        <div class="h-1 bg-gradient-to-r from-cyan-500 to-sky-500"></div>
                    @else
                        <div class="h-1 bg-gradient-to-r from-gray-500 to-slate-600"></div>
                    @endif

                    {{-- Category header --}}
                    <div class="flex items-center gap-3 px-5 pt-4 pb-3">
                        {{-- Icon container with proper color classes --}}
                        @if ($category['color'] === 'blue')
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-blue-50 dark:bg-blue-500/10 ring-blue-200/80 dark:ring-blue-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                        @elseif ($category['color'] === 'amber')
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-amber-50 dark:bg-amber-500/10 ring-amber-200/80 dark:ring-amber-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            </div>
                        @elseif ($category['color'] === 'rose')
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-rose-50 dark:bg-rose-500/10 ring-rose-200/80 dark:ring-rose-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            </div>
                        @elseif ($category['color'] === 'emerald')
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-emerald-50 dark:bg-emerald-500/10 ring-emerald-200/80 dark:ring-emerald-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        @elseif ($category['color'] === 'violet')
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-violet-50 dark:bg-violet-500/10 ring-violet-200/80 dark:ring-violet-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                            </div>
                        @elseif ($category['color'] === 'cyan')
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-cyan-50 dark:bg-cyan-500/10 ring-cyan-200/80 dark:ring-cyan-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                            </div>
                        @else
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ring-1 bg-gray-100 dark:bg-gray-500/10 ring-gray-200/80 dark:ring-gray-500/20">
                                <x-filament::icon :icon="$category['icon']" class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                        @endif

                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $category['name'] }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ count($category['features']) }} {{ Str::plural('item', count($category['features'])) }}
                            </p>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="mx-5 border-t border-gray-100 dark:border-white/5"></div>

                    {{-- Feature list --}}
                    <div class="p-2">
                        @foreach ($category['features'] as $feature)
                            <a
                                href="{{ $feature['url'] }}"
                                wire:navigate
                                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-all duration-150 hover:bg-gray-50 dark:hover:bg-white/5"
                            >
                                {{-- Colored dot --}}
                                @if ($category['color'] === 'blue')
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-blue-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @elseif ($category['color'] === 'amber')
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @elseif ($category['color'] === 'rose')
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @elseif ($category['color'] === 'emerald')
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @elseif ($category['color'] === 'violet')
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-violet-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @elseif ($category['color'] === 'cyan')
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-cyan-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @else
                                    <div class="flex h-1.5 w-1.5 shrink-0 rounded-full bg-gray-500 opacity-40 transition-opacity group-hover:opacity-100"></div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-medium text-gray-700 transition-colors group-hover:text-gray-950 dark:text-gray-300 dark:group-hover:text-white">
                                            {{ $feature['name'] }}
                                        </p>
                                        <span class="shrink-0 rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium leading-tight text-gray-500 transition-colors group-hover:bg-gray-200/70 group-hover:text-gray-600 dark:bg-white/5 dark:text-gray-500 dark:group-hover:bg-white/10 dark:group-hover:text-gray-400">
                                            {{ $feature['resource'] }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs leading-relaxed text-gray-400 dark:text-gray-500">
                                        {{ $feature['description'] }}
                                    </p>
                                </div>

                                <x-filament::icon
                                    icon="heroicon-m-chevron-right"
                                    class="h-4 w-4 shrink-0 text-gray-300 opacity-0 transition-all duration-150 group-hover:translate-x-0.5 group-hover:opacity-100 rtl:group-hover:-translate-x-0.5 rtl:-scale-x-100 dark:text-gray-600"
                                />
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
