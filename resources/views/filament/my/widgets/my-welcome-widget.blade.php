<div style="position: relative; overflow: hidden; border-radius: 16px; padding: 24px; margin-bottom: 12px; background: linear-gradient(135deg, #0f3d91 0%, #1256c5 55%, #1f7edb 100%); color: #ffffff; box-shadow: 0 10px 24px rgba(15, 61, 145, 0.25);">
    <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between; position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 14px; min-width: 240px;">
            <div style="width: 46px; height: 46px; border-radius: 12px; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700;">
                {{ strtoupper(substr($userName, 0, 1)) }}
            </div>
            <div>
                <div style="font-size: 13px; opacity: .9;">{{ $greeting }},</div>
                <div style="font-size: 24px; font-weight: 700; line-height: 1.2;">{{ $userName }}</div>
                <div style="font-size: 14px; opacity: .95; margin-top: 4px;">
                    @if ($draftCount > 0)
                        Anda punya <strong>{{ $draftCount }} draft</strong> yang perlu dilengkapi.
                    @elseif ($hasAnyApp)
                        Aplikasi Anda sedang diproses. Notifikasi update akan muncul otomatis.
                    @else
                        Mulai pendaftaran anak Anda dari tombol aksi di bawah.
                    @endif
                </div>
            </div>
        </div>

        <div>
            @if ($draftCount > 0)
                <a href="{{ $listUrl }}" style="display: inline-block; text-decoration: none; background: #ffd24d; color: #4a3a00; padding: 10px 14px; border-radius: 10px; font-weight: 700; font-size: 13px;">
                    Lanjutkan Draft
                </a>
            @else
                <a href="{{ $createUrl }}" style="display: inline-block; text-decoration: none; background: #ffffff; color: #1256c5; padding: 10px 14px; border-radius: 10px; font-weight: 700; font-size: 13px;">
                    Start New Application
                </a>
            @endif
        </div>
    </div>

    <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.22); font-size: 12px; opacity: .9; position: relative; z-index: 1;">
        VIS Admissions | {{ now()->format('l, d M Y') }} | admissions@vis.sch.id
    </div>

    <div style="position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,.08);"></div>
    <div style="position: absolute; right: 120px; bottom: -55px; width: 130px; height: 130px; border-radius: 50%; background: rgba(255,255,255,.08);"></div>
</div>
