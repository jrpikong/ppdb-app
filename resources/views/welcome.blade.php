@php
    use Illuminate\Support\Str;

    $guidePath = base_path('CLIENT_GUIDE_V2.md');
    $guideMarkdown = is_file($guidePath) ? file_get_contents($guidePath) : '# CLIENT_GUIDE_V2.md tidak ditemukan';
    $guideHtml = Str::markdown($guideMarkdown);

    $staffDemo = [
        ['role' => 'super_admin', 'name' => 'Sarah Johnson', 'email' => 'sarah.johnson@vis-bin.sch.id'],
        ['role' => 'school_admin', 'name' => 'Michael Chen', 'email' => 'michael.chen@vis-bin.sch.id'],
        ['role' => 'admission_admin', 'name' => 'Lisa Wong', 'email' => 'lisa.wong@vis-bin.sch.id'],
        ['role' => 'finance_admin', 'name' => 'Robert Bintaro', 'email' => 'robert.bintaro@vis-bin.sch.id'],
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan PPDB VIS Bintaro</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {theme:{extend:{fontFamily:{sans:["Plus Jakarta Sans","sans-serif"],mono:["JetBrains Mono","monospace"]}}}};
    </script>
    <style>
        html{scroll-behavior:smooth} body{background:#f8fafc}
        .toc-link.active{background:#fff;border-color:#14b8a6;color:#0f172a}
        #guide-content pre{overflow:auto}
    </style>
</head>
<body class="text-slate-800">
<header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
        <div>
            <p class="text-[11px] uppercase tracking-[0.15em] text-slate-500 font-semibold">Client Guide V2</p>
            <h1 class="text-sm sm:text-base font-extrabold">Panduan Penggunaan Sistem PPDB VIS Bintaro</h1>
        </div>
        <div class="hidden md:flex gap-2 text-xs">
            <span class="px-2 py-1 rounded border bg-teal-50 border-teal-100">Portal: <code>/my</code></span>
            <span class="px-2 py-1 rounded border bg-amber-50 border-amber-100">Admin: <code>/school/s/VIS-BIN</code></span>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 grid lg:grid-cols-[260px,1fr] gap-6">
    <aside class="lg:sticky lg:top-24 lg:h-[calc(100vh-7rem)] lg:overflow-auto">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <label class="text-xs uppercase tracking-wide text-slate-500 font-semibold" for="searchNav">Cari bagian</label>
            <input id="searchNav" class="mt-2 w-full rounded-lg border-slate-200 text-sm" placeholder="contoh: faq, akun, alur">
            <nav id="toc" class="mt-4 space-y-2"></nav>
        </div>
    </aside>

    <main class="space-y-6">
        <section id="demo-vis-bin" class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-xl font-extrabold">Akun Demo VIS-BIN (Seeder)</h2>
            <p class="text-sm text-slate-600 mt-1">Semua akun menggunakan password <code>password</code>.</p>
            <div class="mt-4 grid md:grid-cols-2 gap-3 text-sm">
                @foreach ($staffDemo as $user)
                    <div class="rounded-xl border border-slate-200 p-3">
                        <p class="font-semibold">{{ $user['role'] }} - {{ $user['name'] }}</p>
                        <p class="font-mono text-xs mt-1">{{ $user['email'] }}</p>
                        <button type="button" data-copy="{{ $user['email'] }}" class="copy-btn mt-2 px-2 py-1 rounded border text-xs font-semibold hover:border-teal-500">Copy email</button>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <article id="guide-content" class="prose prose-slate max-w-none prose-headings:font-extrabold prose-h2:scroll-mt-24 prose-h3:scroll-mt-24 prose-pre:bg-slate-950 prose-pre:text-slate-100 prose-code:before:content-none prose-code:after:content-none">
                {!! $guideHtml !!}
            </article>
        </section>
    </main>
</div>

<script>
    const toc = document.getElementById("toc");
    const navSearch = document.getElementById("searchNav");
    const content = document.getElementById("guide-content");
    const headings = [...content.querySelectorAll("h2, h3")];
    const slugify = (s) => s.toLowerCase().replace(/[^\w\s-]/g, "").trim().replace(/\s+/g, "-");

    const tocItems = [];
    const quick = document.createElement("a");
    quick.href = "#demo-vis-bin";
    quick.dataset.label = "akun demo vis-bin";
    quick.className = "toc-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700";
    quick.textContent = "Akun Demo VIS-BIN";
    toc.appendChild(quick);
    tocItems.push(quick);

    headings.forEach((h) => {
        if (!h.id) h.id = slugify(h.textContent);
        const a = document.createElement("a");
        a.href = "#" + h.id;
        a.dataset.label = h.textContent.toLowerCase();
        a.className = "toc-link block rounded-lg border border-transparent px-3 py-2 text-sm font-semibold text-slate-700";
        a.textContent = h.textContent;
        if (h.tagName === "H3") a.classList.add("ml-3", "text-[13px]", "font-medium");
        toc.appendChild(a);
        tocItems.push(a);
    });

    const observedTargets = [document.getElementById("demo-vis-bin"), ...headings];
    const markActive = (id) => tocItems.forEach((i) => i.classList.toggle("active", i.getAttribute("href") === "#" + id));

    const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) markActive(e.target.id); });
    }, {rootMargin: "-25% 0px -60% 0px", threshold: 0.01});
    observedTargets.forEach((t) => t && obs.observe(t));

    navSearch.addEventListener("input", (e) => {
        const q = e.target.value.toLowerCase().trim();
        tocItems.forEach((i) => i.classList.toggle("hidden", !i.dataset.label.includes(q)));
    });

    document.querySelectorAll(".copy-btn").forEach((b) => {
        b.addEventListener("click", async () => {
            const text = b.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
                const t = b.textContent;
                b.textContent = "Copied";
                setTimeout(() => b.textContent = t, 1200);
            } catch (_) { window.prompt("Copy:", text); }
        });
    });
</script>
</body>
</html>
