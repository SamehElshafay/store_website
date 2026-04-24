@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container-fluid py-4 px-4">
        <!-- ... existing content ... -->

    {{-- Page Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('contacts.index') }}" class="btn btn-sm rounded-circle" style="width:38px;height:38px;padding:0;display:flex;align-items:center;justify-content:center;background:var(--card-bg);border:1px solid var(--border-color);color:var(--text-main);">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                <i class="bi {{ $defaultType == 'sender' ? 'bi-person-up' : 'bi-person-plus-fill' }} me-2" style="color: #6366f1;"></i>
                {{ $defaultType == 'sender' ? __('New Sender') : __('New Recipient') }}
            </h4>
            <p class="text-muted small mb-0">{{ __('Fill in the contact details below') }}</p>
        </div>
    </div>

    <form id="contactForm">
        @csrf
        <input type="hidden" name="type" value="{{ $defaultType }}">

        <div class="row justify-content-center g-4">
            <div class="col-lg-8">

                {{-- Basic Info Card --}}
                <div class="glass-card rounded-4 p-4 mb-4 shadow-lg border-0">
                    <h6 class="fw-bold mb-4 d-flex align-items-center gap-2" style="color: var(--text-main);">
                        <span style="width:28px;height:28px;background:rgba(99,102,241,0.15);border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
                            <i class="bi {{ $defaultType == 'sender' ? 'bi-person-up' : 'bi-person-badge' }}" style="color:#6366f1;font-size:0.8rem;"></i>
                        </span>
                        {{ $defaultType == 'sender' ? __('Sender Information') : __('Recipient Information') }}
                    </h6>

                    <div class="row g-4">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control border-0 bg-dark-soft p-3 rounded-3" placeholder="{{ $defaultType == 'sender' ? __('Enter sender full name') : __('Enter recipient full name') }}" required>
                            <div class="invalid-feedback name-error"></div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control border-0 bg-dark-soft p-3 rounded-3" placeholder="01x-xxx-xxxx" required>
                            <div class="invalid-feedback phone-error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1">{{ __('Full Address') }} <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control border-0 bg-dark-soft p-3 rounded-3" rows="2" placeholder="{{ __('Street, building, apartment, city...') }}" required></textarea>
                            <div class="invalid-feedback address-error"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted letter-spacing-1">{{ __('Notes') }} ({{ __('Optional') }})</label>
                            <input type="text" name="notes" class="form-control border-0 bg-dark-soft p-3 rounded-3" placeholder="{{ __('Any extra details...') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" id="submitBtn" class="btn btn-lg rounded-pill px-5 fw-bold flex-grow-1 position-relative overflow-hidden" style="background: linear-gradient(135deg, #6366f1, #a855f7); color: white; border: none; min-height: 58px; transition: all 0.3s ease;">
                        <span class="btn-text d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-person-plus"></i> {{ $defaultType == 'sender' ? __('Save Sender') : __('Save Recipient') }}
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            {{ __('Processing...') }}
                        </span>
                    </button>
                    <a href="{{ route('contacts.index') }}" class="btn btn-lg rounded-pill px-4 text-muted bg-dark-soft border-0 d-flex align-items-center justify-content-center" style="min-width: 120px;">
                        {{ __('Cancel') }}
                    </a>
                </div>

            </div>
        </div>
    </form>


    </div> {{-- Close container-fluid --}}
</div> {{-- Close dashboard-wrapper --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    if (!contactForm) return;

    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const btn = document.getElementById('submitBtn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');

        // Reset errors UI
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.innerText = '';
            el.style.display = 'none';
        });

        // Show Loading
        btn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            const formData = new FormData(form);
            const response = await fetch("{{ route('contacts.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok) {
                // SUCCESS: Use the theme's showToast function
                if (window.showToast) {
                    const msg = "{{ $defaultType == 'sender' ? __('Sender saved successfully!') : __('Recipient saved successfully!') }}";
                    window.showToast("{{ __('Success') }}", result.message || msg, 'success');
                } else {
                    alert("{{ __('Saved successfully!') }}");
                }

                setTimeout(() => {
                    window.location.href = result.redirect || "{{ route('contacts.index', ['type' => $defaultType]) }}";
                }, 1000);

            } else {
                throw result;
            }

        } catch (error) {
            console.error('Final Submission Error:', error);
            
            btn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');

            if (error.errors) {
                // VALIDATION ERROR
                let errorDetails = "";
                Object.keys(error.errors).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    const errorBox = form.querySelector(`.${key}-error`);
                    const errorMsg = error.errors[key][0];
                    
                    if (input) input.classList.add('is-invalid');
                    if (errorBox) {
                        errorBox.innerText = errorMsg;
                        errorBox.style.display = 'block';
                    }
                    errorDetails += `• ${errorMsg}<br>`;
                });

                if (window.showToast) {
                    window.showToast("{{ __('Check Fields') }}", "{{ __('Please fix the validation errors below.') }}", 'error', errorDetails);
                }
            } else {
                // SERVER ERROR
                if (window.showToast) {
                    window.showToast("{{ __('Server Error') }}", error.message || "{{ __('Something went wrong. Please try again.') }}", 'error');
                } else {
                    alert("Error: " + (error.message || "Unknown error"));
                }
            }
        }
    });

    // Circles Animation
    (function() {
        const wrapper = document.getElementById('circles');
        if (!wrapper) return;
        const circleCount = 10;
        for (let i = 0; i < circleCount; i++) {
            const circle = document.createElement('div');
            circle.className = 'circle';
            const size = Math.random() * 300 + 150;
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
});
</script>
@endsection
