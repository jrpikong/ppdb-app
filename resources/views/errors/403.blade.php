@php
    $user = auth()->user();
    $roleNames = $user?->getRoleNames()
        ->map(fn (string $role): string => str($role)->replace('_', ' ')->title()->toString())
        ->implode(', ');

    $activeRole = filled($roleNames) ? $roleNames : 'Guest';

    $loginUrl = url('/my/login');

    if ($user?->hasRole('super_admin')) {
        $loginUrl = url('/superadmin/login');
    } elseif ($user?->hasAnyRole(['admin', 'school_admin', 'admission_admin', 'finance_admin'])) {
        $loginUrl = url('/school/login');
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak</title>
    @include('filament.my.auth.partials.head-assets')
    @stack('styles')
</head>
<body class="font-display bg-background-light text-slate-900 antialiased selection:bg-primary/30 selection:text-primary">
<div class="flex min-h-screen w-full flex-row overflow-hidden">
    <aside class="relative hidden w-1/2 bg-slate-900 lg:flex">
        <div
            class="absolute inset-0 bg-cover bg-center opacity-80"
            style="background-image: url('{{ asset('asset/building-backgroud.jpg') }}');"
        ></div>
        <div class="absolute inset-0 bg-primary/40 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>

        <div class="relative z-10 flex w-full flex-col justify-end p-16 text-white">
            <p class="mb-4 text-xs uppercase tracking-[0.35em] text-white/70">VIS Admission Portal</p>
            <h1 class="max-w-lg text-4xl font-bold leading-tight">
                Hak akses dibatasi untuk menjaga keamanan data portal.
            </h1>
            <p class="mt-5 max-w-lg text-sm text-white/85">
                Silakan masuk ke panel yang sesuai dengan peran akun Anda.
            </p>
        </div>
    </aside>

    <main class="flex w-full items-center justify-center bg-white px-6 py-12 lg:w-1/2 lg:px-20">
        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60 lg:p-10">
            <img
                src="{{ asset('/logo/main-logo') }}"
                alt="VIS Bintaro School Logo"
                class="h-14 w-auto object-contain"
            >

            <div class="mt-8 inline-flex items-center rounded-full bg-red-100 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-700">
                403 Forbidden
            </div>

            <h2 class="mt-5 text-3xl font-bold tracking-tight text-slate-900">
                Anda tidak dapat mengakses halaman ini
            </h2>

            <p class="mt-4 text-sm leading-7 text-slate-600">
                Anda sedang login sebagai
                <span class="rounded bg-slate-100 px-2 py-1 font-semibold text-slate-800">{{ $activeRole }}</span>
                dan tidak memiliki izin untuk membuka halaman ini.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ url()->previous() }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Kembali ke halaman sebelumnya
                </a>
                <a
                    href="{{ $loginUrl }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    Buka halaman login yang sesuai
                </a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
