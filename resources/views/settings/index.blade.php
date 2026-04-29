@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex align-items-center mb-5 gap-3">
                <div class="avatar-md bg-primary-soft text-primary rounded-4 d-flex align-items-center justify-content-center fs-2" style="width: 64px; height: 64px;">
                    <i class="bi bi-sliders"></i>
                </div>
                <div>
                    <h2 class="fw-800 mb-0 text-main">{{ __('Settings') }}</h2>
                    <p class="text-muted mb-0">{{ __('Manage your application preferences and system configuration.') }}</p>
                </div>
            </div>

            <!-- Settings Grid -->
            <div class="row g-4">
                <!-- Default Contacts Card -->
                <div class="col-12">
                    <div class="glass-card p-4 rounded-4 border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="avatar-sm bg-primary-soft text-primary rounded-3 d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-main mb-1">{{ __('Default Contacts') }}</h5>
                                <p class="small text-muted mb-0">{{ __('Set default senders and recipients to speed up parcel registration.') }}</p>
                            </div>
                        </div>

                        <form id="defaultContactsForm">
                            @csrf
                            <div class="row g-4">
                                {{-- Receive Defaults --}}
                                <div class="col-md-6">
                                    <h6 class="small fw-800 text-uppercase text-primary mb-3"><i class="bi bi-box-seam me-1"></i> {{ __('Receive Modal Defaults') }}</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ __('Default Sender (Source)') }}</label>
                                        <div class="custom-search-select has-search" id="setting_receive_sender">
                                            <input type="hidden" name="default_receive_sender_id" value="{{ $settings['default_receive_sender_id'] }}">
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" 
                                                placeholder="{{ __('Search senders...') }}" 
                                                value="{{ \App\Models\Contact::find($settings['default_receive_sender_id'])->name ?? '' }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ __('Default Recipient (Destination)') }}</label>
                                        <div class="custom-search-select has-search" id="setting_receive_recipient">
                                            <input type="hidden" name="default_receive_recipient_id" value="{{ $settings['default_receive_recipient_id'] }}">
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" 
                                                placeholder="{{ __('Search recipients...') }}" 
                                                value="{{ \App\Models\Contact::find($settings['default_receive_recipient_id'])->name ?? '' }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dispatch Defaults --}}
                                <div class="col-md-6">
                                    <h6 class="small fw-800 text-uppercase text-primary mb-3"><i class="bi bi-truck me-1"></i> {{ __('Dispatch Modal Defaults') }}</h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ __('Default Sender (Source)') }}</label>
                                        <div class="custom-search-select has-search" id="setting_dispatch_sender">
                                            <input type="hidden" name="default_dispatch_sender_id" value="{{ $settings['default_dispatch_sender_id'] }}">
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" 
                                                placeholder="{{ __('Search senders...') }}" 
                                                value="{{ \App\Models\Contact::find($settings['default_dispatch_sender_id'])->name ?? '' }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">{{ __('Default Recipient (Destination)') }}</label>
                                        <div class="custom-search-select has-search" id="setting_dispatch_recipient">
                                            <input type="hidden" name="default_dispatch_recipient_id" value="{{ $settings['default_dispatch_recipient_id'] }}">
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" 
                                                placeholder="{{ __('Search recipients...') }}" 
                                                value="{{ \App\Models\Contact::find($settings['default_dispatch_recipient_id'])->name ?? '' }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Dispatch Status Setting --}}
                            <div class="mt-4 pt-3 border-top border-white border-opacity-10">
                                <h6 class="small fw-800 text-uppercase text-warning mb-3">
                                    <i class="bi bi-arrow-right-circle-fill me-1"></i>
                                    {{ __('Dispatch Modal — Target Status') }}
                                </h6>
                                <p class="small text-muted mb-3">
                                    {{ __('Choose the status that will be applied to a parcel when it is registered & dispatched via the dispatch modal.') }}
                                </p>
                                <div class="row g-3 align-items-center">
                                    @foreach ($parcel_statuses as $ps)
                                    <div class="col-auto">
                                        <label class="d-flex align-items-center gap-2 p-3 rounded-3 border cursor-pointer dispatch-status-label"
                                            style="background: {{ $settings['default_dispatch_status_id'] == $ps->id ? 'rgba(99,102,241,0.2)' : 'rgba(255,255,255,0.03)' }};
                                                   border-color: {{ $settings['default_dispatch_status_id'] == $ps->id ? '#6366f1' : 'rgba(255,255,255,0.1)' }};
                                                   transform: {{ $settings['default_dispatch_status_id'] == $ps->id ? 'scale(1.05)' : 'scale(1)' }};
                                                   cursor: pointer; transition: all 0.2s;">
                                            <input type="radio" name="default_dispatch_status_id"
                                                   value="{{ $ps->id }}"
                                                   class="dispatch-status-radio d-none"
                                                   {{ $settings['default_dispatch_status_id'] == $ps->id ? 'checked' : '' }}>
                                            <span class="badge rounded-pill px-3 py-2 fw-bold"
                                                  style="background: {{ $ps->color ?? '#6366f1' }}20; color: {{ $ps->color ?? '#6366f1' }}; border: 1px solid {{ $ps->color ?? '#6366f1' }}40;">
                                                <i class="bi {{ $ps->icon ?? 'bi-circle' }} me-1"></i>
                                                {{ $ps->display_name }}
                                            </span>
                                            @if($settings['default_dispatch_status_id'] == $ps->id)
                                                <i class="bi bi-check-circle-fill active-check ms-2 text-primary"></i>
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSaveDefaults">
                                    {{ __('Save Default Settings') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Status Settings Card -->
                <div class="col-md-6">
                    <div class="glass-card p-4 rounded-4 border-0 shadow-sm h-100 transition-all hover-up">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="avatar-sm bg-info-soft text-info rounded-3 d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="bi bi-gear-wide-connected"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-main mb-1">{{ __('Parcel Statuses') }}</h5>
                                <p class="small text-muted mb-0">{{ __('Configure parcel workflow, colors, and modal types.') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('parcel-statuses.index') }}" class="btn btn-dark-soft w-100 rounded-pill fw-bold">
                            {{ __('Manage Statuses') }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Account Settings Card -->
                <div class="col-md-6">
                    <div class="glass-card p-4 rounded-4 border-0 shadow-sm h-100 transition-all hover-up">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="avatar-sm bg-success-soft text-success rounded-3 d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-main mb-1">{{ __('Account Info') }}</h5>
                                <p class="small text-muted mb-0">{{ __('Update your profile details and security settings.') }}</p>
                            </div>
                        </div>
                        <button class="btn btn-dark-soft w-100 rounded-pill fw-bold opacity-50 cursor-not-allowed" disabled>
                            {{ __('Coming Soon') }}
                        </button>
                    </div>
                </div>

                <!-- Localization Settings -->
                <div class="col-md-6">
                    <div class="glass-card p-4 rounded-4 border-0 shadow-sm h-100 transition-all hover-up">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="avatar-sm bg-warning-soft text-warning rounded-3 d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="bi bi-translate"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-main mb-1">{{ __('Language & Region') }}</h5>
                                <p class="small text-muted mb-0">{{ __('Choose your preferred language and timezone.') }}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="?lang=en" class="btn {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-dark-soft' }} flex-grow-1 rounded-pill fw-bold">English</a>
                            <a href="?lang=ar" class="btn {{ app()->getLocale() == 'ar' ? 'btn-primary' : 'btn-dark-soft' }} flex-grow-1 rounded-pill fw-bold">العربية</a>
                        </div>
                    </div>
                </div>

                <!-- System Maintenance -->
                <div class="col-md-6">
                    <div class="glass-card p-4 rounded-4 border-0 shadow-sm h-100 transition-all hover-up">
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="avatar-sm bg-danger-soft text-danger rounded-3 d-flex align-items-center justify-content-center fs-4" style="width: 48px; height: 48px;">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-main mb-1">{{ __('Maintenance') }}</h5>
                                <p class="small text-muted mb-0">{{ __('System tools to clear cache and optimize performance.') }}</p>
                            </div>
                        </div>
                        <button class="btn btn-danger-soft w-100 rounded-pill fw-bold opacity-50 cursor-not-allowed" disabled>
                            {{ __('Coming Soon') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-up {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-up:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 45px rgba(0,0,0,0.1) !important;
    }
    .bg-primary-soft { background: rgba(99, 102, 241, 0.1); }
    .bg-info-soft { background: rgba(14, 165, 233, 0.1); }
    .bg-success-soft { background: rgba(34, 197, 94, 0.1); }
    .bg-warning-soft { background: rgba(234, 179, 8, 0.1); }
    .bg-danger-soft { background: rgba(239, 68, 68, 0.1); }
    .btn-danger-soft {
        background: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border: none !important;
    }
    .btn-danger-soft:hover {
        background: #ef4444 !important;
        color: white !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Search Selects
    if (window.initSearchSelect) {
        window.initSearchSelect('setting_receive_sender', 'sender');
        window.initSearchSelect('setting_receive_recipient', 'recipient');
        window.initSearchSelect('setting_dispatch_sender', 'sender');
        window.initSearchSelect('setting_dispatch_recipient', 'recipient');
    }

    // Dispatch Status Radio Cards — visual interaction
    document.querySelectorAll('.dispatch-status-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            // 1. Reset all labels
            document.querySelectorAll('.dispatch-status-label').forEach(l => {
                l.style.background = 'rgba(255,255,255,0.03)';
                l.style.borderColor = 'rgba(255,255,255,0.1)';
                l.style.transform = 'scale(1)';
                // Remove existing checkmark if any
                const existingCheck = l.querySelector('.active-check');
                if (existingCheck) existingCheck.remove();
            });

            // 2. Highlight selected
            if (this.checked) {
                const label = this.closest('.dispatch-status-label');
                label.style.background = 'rgba(99,102,241,0.2)';
                label.style.borderColor = '#6366f1';
                label.style.transform = 'scale(1.05)';

                // Add checkmark icon
                const check = document.createElement('i');
                check.className = 'bi bi-check-circle-fill active-check ms-2 text-primary';
                label.appendChild(check);

                // Show toast notification
                const statusName = label.querySelector('.badge').innerText.trim();
                if (window.showToast) {
                    window.showToast("{{ __('Selected') }}", statusName, 'success');
                }
            }
        });
    });
});

document.getElementById('defaultContactsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveDefaults');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Saving...') }}`;
    btn.disabled = true;

    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => data[key] = value);

    fetch("{{ route('settings.defaults.update') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(async res => {
        const result = await res.json();
        if (res.ok && result.success) {
            showToast("{{ __('Success') }}", result.message, 'success');
        } else {
            showToast("{{ __('Error') }}", result.message || "{{ __('Failed to update defaults') }}", 'error');
        }
    })
    .catch(err => {
        showToast("{{ __('Error') }}", "{{ __('System Error') }}", 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endsection
