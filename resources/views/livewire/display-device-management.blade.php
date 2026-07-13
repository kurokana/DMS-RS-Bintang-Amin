<div>
    <div class="header">
        <div>
            <h1 class="page-title">Manajemen Display Monitor</h1>
            <p class="page-subtitle">Daftarkan perangkat monitor STB baru dan atur mapping konten yang akan ditampilkan</p>
        </div>
    </div>

    @if($message)
        <div class="card" style="background: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.2); padding: 1rem; color: var(--color-success); font-weight: 500; margin-bottom: 1.5rem; border-radius: 0.75rem;">
            {{ $message }}
        </div>
    @endif

    @if($errorMessage)
        <div class="card" style="background: rgba(248, 113, 113, 0.1); border-color: rgba(248, 113, 113, 0.2); padding: 1rem; color: var(--color-danger); font-weight: 500; margin-bottom: 1.5rem; border-radius: 0.75rem;">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="grid-2">
        <!-- Devices Table -->
        <div class="card" style="flex: 2;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem;">Monitor Terdaftar</h3>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Display ID</th>
                            <th>Nama Monitor</th>
                            <th>Status Alat</th>
                            <th>Mapping Konten</th>
                            <th>Target Mapping</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $dev)
                            <tr>
                                <td><code>{{ $dev->display_id }}</code></td>
                                <td>{{ $dev->name }}</td>
                                <td>
                                    @if($dev->isOnline())
                                        <span class="badge badge-success">Online</span>
                                    @else
                                        <span class="badge badge-danger">Offline</span>
                                    @endif
                                </td>
                                <td>
                                    @if($dev->mappings->isNotEmpty())
                                        <span class="badge badge-warning" style="text-transform: uppercase;">
                                            {{ $dev->mappings->first()->target_type === 'ward_class' ? 'Rawat Inap' : 'Kamar Operasi' }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(148, 163, 184, 0.1); color: var(--color-text-muted);">Belum Dimap</span>
                                    @endif
                                </td>
                                <td>
                                    @if($dev->mappings->isNotEmpty())
                                        <strong>{{ $dev->mappings->first()->target?->name ?? 'Unknown Target' }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--color-text-muted); padding: 2rem;">
                                    Belum ada monitor terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Management Forms Side -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Register Device Form (Admin Only) -->
            @if(auth()->user()->role === 'admin')
                <div class="card">
                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="plus-circle" style="color: var(--color-primary);"></i>
                        Daftar Monitor Baru (STB)
                    </h3>
                    
                    <form wire:submit.prevent="registerDevice">
                        <div class="form-group">
                            <label class="form-label" for="displayId">Display ID</label>
                            <input wire:model="displayId" type="text" id="displayId" class="form-control" placeholder="MISAL: DSP001" required>
                            @error('displayId') <span class="error-message">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="deviceName">Nama Lokasi/Monitor</label>
                            <input wire:model="name" type="text" id="deviceName" class="form-control" placeholder="MISAL: Ruang Tunggu OK 1" required>
                            @error('name') <span class="error-message">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Daftarkan Monitor
                        </button>
                    </form>
                </div>
            @endif

            <!-- Update Mapping Form (Admin & Operator) -->
            <div class="card">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="shuffle" style="color: var(--color-secondary);"></i>
                    Update Mapping Konten
                </h3>

                <form wire:submit.prevent="updateMapping">
                    <div class="form-group">
                        <label class="form-label" for="targetDevice">Pilih Monitor</label>
                        <select wire:model.live="selectedDeviceId" id="targetDevice" class="form-control" required>
                            <option value="">-- Pilih Monitor --</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->id }}">{{ $dev->display_id }} - {{ $dev->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mappingType">Jenis Konten</label>
                        <select wire:model.live="targetType" id="mappingType" class="form-control" required>
                            <option value="ward_class">Ketersediaan Kamar Rawat Inap</option>
                            <option value="operating_room">Jadwal Kamar Operasi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="targetId">Pilih Target Tujuan</label>
                        <select wire:model="targetId" id="targetId" class="form-control" required>
                            <option value="">-- Pilih Target --</option>
                            @if($targetType === 'ward_class')
                                @foreach($wards as $w)
                                    <option value="{{ $w->id }}">{{ $w->bpjs_class_code }} - {{ $w->name }}</option>
                                @endforeach
                            @elseif($targetType === 'operating_room')
                                @foreach($rooms as $r)
                                    <option value="{{ $r->id }}">{{ $r->bpjs_or_code }} - {{ $r->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; background: linear-gradient(135deg, var(--color-secondary), #8b5cf6);">
                        Perbarui Mapping
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
