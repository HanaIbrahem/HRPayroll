{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="corporate">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('logo-small.png') }}" type="image/x-icon">
    <title>{{ env('APP_NAME') }}</title>
</head>
<body class="min-h-screen bg-base-200">

    <!-- Move theme switcher to a better place (top-right) -->
    <div class="fixed top-4 right-4 z-50">
        <x-nav.theme />
    </div>

    <div class="hero min-h-screen">
        <div class="hero-content w-full max-w-md">
            <div class="card w-full bg-base-100 shadow-2xl border border-base-300">

                <!-- Centered logo inside card -->
                <div class="pt-8 flex justify-center">
                    <img class="w-35 object-contain" src="{{ asset('logo-small.svg') }}" alt="Logo">
                </div>

                <div class="card-body">
                    <h2 class="text-2xl font-bold text-center">Login to checklist</h2>

                    @if ($errors->any())
                        <div class="alert alert-error mt-3">
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="mt-4 space-y-4" autocomplete="on">
                        @csrf

                        <div class="form-control">
                            <label class="label"><span class="label-text mb-2">Username</span></label>
                            <input
                                type="text"
                                name="username"
                                value="{{ old('username') }}"
                                required
                                autofocus
                                class="input input-primary w-full"
                                placeholder="your.username" />
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text mb-2">Password</span></label>

                            <!-- Password with show/hide button -->
                            <div class="join w-full">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    required
                                    autocomplete="current-password"
                                    class="input input-primary join-item w-full"
                                    placeholder="••••••••" />

                         
                            </div>
                           
                        </div>
                        <div class="form-control">
                            <input type="checkbox" class="checkbox" id="togglePassword">
                            <label class="label-text" for="">Show Password</label>
                        </div>

                        <div class="form-control my-2">

                <label class="label cursor-pointer gap-3 justify-start">
                    <input wire:model="remember" type="checkbox" class="checkbox" />
                    <span class="label-text">Remember me</span>
                </label>
                        </div>
                        <div class="form-control mt-2">
                            <button type="submit" class="btn btn-primary w-full rounded-3xl">Login</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Tiny script to toggle password visibility -->
    <script>
        (function () {
            const password = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (password && toggle) {
                toggle.addEventListener('click', () => {
                    const isHidden = password.type === 'password';
                    password.type = isHidden ? 'text' : 'password';
                    toggle.setAttribute('aria-pressed', String(isHidden));
                    if (eyeOpen && eyeClosed) {
                        eyeOpen.classList.toggle('hidden', !isHidden);
                        eyeClosed.classList.toggle('hidden', isHidden);
                    }
                });
            }
        })();
    </script>
</body>
</html>
