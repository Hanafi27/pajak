<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class LoginController extends Controller
{
    private array $defaultUsers = [
        'petugas' => ['password' => 'password123', 'name' => 'Petugas Bapenda', 'role' => 'petugas'],
        'pimpinan' => ['password' => 'password123', 'name' => 'Pimpinan Bapenda', 'role' => 'pimpinan'],
    ];

    private function usersFilePath(): string
    {
        return storage_path('app/demo-users.json');
    }

    private function getUsers(): array
    {
        $path = $this->usersFilePath();
        if (! File::exists($path)) {
            File::put($path, json_encode($this->defaultUsers, JSON_PRETTY_PRINT));
            return $this->defaultUsers;
        }

        $raw = File::get($path);
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            File::put($path, json_encode($this->defaultUsers, JSON_PRETTY_PRINT));
            return $this->defaultUsers;
        }

        return array_replace_recursive($this->defaultUsers, $decoded);
    }

    private function saveUsers(array $users): void
    {
        File::put($this->usersFilePath(), json_encode($users, JSON_PRETTY_PRINT));
    }

    public function show(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $users = $this->getUsers();

        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $users[$validated['username']] ?? null;

        if (! $user || $validated['password'] !== $user['password']) {
            return back()->withInput()->with('error', 'Username atau password salah.');
        }

        $request->session()->put('auth_user', [
            'id' => crc32($validated['username']),
            'username' => $validated['username'],
            'name' => $user['name'],
            'role' => $user['role'],
        ]);

        return redirect()->route('dashboard.index')->with('success', 'Login berhasil.');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function processForgotPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:50', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        $users = $this->getUsers();
        $username = strtolower($validated['username']);

        if (! isset($users[$username])) {
            return back()->withInput()->with('error', 'Username tidak ditemukan.');
        }

        $users[$username]['password'] = $validated['password'];
        $this->saveUsers($users);

        return redirect()->route('login.form')->with('success', 'Password berhasil diubah. Silakan login kembali.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['auth_user', 'mock']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form')->with('success', 'Logout berhasil.');
    }
}
