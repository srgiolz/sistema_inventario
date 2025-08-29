<x-guest-layout>
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="card shadow-sm text-center" style="width: 100%; max-width: 480px;">
            <div class="card-body px-4 py-4">

                <!-- Logo dentro del card -->
<div class="mb-4 text-center">
    <img src="{{ asset('images/sinvaris_logo.png') }}" 
         alt="YatiñaSoft Logo" 
         style="width: 250px; max-width: 100%;">
</div>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3 text-start">
        <label for="email" class="form-label" style="font-size: 14px;">Correo electrónico</label>
        <input id="email" type="email" class="form-control form-control-sm" name="email" required autofocus>
    </div>

    <div class="mb-3 text-start">
        <label for="password" class="form-label" style="font-size: 14px;">Contraseña</label>
        <input id="password" type="password" class="form-control form-control-sm" name="password" required>
    </div>

    <div class="form-check mb-3 text-start">
        <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
        <label class="form-check-label" for="remember_me" style="font-size: 13px;">Recordarme</label>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 13px;">¿Olvidaste tu contraseña?</a>
        @endif

        <button type="submit" class="btn btn-success btn-sm d-flex align-items-center">
            <i class="bi bi-box-arrow-in-right me-1"></i> Entrar
        </button>
    </div>
</form>

            </div>
        </div>
    </div>
</x-guest-layout>




