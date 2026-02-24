@include('filament.my.auth.partials.head-assets')

<div class="font-display bg-background-light text-slate-900 min-h-screen antialiased selection:bg-primary/30 selection:text-primary">
    <div class="flex min-h-screen w-full flex-row overflow-hidden">
        <div class="hidden lg:flex lg:w-1/2 relative bg-slate-900">
            <div
                class="absolute inset-0 bg-cover bg-center opacity-80"
                style='background-image: url({{ asset('asset/building-backgroud-2.jpg') }});'
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
                <div class="flex flex-col items-center text-center">
                    <img
                        src="{{ asset('/logo/main-logo') }}"
                        alt="VIS Bintaro School Logo"
                        class="h-16 w-auto mb-6 object-contain"
                    >
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                        Admission Portal Register
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Create your parent account to continue the admissions process.
                    </p>
                </div>

                <form wire:submit="register" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium leading-6 text-slate-900">
                                Full name
                            </label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">person</span>
                                </div>
                                <input
                                    id="name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    placeholder="William Parent"
                                    wire:model.defer="data.name"
                                    class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                />
                            </div>
                            @error('data.name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

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
                                    placeholder="william@gmail.com"
                                    wire:model.defer="data.email"
                                    class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                />
                            </div>
                            @error('data.email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium leading-6 text-slate-900">
                                Phone number
                            </label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">call</span>
                                </div>
                                <input
                                    id="phone"
                                    type="tel"
                                    autocomplete="tel"
                                    maxlength="20"
                                    placeholder="+62-812-xxxx-xxxx"
                                    wire:model.defer="data.phone"
                                    class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                />
                            </div>
                            @error('data.phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium leading-6 text-slate-900">
                                Password
                            </label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    autocomplete="new-password"
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

                        <div>
                            <label for="passwordConfirmation" class="block text-sm font-medium leading-6 text-slate-900">
                                Confirm password
                            </label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                </div>
                                <input
                                    id="passwordConfirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                    placeholder="********"
                                    wire:model.defer="data.passwordConfirmation"
                                    class="block w-full rounded-lg border-0 py-3 pl-10 text-slate-900 ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 transition-all"
                                />
                            </div>
                            @error('data.passwordConfirmation')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-start gap-2">
                            <input
                                id="terms"
                                type="checkbox"
                                wire:model="data.terms"
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary"
                            >
                            <label for="terms" class="text-sm text-slate-600">
                                I agree to the
                                <a href="{{ route('terms') }}" target="_blank" class="font-medium text-primary hover:text-primary-dark transition-colors">Terms & Conditions</a>
                                and
                                <a href="{{ route('privacy') }}" target="_blank" class="font-medium text-primary hover:text-primary-dark transition-colors">Privacy Policy</a>.
                            </label>
                        </div>
                        @error('data.terms')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="relative flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-md hover:bg-blue-700 transition disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="register">
                                Create My Account
                            </span>
                            <span wire:loading.flex wire:target="register" class="items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                                <path class="opacity-75" fill="white"
                                      d="M4 12a8 8 0 018-8v8H4z">
                                </path>
                            </svg>
                            Creating account...
                        </span>
                                        </button>
                                    </div>
                </form>

                @if (filament()->hasLogin())
                    <div class="relative">
                        <div aria-hidden="true" class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm font-medium leading-6">
                            <span class="bg-white px-6 text-slate-500">Already registered?</span>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-slate-600">
                            Already have an account?
                            <a href="{{ filament()->getLoginUrl() }}" class="font-bold text-primary hover:text-primary-dark transition-colors ml-1">
                                Login here
                            </a>
                        </p>
                    </div>
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
