<div class="card">
    <div class="header">
        <div class="logo-icon">
            <i data-lucide="monitor-play"></i>
        </div>
        <h1 class="title">DMS Middleware</h1>
        <p class="subtitle">Silakan login untuk mengelola display monitor</p>
    </div>

    <form wire:submit.prevent="login">
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input 
                wire:model="email" 
                type="email" 
                id="email" 
                class="form-control" 
                placeholder="contoh@dms.local" 
                required
            >
            @error('email') <span class="error-message">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input 
                wire:model="password" 
                type="password" 
                id="password" 
                class="form-control" 
                placeholder="••••••••" 
                required
            >
            @error('password') <span class="error-message">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn">
            Masuk ke Dashboard
        </button>
    </form>
</div>
