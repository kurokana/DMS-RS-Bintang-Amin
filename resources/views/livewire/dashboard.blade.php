<div>
    <div class="header">
        <div>
            <h1 class="page-title">Ringkasan Sistem</h1>
            <p class="page-subtitle">Display Management System (DMS) — Tahap 1</p>
        </div>
    </div>

    <!-- Quick stats grid -->
    <div class="grid-stats">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 500;">TOTAL MONITOR</p>
                    <h2 style="font-size: 2.25rem; font-weight: 800; margin-top: 0.5rem;">{{ $totalDevices }}</h2>
                </div>
                <div style="padding: 0.75rem; background: rgba(56, 189, 248, 0.1); border-radius: 0.75rem; color: var(--color-primary);">
                    <i data-lucide="monitor"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 500;">MONITOR ONLINE</p>
                    <h2 style="font-size: 2.25rem; font-weight: 800; margin-top: 0.5rem; color: var(--color-success);">{{ $onlineDevices }}</h2>
                </div>
                <div style="padding: 0.75rem; background: rgba(52, 211, 153, 0.1); border-radius: 0.75rem; color: var(--color-success);">
                    <i data-lucide="wifi"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 500;">MONITOR OFFLINE</p>
                    <h2 style="font-size: 2.25rem; font-weight: 800; margin-top: 0.5rem; color: var(--color-danger);">{{ $offlineDevices }}</h2>
                </div>
                <div style="padding: 0.75rem; background: rgba(248, 113, 113, 0.1); border-radius: 0.75rem; color: var(--color-danger);">
                    <i data-lucide="wifi-off"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 500;">STATUS BPJS</p>
                    @if($bpjsStatus === 'connected')
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.75rem;">
                            <span class="badge badge-success">Terhubung</span>
                        </div>
                    @else
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.75rem;">
                            <span class="badge badge-danger">Terputus</span>
                        </div>
                    @endif
                </div>
                <div style="padding: 0.75rem; background: {{ $bpjsStatus === 'connected' ? 'rgba(52, 211, 153, 0.1)' : 'rgba(248, 113, 113, 0.1)' }}; border-radius: 0.75rem; color: {{ $bpjsStatus === 'connected' ? 'var(--color-success)' : 'var(--color-danger)' }};">
                    <i data-lucide="activity"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- BPJS connection sync status cards -->
    <div class="grid-2">
        <div class="card">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="bed-double" style="color: var(--color-primary);"></i>
                Sinkronisasi Ketersediaan Kamar
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 0.75rem;">
                    <span style="color: var(--color-text-muted);">Status Terakhir</span>
                    @if($wardLastSync && $wardLastSync->status === 'success')
                        <span class="badge badge-success">Sukses</span>
                    @else
                        <span class="badge badge-danger">Gagal / Belum Sync</span>
                    @endif
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 0.75rem;">
                    <span style="color: var(--color-text-muted);">Waktu Sinkronisasi</span>
                    <span>{{ $wardLastSync?->synced_at ? $wardLastSync->synced_at->format('d M Y H:i:s') : '-' }}</span>
                </div>
                @if($wardLastSync && $wardLastSync->status === 'failed')
                    <div style="padding: 0.75rem; background: rgba(248, 113, 113, 0.05); border: 1px solid rgba(248, 113, 113, 0.15); border-radius: 0.5rem; font-size: 0.8rem; color: var(--color-danger);">
                        <strong>Error:</strong> {{ $wardLastSync->error_message }}
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="activity" style="color: var(--color-secondary);"></i>
                Sinkronisasi Jadwal Operasi
            </h3>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 0.75rem;">
                    <span style="color: var(--color-text-muted);">Status Terakhir</span>
                    @if($orLastSync && $orLastSync->status === 'success')
                        <span class="badge badge-success">Sukses</span>
                    @else
                        <span class="badge badge-danger">Gagal / Belum Sync</span>
                    @endif
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255, 255, 255, 0.05); padding-bottom: 0.75rem;">
                    <span style="color: var(--color-text-muted);">Waktu Sinkronisasi</span>
                    <span>{{ $orLastSync?->synced_at ? $orLastSync->synced_at->format('d M Y H:i:s') : '-' }}</span>
                </div>
                @if($orLastSync && $orLastSync->status === 'failed')
                    <div style="padding: 0.75rem; background: rgba(248, 113, 113, 0.05); border: 1px solid rgba(248, 113, 113, 0.15); border-radius: 0.5rem; font-size: 0.8rem; color: var(--color-danger);">
                        <strong>Error:</strong> {{ $orLastSync->error_message }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
