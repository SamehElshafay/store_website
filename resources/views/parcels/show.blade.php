@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container py-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-primary">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Parcel Details') }}</li>
                    </ol>
                </nav>
                <h2 class="fw-800 display-5 mb-0">
                    {{ $parcel->title }} <span class="premium-text">#{{ $parcel->id }}</span>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#statusUpdateModal">
                    <i class="bi bi-arrow-right-circle me-2"></i> {{ __('Change Status') }}
                </button>
                <button class="btn btn-outline-premium rounded-pill px-4" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i> {{ __('Print') }}
                </button>
                <a href="{{ route('home') }}" class="btn btn-premium rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> {{ __('Back') }}
                </a>
            </div>
        </div>

        {{-- Status Update Modal --}}
        <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-card border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-main">{{ __('Update Status for') }} #{{ $parcel->id }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="statusUpdateForm">
                        <div class="modal-body py-4">
                            <input type="hidden" name="parcel_id" value="{{ $parcel->id }}">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">{{ __('Select Next Status') }}</label>
                                <select name="status_id" class="form-select border-0 bg-dark-soft rounded-pill px-3" required>
                                    <option value="">{{ __('Choose...') }}</option>
                                    @php $currentOrder = $parcel->statusModel ? $parcel->statusModel->sort_order : 0; @endphp
                                    @foreach($statuses as $status)
                                        @if($status->sort_order > $currentOrder)
                                            <option value="{{ $status->id }}">{{ $status->display_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-2">{{ __('Movement Notes') }}</label>
                                <textarea name="notes" class="form-control border-0 bg-dark-soft rounded-4 px-3 py-2" rows="2" placeholder="{{ __('Optional internal comments...') }}"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnUpdateStatus">{{ __('Confirm Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Primary Info Cards -->
            <div class="col-lg-8">
                <!-- Status & Progress -->
                <div class="glass-container p-4 mb-4 border-0 shadow-lg">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-uppercase letter-spacing-1 small text-muted">{{ __('Current Status') }}</h5>
                        @php
                            $statusInfo = [
                                'ready' => ['bg' => 'bg-primary-soft', 'text' => 'text-primary', 'icon' => 'bi-box'],
                                'delivered' => ['bg' => 'bg-success-soft', 'text' => 'text-success', 'icon' => 'bi-check2-circle'],
                                'in_transit' => ['bg' => 'bg-warning-soft', 'text' => 'text-warning', 'icon' => 'bi-truck'],
                                'returned' => ['bg' => 'bg-secondary-soft', 'text' => 'text-secondary', 'icon' => 'bi-arrow-left-right'],
                                'damaged' => ['bg' => 'bg-danger-soft', 'text' => 'text-danger', 'icon' => 'bi-exclamation-octagon'],
                            ][$parcel->status] ?? ['bg' => 'bg-secondary-soft', 'text' => 'text-secondary', 'icon' => 'bi-circle-fill'];
                        @endphp
                        <span class="badge {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }} rounded-pill px-4 py-2 fs-6 text-capitalize">
                            <i class="bi {{ $statusInfo['icon'] }} me-2 small"></i> {{ __($parcel->status) }}
                        </span>
                    </div>
                    
                    <div class="status-timeline mt-5 px-md-5">
                        <div class="row g-0 justify-content-center timeline-row">
                            @php 
                                // Enhanced logic for dynamic statuses in timeline
                                $allSteps = \App\Models\ParcelStatus::orderBy('sort_order')->get();
                                $currentStatus = $parcel->statusModel;
                                $currentOrder = $currentStatus ? $currentStatus->sort_order : 0;
                            @endphp
                            
                            @foreach($allSteps as $step)
                                @php 
                                    $isPast = $step->sort_order < $currentOrder;
                                    $isCurrent = $step->id === $parcel->status_id;
                                    
                                    $icon = 'bi-dot';
                                    if($step->key == 'ready') $icon = 'bi-box';
                                    if($step->key == 'in_transit') $icon = 'bi-truck';
                                    if($step->key == 'delivered') $icon = 'bi-check2-circle';
                                    if($step->key == 'returned') $icon = 'bi-arrow-left-right';
                                    if($step->key == 'damaged') $icon = 'bi-exclamation-octagon';
                                @endphp
                                <div class="col text-center position-relative timeline-item {{ $isPast ? 'is-past' : '' }} {{ $isCurrent ? 'is-current' : '' }}">
                                    <div class="timeline-node mx-auto {{ $isPast || $isCurrent ? 'shadow-sm' : 'bg-dark-soft border text-muted' }}" 
                                         style="{{ $isPast || $isCurrent ? 'background-color: '.$step->color.'; color: white;' : '' }}">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                    <div class="mt-3 small fw-bold text-uppercase {{ $isCurrent ? '' : 'text-muted' }}" 
                                         style="{{ $isCurrent ? 'color: '.$step->color.';' : '' }}">
                                        {{ $step->display_name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Financial Card -->
                <div class="glass-container p-4 mb-4 border-0 shadow-lg">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="bi bi-cash-coin me-2 text-primary"></i> {{ __('Financial Tracking') }}</h5>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-dark-soft rounded-4 text-center">
                                <div class="small text-muted text-uppercase mb-1">{{ __('Delivery Price') }}</div>
                                <div class="fw-800 fs-4">{{ number_format($parcel->delivery_price, 2) }}</div>
                                <small class="text-muted">EGP</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-dark-soft rounded-4 text-center">
                                <div class="small text-muted text-uppercase mb-1">{{ __('Collection') }} (COD)</div>
                                <div class="fw-800 fs-4 text-success">{{ number_format($parcel->collection_amount, 2) }}</div>
                                <small class="text-muted">EGP</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-primary-soft rounded-4 text-center border border-primary border-opacity-10">
                                <div class="small text-primary text-uppercase mb-1">{{ __('Net Collection') }}</div>
                                <div class="fw-800 fs-4 premium-text text-primary">{{ number_format($parcel->net_collection, 2) }}</div>
                                <small class="text-muted">EGP</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics Grid -->
                <div class="glass-container p-4 border-0 shadow-lg">
                    <h5 class="fw-bold mb-4"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i> {{ __('Logistics Information') }}</h5>
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <label class="small text-muted text-uppercase d-block mb-1">{{ __('Barcodes') }}</label>
                            <div class="mb-3">
                                <span class="badge bg-dark-soft text-main font-monospace px-3 py-2 border">In: {{ $parcel->barcode_in }}</span>
                            </div>
                            @if($parcel->barcode_out)
                            <div class="mb-3">
                                <span class="badge bg-info-soft text-info font-monospace px-3 py-2 border">Out: {{ $parcel->barcode_out }}</span>
                            </div>
                            @endif
                            @if($parcel->barcode_collection)
                            <div class="mb-3">
                                <span class="badge bg-warning-soft text-warning font-monospace px-3 py-2 border">Coll: {{ $parcel->barcode_collection }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase d-block mb-1">{{ __('Invoice Number') }}</label>
                                <span class="fw-bold font-monospace">{{ $parcel->invoice_number ?? '---' }}</span>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase d-block mb-1">{{ __('Service Type') }}</label>
                                <span class="fw-bold">{{ $parcel->service_type ?? '---' }}</span>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted text-uppercase d-block mb-1">{{ __('Collection Method') }}</label>
                                <span class="badge bg-dark-soft text-capitalize">{{ __($parcel->collection_method) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Contacts & Dates -->
            <div class="col-lg-4">
                <!-- Contacts -->
                <div class="glass-container p-4 mb-4 border-0 shadow-lg">
                    <h5 class="fw-bold mb-4"><i class="bi bi-person-lines-fill me-2 text-primary"></i> {{ __('People Involved') }}</h5>
                    
                    <div class="contact-box p-3 bg-dark-soft rounded-4 mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-primary">{{ __('Processed By (Sender)') }}</h6>
                        </div>
                        <div class="ps-5">
                            <div class="fw-bold fs-5">{{ $parcel->receiver->name ?? '---' }}</div>
                            <div class="small text-muted">{{ __('System Administrator') }}</div>
                        </div>
                    </div>

                    <div class="contact-box p-3 bg-dark-soft rounded-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <h6 class="fw-bold mb-0 text-info">{{ __('Recipient') }}</h6>
                        </div>
                        <div class="ps-5">
                            <div class="fw-bold fs-5">
                                {{ $parcel->recipient_name ?? ($parcel->recipientContact ? $parcel->recipientContact->name : ($parcel->delivered_to ?? '---')) }}
                            </div>
                            <div class="small fw-medium py-1">
                                {{ $parcel->recipient_address ?? ($parcel->recipientContact ? $parcel->recipientContact->address : '---') }}
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-phone me-1"></i> 
                                {{ $parcel->recipient_phone ?? ($parcel->recipientContact ? $parcel->recipientContact->phone : '---') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates -->
                <div class="glass-container p-4 mb-4 border-0 shadow-lg">
                    <h5 class="fw-bold mb-4"><i class="bi bi-calendar-event me-2 text-primary"></i> {{ __('Timeline') }}</h5>
                    <div class="mb-3 pb-3 border-bottom border-dashed">
                        <label class="small text-muted text-uppercase d-block mb-1">{{ __('Booking Date') }}</label>
                        <span class="fw-bold">{{ $parcel->booking_date ? $parcel->booking_date->format('M d, Y') : '---' }}</span>
                    </div>
                    <div class="mb-3 pb-3 border-bottom border-dashed">
                        <label class="small text-muted text-uppercase d-block mb-1">{{ __('Received At (Logistics)') }}</label>
                        <span class="fw-bold">{{ $parcel->created_at->format('M d, Y | h:i A') }}</span>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted text-uppercase d-block mb-1">{{ __('Delivery Date (Estimated)') }}</label>
                        <span class="fw-bold text-primary">{{ $parcel->delivery_date ? $parcel->delivery_date->format('M d, Y') : '---' }}</span>
                    </div>
                </div>

                <!-- Notes -->
                @if($parcel->notes)
                <div class="glass-container p-4 border-0 shadow-lg bg-warning bg-opacity-10 border border-warning border-opacity-10">
                    <h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2"></i> {{ __('Notes') }}</h5>
                    <p class="text-muted fst-italic mb-0">"{{ $parcel->notes }}"</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .premium-text {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    .glass-container {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--border-color);
        border-radius: 2rem;
    }

    .bg-dark-soft {
        background-color: rgba(0, 0, 0, 0.03) !important;
    }
    [data-theme="dark"] .bg-dark-soft {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    .bg-primary-soft { background-color: rgba(99, 102, 241, 0.1); }
    .bg-success-soft { background-color: rgba(34, 197, 94, 0.1); }
    .bg-info-soft { background-color: rgba(14, 165, 233, 0.1); }
    .bg-warning-soft { background-color: rgba(234, 179, 8, 0.1); }
    .bg-secondary-soft { background-color: rgba(148, 163, 184, 0.1); }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1); }

    .timeline-node {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        z-index: 2;
        position: relative;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        top: 24px;
        width: 100%;
        height: 2px;
        background-color: var(--border-color);
        z-index: 0;
        opacity: 0.2;
    }

    [dir="ltr"] .timeline-item:not(:last-child)::before { left: 50%; right: auto; }
    [dir="rtl"] .timeline-item:not(:last-child)::before { right: 50%; left: auto; }

    .timeline-item.is-past::before {
        background-color: var(--primary);
        opacity: 1;
        height: 3px;
    }

    /* Premium Modal Styling */
    #statusUpdateModal .glass-card {
        background: var(--card-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--border-color) !important;
    }

    [data-theme="dark"] #statusUpdateModal .glass-card {
        background: rgba(23, 23, 33, 0.95);
    }

    #statusUpdateModal .form-select, 
    #statusUpdateModal .form-control {
        background-color: rgba(0, 0, 0, 0.05) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color) !important;
    }

    [data-theme="dark"] #statusUpdateModal .form-select, 
    [data-theme="dark"] #statusUpdateModal .form-control {
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #ffffff !important;
    }

    #statusUpdateModal .form-select option {
        background-color: var(--bg-body);
        color: var(--text-main);
    }

    [data-theme="dark"] #statusUpdateModal .form-select option {
        background-color: #1a1a2e;
        color: #ffffff;
    }

    .letter-spacing-1 { letter-spacing: 1px; }

    .border-dashed { border-style: dashed !important; }

    .dashboard-wrapper {
        position: relative;
        min-height: calc(100vh - 70px);
        overflow: hidden;
    }

    .circles-wrapper {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: -1; pointer-events: none;
    }

    .circle {
        position: absolute; border-radius: 50%;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(168, 85, 247, 0.05));
        animation: move linear infinite;
    }
    @keyframes move {
        from { transform: translate(0, 0) rotate(0deg); }
        to { transform: translate(var(--x), var(--y)) rotate(360deg); }
    }

    @media print {
        @page { size: A4 orientation; margin: 0.5cm; }
        body { background: white !important; color: black !important; font-size: 9pt !important; line-height: 1.1 !important; }
        .btn, .circles-wrapper, nav, .breadcrumb, .modal, .theme-toggle-btn, .lang-switch-auth { display: none !important; }
        .container { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .py-5 { padding-top: 0 !important; padding-bottom: 0 !important; }
        .mb-5, .mb-4, .mt-5 { margin: 5px 0 !important; }
        .dashboard-wrapper { min-height: auto !important; padding: 0 !important; }
        
        .glass-container { 
            background: white !important; 
            border: 1px solid #dee2e6 !important; 
            box-shadow: none !important; 
            border-radius: 4px !important;
            padding: 8px !important;
            margin-bottom: 8px !important;
            page-break-inside: avoid;
            backdrop-filter: none !important;
        }

        .premium-text { -webkit-text-fill-color: #000 !important; color: #000 !important; }
        
        .row { display: flex !important; flex-wrap: nowrap !important; gap: 10px !important; }
        .col-lg-8 { width: 62% !important; flex: 0 0 62% !important; }
        .col-lg-4 { width: 38% !important; flex: 0 0 38% !important; }
        
        .col-md-4, .col-md-6 { flex: 1 !important; width: auto !important; }
        
        .timeline-item::before { background-color: #eee !important; opacity: 1 !important; }
        .timeline-node { border: 1px solid #ccc !important; background: #fff !important; color: #000 !important; }
        .is-past .timeline-node, .is-current .timeline-node { background: #eee !important; }
        
        .bg-dark-soft, .bg-primary-soft, .bg-info-soft, .bg-warning-soft { 
            background: white !important; 
            border: 1px solid #efefef !important; 
        }

        .status-timeline { margin-top: 5px !important; font-size: 7.5pt !important; }
        .timeline-node { width: 20px !important; height: 20px !important; font-size: 7pt !important; }
        .timeline-item:not(:last-child)::before { top: 10px !important; height: 1px !important; }
        
        .avatar-sm { display: none !important; }
        .contact-box { padding: 5px !important; margin-bottom: 5px !important; }
        .ps-5 { padding-left: 0 !important; }
        
        h2 { font-size: 14pt !important; margin-bottom: 5px !important; }
        h5 { font-size: 10pt !important; border-bottom: 1px solid #eee; padding-bottom: 2px; margin-bottom: 5px !important; }
        h5 i { display: none !important; }
        .fs-4 { font-size: 1rem !important; }
        .fs-5 { font-size: 0.9rem !important; }
        
        .border-dashed { border: none !important; border-bottom: 1px solid #eee !important; padding-bottom: 2px !important; margin-bottom: 5px !important; }
        p.text-muted { font-size: 8.5pt !important; }
    }
</style>

<script>
// Generate animated circles
(function() {
    const wrapper = document.getElementById('circles');
    if (!wrapper) return;
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
        circle.style.animationDuration = `${Math.random() * 20 + 20}s`;
        wrapper.appendChild(circle);
    }
})();

// Status Update Logic
const updateForm = document.getElementById('statusUpdateForm');
if (updateForm) {
    updateForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnUpdateStatus');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> {{ __("Updating...") }}';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = {
            status_id: formData.get('status_id'),
            notes: formData.get('notes')
        };

        fetch(`/parcels/{{ $parcel->id }}/status`, {
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
            if (res.ok) {
                showToast("{{ __('Success') }}", result.message, 'success');
                
                // Cleanup Modal
                const modalEl = document.getElementById('statusUpdateModal');
                const inst = (window.bootstrap || bootstrap).Modal.getInstance(modalEl);
                if (inst) inst.hide();
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.style.overflow = '';
                
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast("{{ __('Error') }}", result.message || "{{ __('Failed to update status') }}", 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(err => {
            showToast("{{ __('Error') }}", "{{ __('System Error') }}", 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
}
</script>
@endsection
