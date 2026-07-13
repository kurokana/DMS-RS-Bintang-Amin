<div>
    <div class="header">
        <div>
            <h1 class="page-title">Jadwal Operasi Rumah Sakit</h1>
            <p class="page-subtitle">Menampilkan jadwal tindakan operasi (lokal cache) terintegrasi dengan BPJS</p>
        </div>
        <button wire:click="syncManual" class="btn btn-primary">
            <i data-lucide="refresh-cw"></i> Sync Manual
        </button>
    </div>

    @if($syncMessage)
        <div class="card" style="background: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.2); padding: 1rem; color: var(--color-success); font-weight: 500; display: flex; align-items: center; gap: 0.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
            <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
            {{ $syncMessage }}
        </div>
    @endif

    <!-- Filtering Area -->
    <div class="card" style="padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <label class="form-label" style="margin-bottom: 0;" for="roomFilter">Filter Kamar Operasi:</label>
            <select wire:model.live="selectedRoomId" id="roomFilter" class="form-control" style="padding: 0.5rem 1.5rem; min-width: 200px;">
                <option value="">Semua Kamar</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Jadwal BPJS</th>
                        <th>Kamar Operasi</th>
                        <th>Nama Pasien</th>
                        <th>Rencana Mulai</th>
                        <th>Mulai Aktual</th>
                        <th>Status Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $sch)
                        <tr>
                            <td><code>{{ $sch->bpjs_schedule_id }}</code></td>
                            <td>{{ $sch->operatingRoom->name }}</td>
                            <td><strong>{{ $sch->patient_name }}</strong></td>
                            <td>{{ $sch->scheduled_start_at->format('d M Y H:i') }}</td>
                            <td>{{ $sch->actual_start_at ? $sch->actual_start_at->format('d M Y H:i') : '-' }}</td>
                            <td>
                                @if($sch->status === 'selesai')
                                    <span class="badge badge-success">Selesai</span>
                                @elseif($sch->status === 'sedang_dilaksanakan')
                                    <span class="badge badge-warning">Sedang Berjalan</span>
                                @else
                                    <span class="badge" style="background: rgba(148, 163, 184, 0.1); color: var(--color-text-muted); border: 1px solid rgba(148, 163, 184, 0.2);">Menunggu</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 2rem;">
                                Tidak ada jadwal operasi untuk kamar yang dipilih. Silakan lakukan sinkronisasi manual.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
