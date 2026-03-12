@include('filament.school.auth.partials.head-assets')

<div
    class="font-display bg-background-light text-slate-900 min-h-screen antialiased selection:bg-primary/30 selection:text-primary">
    <div class="flex min-h-screen w-full flex-row overflow-hidden">
        <div class="hidden lg:flex lg:w-1/2 relative bg-slate-900">
            <div
                class="absolute inset-0 bg-cover bg-center opacity-80"
                style='background-image: url({{ asset('asset/building-backgroud.jpg') }});'
            ></div>
            <div class="absolute inset-0 bg-primary/40 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>

            <div class="relative z-10 flex flex-col justify-end p-16 w-full text-white">
                <blockquote class="max-w-lg">
                    <p class="text-3xl font-bold leading-tight tracking-tight mb-6">
                        "Empowering future leaders through holistic education and global perspectives."
                    </p>
                    <footer class="flex items-center gap-4">
                        <div class="h-px w-12 bg-white/50"></div>
                        <span class="text-sm font-medium text-white/90">VIS -</span>
                    </footer>
                </blockquote>
            </div>
        </div>

        <div class="flex w-full lg:w-1/2 flex-col justify-center items-center bg-white px-6 py-12 lg:px-20 xl:px-32">
            <div class="w-full max-w-md space-y-8">
                @php
                    $socialiteEnabled = (bool) config('filament-socialite.school_panel.enabled')
                        && filled(config('services.google.client_id'))
                        && filled(config('services.google.client_secret'));
                @endphp

                <div class="flex flex-col items-center text-center">
                    <img
                        src="{{ asset('/logo/main-logo') }}"
                        alt="VIS School Logo"
                        class="h-16 w-auto mb-6 object-contain"
                    >
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                        Staff Portal Login
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Welcome back! Please enter your credentials to access the staff portal.
                    </p>
                </div>

                <form wire:submit="authenticate" class="mt-8 space-y-6">
                    <div class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-slate-900">
                                Email address
                            </label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">mail</span>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    placeholder="staff@vis.sch.id"
                                    wire:model.defer="data.email"
                                    class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                />
                            </div>
                            @error('data.email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium leading-6 text-slate-900">
                                    Password
                                </label>
                                @if (filament()->hasPasswordReset())
                                    <div class="text-sm">
                                        <a
                                            href="{{ filament()->getRequestPasswordResetUrl() }}"
                                            class="font-medium text-primary hover:text-primary-dark transition-colors"
                                        >
                                            Forgot password?
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    placeholder="********"
                                    wire:model.defer="data.password"
                                    class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                />
                            </div>
                            @error('data.password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="remember"
                                type="checkbox"
                                wire:model="data.remember"
                                class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                            >
                            <label for="remember" class="text-sm text-slate-600">Remember me</label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="flex w-full justify-center rounded-lg bg-blue-600 px-3 py-3 text-sm font-bold leading-6 text-white shadow-sm hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all duration-200 disabled:opacity-70"
                        >
                            <span wire:loading.remove wire:target="authenticate">
                                Login to Staff Portal
                            </span>
                            <span wire:loading wire:target="authenticate">
                                Signing in...
                            </span>
                        </button>
                    </div>
                </form>

                @if ($socialiteEnabled)
                    <x-filament-socialite::buttons />
                @endif

                <div class="mt-8 flex justify-center gap-6 text-xs text-slate-400">
                    <a href="{{ route('privacy') }}" class="hover:text-slate-600 transition-colors">Privacy Policy</a>
                    <a href="{{ route('terms') }}" class="hover:text-slate-600 transition-colors">Terms of Service</a>
                    <a href="{{ route('welcome') }}" class="hover:text-slate-600 transition-colors">Help Center</a>
                </div>
            </div>
        </div>
    </div>
</div>
