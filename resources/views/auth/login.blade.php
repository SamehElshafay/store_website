@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="auth-card p-4 p-md-5">
                    <div class="text-center mb-5">
                        <h2 class="fw-800 mb-2" style="background: linear-gradient(135deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            {{ app()->getLocale() == 'ar' ? 'مرحباً بعودتك' : 'Welcome Back' }}
                        </h2>
                        <p class="text-muted small">
                            {{ app()->getLocale() == 'ar' ? 'الرجاء إدخال بياناتك للوصول إلى المستودع' : 'Please enter your details to access the warehouse' }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label small fw-bold text-uppercase text-muted">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@company.com">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label for="password" class="form-label small fw-bold text-uppercase text-muted">{{ __('Password') }}</label>
                                @if (Route::has('password.request'))
                                    <a class="text-decoration-none small fw-bold" href="{{ route('password.request') }}" style="color: #6366f1;">
                                        {{ app()->getLocale() == 'ar' ? 'نسيت كلمة السر؟' : 'Forgot password?' }}
                                    </a>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-lock"></i></span>
                                <input id="password" type="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-muted" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-gradient py-3 border-0 fw-bold">
                                {{ app()->getLocale() == 'ar' ? 'تسجيل الدخول' : 'Sign In' }}
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="text-center mt-4">
                     <p class="small text-muted">&copy; {{ date('Y') }} SmartStore Management System</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        overflow: hidden;
    }

    .fw-800 { font-weight: 800; }

    .auth-card {
        background: var(--card-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--border-color);
        border-radius: 30px;
        box-shadow: var(--card-shadow);
    }

    .form-control {
        padding: 0.8rem 1rem;
        border-radius: 12px;
        background-color: var(--nav-bg) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
    }

    [data-theme="dark"] .text-muted {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    [data-theme="dark"] .input-group-text {
        background-color: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main);
    }

    [data-theme="dark"] .form-control::placeholder {
        color: rgba(255, 255, 255, 0.4) !important;
    }

    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        background: var(--card-bg) !important;
        border: 1px solid #6366f1 !important;
    }

    .input-group-text {
        border-radius: 12px 0 0 12px;
    }

    .btn-primary-gradient {
        background: var(--primary-gradient);
        color: white;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        transition: all 0.3s;
    }

    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
        color: white;
    }

    [data-theme="dark"] .circle {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.1), rgba(192, 132, 252, 0.1));
    }

    /* Circles Animation */
    .circles-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        overflow: hidden;
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1));
        animation: move linear infinite;
    }

    @keyframes move {
        from { transform: translate(0, 0) rotate(0deg); }
        to { transform: translate(var(--x), var(--y)) rotate(360deg); }
    }
</style>

<script>
    // Generate animated circles for the auth page
    const wrapper = document.getElementById('circles');
    const circleCount = 10;

    for (let i = 0; i < circleCount; i++) {
        const circle = document.createElement('div');
        circle.className = 'circle';
        
        const size = Math.random() * 300 + 100;
        circle.style.width = `${size}px`;
        circle.style.height = `${size}px`;
        circle.style.left = `${Math.random() * 100}%`;
        circle.style.top = `${Math.random() * 100}%`;
        circle.style.setProperty('--x', `${(Math.random() - 0.5) * 400}px`);
        circle.style.setProperty('--y', `${(Math.random() - 0.5) * 400}px`);
        circle.style.animationDuration = `${Math.random() * 25 + 15}s`;
        
        wrapper.appendChild(circle);
    }
</script>

<!-- Add Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
