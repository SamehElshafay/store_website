@extends('layouts.app')

@section('content')
<div class="landing-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    
    <main class="content">
        <div class="glass-card">
            <h1 class="display-3 fw-800 mb-4">
                @if(app()->getLocale() == 'ar')
                    مستقبل <span class="premium-text">إدارة المخازن</span> الذكية
                @else
                    Future of <span class="premium-text">Smart Warehouse</span> Management
                @endif
            </h1>
            <p class="fs-5 mb-5 opacity-75">
                @if(app()->getLocale() == 'ar')
                    تحكم كامل في مخزونك، كفاءة عالية، ودقة متناهية. صُمم خصيصاً ليناسب احتياجات الشركات الطموحة في عصر التحول الرقمي.
                @else
                    Full control over your inventory, high efficiency, and ultimate precision. Designed specifically for ambitious businesses in the digital transformation era.
                @endif
            </p>
            
            <div class="cta">
                @auth
                    <a href="{{ url('/home') }}" class="btn btn-primary-gradient px-5 py-3 rounded-pill fw-bold fs-5">{{ app()->getLocale() == 'ar' ? 'لوحة التحكم' : 'Go to Dashboard' }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary-gradient px-5 py-3 rounded-pill fw-bold fs-5">{{ app()->getLocale() == 'ar' ? 'تسجيل الدخول الآن' : 'Sign In Now' }}</a>
                @endauth
            </div>
        </div>
    </main>
</div>

<style>
    .landing-wrapper {
        position: relative;
        min-height: calc(100vh - 82px);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .circles-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(168, 85, 247, 0.15));
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: move linear infinite;
    }

    @keyframes move {
        from { transform: translate(0, 0) rotate(0deg); }
        to { transform: translate(var(--x), var(--y)) rotate(360deg); }
    }

    .glass-card {
        background: var(--card-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--border-color);
        padding: 4rem;
        border-radius: 40px;
        max-width: 900px;
        text-align: center;
        box-shadow: var(--card-shadow);
        color: var(--text-main);
        transition: all 0.3s ease;
    }

    .premium-text {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .btn-primary-gradient {
        background: var(--primary-gradient);
        color: white;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary-gradient:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4);
        color: white;
    }

    .fw-800 { font-weight: 800; }

    [data-theme="dark"] .circle {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.1), rgba(192, 132, 252, 0.1));
    }
</style>

<script>
    // Generate animated circles
    (function() {
        const wrapper = document.getElementById('circles');
        if (!wrapper) return;
        const circleCount = 15;

        for (let i = 0; i < circleCount; i++) {
            const circle = document.createElement('div');
            circle.className = 'circle';
            
            const size = Math.random() * 300 + 100;
            circle.style.width = `${size}px`;
            circle.style.height = `${size}px`;
            circle.style.left = `${Math.random() * 100}%`;
            circle.style.top = `${Math.random() * 100}%`;
            circle.style.setProperty('--x', `${(Math.random() - 0.5) * 500}px`);
            circle.style.setProperty('--y', `${(Math.random() - 0.5) * 500}px`);
            circle.style.animationDuration = `${Math.random() * 25 + 15}s`;
            
            wrapper.appendChild(circle);
        }
    })();
</script>
@endsection
