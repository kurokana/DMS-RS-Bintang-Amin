<?php

namespace App\Livewire\Auth;

use App\Services\LocalAuthService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest', ['title' => 'Login DMS Admin']);
    }

    public function login(LocalAuthService $authService)
    {
        $this->validate();

        $key = 'login-attempts:' . request()->ip() . '|' . strtolower($this->email);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");
            return;
        }

        if ($authService->attemptLogin($this->email, $this->password)) {
            RateLimiter::clear($key);
            return redirect()->route('admin.dashboard');
        }

        RateLimiter::hit($key, 60); // lock for 60 seconds
        $this->addError('email', 'Email atau password salah.');
    }
}
