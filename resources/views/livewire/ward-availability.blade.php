<div>
    <div class="header">
        <div>
            <h1 class="page-title">Ketersediaan Kamar Rawat Inap</h1>
            <p class="page-subtitle">Menampilkan data ketersediaan tempat tidur (lokal cache) terintegrasi dengan BPJS</p>
        </div>
        <button wire:click="syncManual" class="btn btn-primary">
            <i data-lucide="refresh-cw"></i> Sync Manual
        </button>
    </div>

    @if($syncMessage)
        <div class="card" style="background: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.2); padding: 1rem; color: var(--color-success); font-weight: 500; display: flex; align-items: center; gap: 0.5rem; border-radius: 0.75rem;">
            <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            {{ $syncMessage }}
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Kelas</th>
                        <th>Nama Kelas</th>
                        <th>Total Bed</th>
                        <th>Terisi</th>
                        <th>Tersedia (Sisa)</th>
                        <th>Update Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wards as $w)
                        <tr>
                            <td><strong>{{ $w->bpjs_class_code }}</strong></td>
                            <td>{{ $w->name }}</td>
                            <td>{{ $w->currentAvailability?->bed_total ?? 0 }}</td>
                            <td>{{ $w->currentAvailability?->bed_occupied ?? 0 }}</td>
                            <td>
                                <span class="badge {{ ($w->currentAvailability?->bed_available ?? 0) > 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $w->currentAvailability?->bed_available ?? 0 }} Bed Sisa
                                </span>
                            </td>
                            <td>{{ $w->synced_at ? $w->synced_at->format('d M Y H:i:s') : 'Belum Sync' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 2rem;">
                                Tidak ada data kamar rawat inap. Silakan lakukan sinkronisasi manual.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
