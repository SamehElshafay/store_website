@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container py-4">
        <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Header Section -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5 gap-3">
                <div>
                    <h2 class="fw-800 display-6 mb-1">
                        {{ __('Welcome back') }}, <span class="premium-text">{{ Auth::user()->name }}</span>! 👋
                    </h2>
                    <p class="text-muted fs-5 mb-0">{{ __('Smart Warehouse Management System Overview') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-premium rounded-pill px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#receiveModal">
                        <i class="bi bi-box-arrow-in-down me-2"></i> {{ __('Receive Parcel') }}
                    </button>
                    <button class="btn btn-premium rounded-pill px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#deliverModal">
                        <i class="bi bi-box-arrow-up-right me-2"></i> {{ __('Register Outgoing Parcel') }}
                    </button>
                </div>
            </div>

            <!-- Status Breakdown Row -->
            <div class="row g-3 mb-4 justify-content-center">
                @foreach($statuses as $status)
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="stat-card p-3 h-100 text-center border-0 shadow-sm d-flex flex-column align-items-center justify-content-center position-relative" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.05) !important;">
                        <a href="{{ route('parcels.index', ['status' => $status->id]) }}" class="stretched-link"></a>
                        
                        {{-- Genius Plus Button --}}
                        <button class="btn-plus-genius" onclick="event.preventDefault(); event.stopPropagation(); handlePlusClick('{{ $status->id }}', '{{ $status->display_name }}', '{{ $status->modal_type }}')" title="{{ __('Quick Action') }}">
                            <i class="bi bi-plus-lg"></i>
                        </button>

                        <div class="mb-2">
                            @php
                                $statusIcon = $status->icon ?: 'bi-dot';
                            @endphp
                            <i class="bi {{ $statusIcon }} fs-3" style="color: {{ $status->color }}"></i>
                        </div>
                        <div class="fw-800 fs-4 mb-0" style="color: {{ $status->color }}">
                            {{ $status_counts[$status->id] ?? 0 }}
                        </div>
                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                            {{ $status->display_name }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-primary-soft text-primary">
                                <i class="bi bi-house-door fs-4"></i>
                            </div>
                            <h6 class="text-muted fw-bold text-uppercase mb-0 ms-3 letter-spacing-1">{{ __('Total Inventory') }}</h6>
                        </div>
                        <div class="d-flex align-items-end">
                            <h2 class="fw-800 mb-0 display-5">{{ $stats['total_stock'] }}</h2>
                            <span class="ms-2 text-muted pb-1">{{ __('Parcels') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-info-soft text-info">
                                <i class="bi bi-plus-circle fs-4"></i>
                            </div>
                            <h6 class="text-muted fw-bold text-uppercase mb-0 ms-3 letter-spacing-1">{{ __('Received Today') }}</h6>
                        </div>
                        <div class="d-flex align-items-end">
                            <h2 class="fw-800 mb-0 display-5">{{ $stats['received_today'] }}</h2>
                            <span class="ms-2 text-muted pb-1">{{ __('Parcels') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="stat-icon bg-success-soft text-success">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                            <h6 class="text-muted fw-bold text-uppercase mb-0 ms-3 letter-spacing-1">{{ __('Delivered Today') }}</h6>
                        </div>
                        <div class="d-flex align-items-end">
                            <h2 class="fw-800 mb-0 display-5">{{ $stats['delivered_today'] }}</h2>
                            <span class="ms-2 text-muted pb-1">{{ __('Parcels') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Parcels Table -->
            <div class="glass-container p-4 border-0 shadow-lg">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0 text-main"><i class="bi bi-clock-history me-2 opacity-50"></i>{{ __('Recent Movements') }}</h5>
                    <a href="{{ route('parcels.index') }}" class="btn btn-link btn-sm text-decoration-none fw-bold small p-0">{{ __('View Full Log') }} &rarr;</a>
                </div>

                {{-- Status Update Modal (Moved to Top for better stability) --}}
                <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content glass-card border-0 shadow-lg">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-main" id="modalParcelTitle"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="statusUpdateForm">
                                <div class="modal-body py-4">
                                    <input type="hidden" id="modalParcelId" name="parcel_id">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">{{ __('Select Next Status') }}</label>
                                        <select name="status_id" id="nextStatusSelect" class="form-select border-0 bg-dark-soft rounded-pill px-3" required>
                                            <option value="">{{ __('Choose...') }}</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->id }}" data-order="{{ $status->sort_order }}">{{ $status->display_name }}</option>
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="small text-uppercase border-bottom">
                        <th class="ps-4 py-3 border-0 text-muted">{{ __('Parcel / Product') }}</th>
                        <th class="py-3 border-0 text-muted">{{ __('Barcodes') }}</th>
                        <th class="py-3 border-0 text-muted">{{ __('Status') }}</th>
                        <th class="py-3 border-0 text-muted">{{ __('Handled By') }}</th>
                        <th class="py-3 border-0 text-muted">{{ __('Date') }}</th>
                        <th class="text-end pe-4 py-3 border-0 text-muted">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_parcels as $parcel)
                    <tr class="parcel-row cursor-pointer" data-id="{{ $parcel->id }}" onclick="window.location.href='/parcels/{{ $parcel->id }}'">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-soft text-primary rounded-3 me-3 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-box"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-slate-800">{{ $parcel->title }}</div>
                                    <small class="text-muted">{{ __('ID') }}: #{{ $parcel->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-medium">In: <span class="badge bg-primary-soft text-primary font-monospace">{{ $parcel->barcode_in }}</span></div>
                            @if($parcel->barcode_out)
                            <div class="small fw-medium mt-1">Out: <span class="badge bg-info-soft text-info font-monospace">{{ $parcel->barcode_out }}</span></div>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusName = $parcel->status;
                                $statusColor = '#6366f1';
                                
                                // Check if we have a relational status
                                if ($parcel->status_id) {
                                    $dbStatus = \App\Models\ParcelStatus::find($parcel->status_id);
                                    if ($dbStatus) {
                                        $statusName = $dbStatus->display_name;
                                        $statusColor = $dbStatus->color;
                                    }
                                } else {
                                    // Fallback for old records or system defaults
                                    $statusName = __($parcel->status);
                                    $statusColor = [
                                        'ready' => '#6366f1',
                                        'delivered' => '#22c55e',
                                        'in_transit' => '#eab308',
                                        'returned' => '#94a3b8',
                                        'damaged' => '#ef4444',
                                    ][$parcel->status] ?? '#6366f1';
                                }
                            @endphp
                            <span class="badge rounded-pill px-3 py-2 text-capitalize" style="background-color: {{ $statusColor }}20; color: {{ $statusColor }}; border: 1px solid {{ $statusColor }}30;">
                                <i class="bi bi-dot me-1"></i> {{ $statusName }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-bold text-slate-700">{{ $parcel->receiver->name ?? 'System' }}</div>
                            @if($parcel->delivered_to)
                            <div class="small text-muted mt-1"><i class="bi bi-person-check me-1"></i> To: {{ $parcel->delivered_to }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $parcel->created_at->format('M d, Y') }}</div>
                            <div class="small text-muted">{{ $parcel->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                @php
                                    $defaultStatus = $statuses->where('is_default', true)->first();
                                    $isDefault = $defaultStatus && $parcel->status_id == $defaultStatus->id;
                                @endphp

                                @if($isDefault)
                                <button onclick="event.preventDefault(); event.stopPropagation(); openDispatchModal({{ $parcel->id }})" class="btn btn-dispatch rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" title="{{ __('Dispatch Parcel') }}">
                                    <i class="bi bi-truck"></i>
                                </button>
                                @endif
                                <a href="/parcels/{{ $parcel->id }}" class="btn btn-dark-soft rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" data-bs-toggle="tooltip" title="{{ __('View Details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 border-0">
                            <div class="empty-state py-4">
                                <i class="bi bi-box2 fs-1 text-muted opacity-50 d-block mb-3"></i>
                                <p class="text-muted mb-0">{{ __('No recent movements found') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            </div>
        </div>
    </div>
</div>

<!-- Receive Modal (Upgraded Design) -->
<div class="modal fade" id="receiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content glass-modal border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-main d-flex align-items-center">
                    <i class="bi bi-box-seam me-2 text-primary"></i>
                    {{ __('Receive Parcel') }}
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="receiveForm">
                @csrf
                <div class="modal-body p-4 pt-0">
                    <div class="mb-4 bg-primary bg-opacity-10 p-3 rounded-4">
                        <p class="small text-primary mb-0 fw-bold"><i class="bi bi-info-circle me-1"></i> {{ __('Register a new parcel arriving at the warehouse.') }}</p>
                    </div>

                    <div class="row g-4">
                        {{-- Left Column --}}
                        <div class="col-lg-6">
                            <div class="glass-card p-3 rounded-4 h-100 border-0 shadow-sm" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05) !important;">
                                <h6 class="small fw-bold text-uppercase text-primary mb-3">{{ __('Parcel Content & Source') }}</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">{{ __('Parcel Title / Item Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control border-0 bg-dark-soft text-main" placeholder="e.g. Samsung Galaxy S24" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Scan In (Barcode)') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="barcode_in" class="form-control border-0 bg-dark-soft text-main" placeholder="Scan barcode..." required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Invoice Number') }}</label>
                                        <input type="text" name="invoice_number" class="form-control border-0 bg-dark-soft text-main" placeholder="INV-XXXXX">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted mb-2">{{ __('Sender (Source)') }} <span class="text-danger">*</span></label>
                                        <div class="custom-search-select has-search" id="senderSelectReceive">
                                            <input type="hidden" name="sender_contact_id" required>
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" placeholder="{{ __('Search senders...') }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted mb-2">{{ __('Recipient (Destination)') }} <span class="text-danger">*</span></label>
                                        <div class="custom-search-select has-search" id="recipientSelectReceive">
                                            <input type="hidden" name="recipient_contact_id" required>
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" placeholder="{{ __('Search recipients...') }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="col-lg-6">
                            <div class="glass-card p-3 rounded-4 h-100 border-0 shadow-sm" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05) !important;">
                                <h6 class="small fw-bold text-uppercase text-primary mb-3">{{ __('Logistics & Timeline') }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Booking Date') }}</label>
                                        <input type="date" name="booking_date" class="form-control border-0 bg-dark-soft text-main">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Delivery Date (Est.)') }}</label>
                                        <input type="date" name="delivery_date" class="form-control border-0 bg-dark-soft text-main">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold text-muted">{{ __('Collection Barcode') }}</label>
                                        <input type="text" name="barcode_collection" class="form-control border-0 bg-dark-soft text-main" placeholder="COLL-XXXXX">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">{{ __('Additional Notes') }}</label>
                                        <textarea name="notes" class="form-control border-0 bg-dark-soft text-main" rows="3" placeholder="{{ __('Any additional notes...') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-lg btn-link text-muted text-decoration-none px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-premium rounded-pill px-5 py-2 fw-bold shadow-lg">
                        <i class="bi bi-check2-circle me-1"></i> {{ __('Submit Entry') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Quick Status Update Modal --}}
<div class="modal fade" id="quickStatusModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-modal border-0 shadow-lg text-main">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
                    {{ __('Quick Status Update') }}: <span id="quickStatusName" class="premium-text"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickStatusForm">
                <div class="modal-body py-4">
                    <input type="hidden" id="quickStatusId" name="status_id">
                    <input type="hidden" id="quickModalType" name="modal_type">
                    
                    <div class="alert alert-info bg-info bg-opacity-10 border-0 rounded-4 small mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('Enter or scan multiple barcodes to update them all to this status at once.') }}
                    </div>

                    <!-- Scan Area -->
                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-8 col-md-9">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">{{ __('Scan or Enter Barcode') }}</label>
                            <input type="text" id="bulkScanInput" class="form-control border-0 bg-dark-soft rounded-pill px-4 py-3" placeholder="{{ __('Type or scan barcode...') }}" autocomplete="off">
                        </div>
                        <div class="col-4 col-md-3">
                            <button type="button" id="btnAddBarcode" class="btn btn-primary rounded-pill w-100 py-3 fw-bold shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Add') }}
                            </button>
                        </div>
                    </div>

                    <!-- List Area -->
                    <div class="barcode-list-container glass-card rounded-4 p-2" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-borderless align-middle mb-0" id="barcodeTable">
                            <thead class="sticky-top bg-dark" style="z-index: 10;">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-4">{{ __('Barcode') }}</th>
                                    <th class="text-end pe-4">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items added here -->
                            </tbody>
                        </table>
                        <div id="emptyBarcodeState" class="text-center py-4 text-muted">
                            <i class="bi bi-upc-scan fs-1 d-block mb-2 opacity-25"></i>
                            <small>{{ __('No barcodes added yet') }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" id="btnConfirmQuickUpdate">{{ __('Confirm Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Detail Modal for Bulk Entries is now handled by reusing existing modals --}}


<style>
    .premium-text {
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }

    h2, h5, h6, .fw-bold {
        color: var(--text-main);
    }

    .stat-card {
        background: var(--card-bg);
        border-radius: 1.5rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    .stat-card:hover { transform: translateY(-5px); }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-soft { background-color: rgba(99, 102, 241, 0.15); }
    .bg-success-soft { background-color: rgba(34, 197, 94, 0.15); }
    .bg-info-soft { background-color: rgba(14, 165, 233, 0.15); }
    .bg-warning-soft { background-color: rgba(234, 179, 8, 0.15); }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.15); }
    .bg-secondary-soft { background-color: rgba(148, 163, 184, 0.15); }
    .btn-danger-soft { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; }
    .btn-danger-soft:hover { background-color: #ef4444; color: white; }
    .btn-primary-soft { background-color: rgba(99, 102, 241, 0.1); color: #6366f1; border: none; }
    .btn-primary-soft:hover { background-color: #6366f1; color: white; }
    .btn-info-soft { background-color: rgba(14, 165, 233, 0.1); color: #0ea5e9; border: none; }
    .btn-info-soft:hover { background-color: #0ea5e9; color: white; }

    .btn-premium {
        background: var(--primary-gradient);
        color: white;
        border: none;
    }
    .btn-premium:hover { background: linear-gradient(135deg, #4f46e5, #4338ca); color: white; }
    
    .btn-outline-premium {
        border: 2px solid #6366f1;
        color: #6366f1;
        font-weight: 600;
    }
    .btn-outline-premium:hover { background: #6366f1; color: white; }

    .glass-container {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--border-color);
        border-radius: 2rem;
    }
    
    .glass-modal {
        background: var(--nav-bg);
        backdrop-filter: blur(20px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        color: var(--text-main);
    }
    
    .modal-body {
        overflow: visible !important;
    }
    
    .glass-modal, .modal-content {
        overflow: visible !important;
    }

    .form-control-lg {
        padding: 0.8rem 1.2rem;
        border-radius: 1rem;
        font-size: 1rem;
        background-color: var(--border-color) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
    }

    [data-theme="dark"] .table { color: #cbd5e1; }
    [data-theme="dark"] .text-slate-800 { color: #f1f5f9 !important; }
    [data-theme="dark"] .bg-light { background-color: rgba(255,255,255,0.05) !important; }
    [data-theme="dark"] .modal-header .btn-close { filter: invert(1); }
    
    .avatar-sm { width: 40px; height: 40px; font-size: 1.2rem; }
    
    .dashboard-wrapper {
        position: relative;
        min-height: calc(100vh - 70px);
        overflow: hidden;
    }

    .circles-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        pointer-events: none;
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(168, 85, 247, 0.15));
        animation: move linear infinite;
        filter: blur(40px);
    }

    [data-theme="dark"] .circle {
        background: linear-gradient(135deg, rgba(129, 140, 248, 0.1), rgba(192, 132, 252, 0.1));
    }

    @keyframes move {
        from { transform: translate(0, 0) rotate(0deg); }
        to { transform: translate(var(--x), var(--y)) rotate(360deg); }
    }
    
    /* Arabic Support */
    [lang="ar"] .ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
    [lang="ar"] .me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
    [lang="ar"] .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
    [lang="ar"] .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
    [lang="ar"] .text-end { text-align: left !important; }
    [lang="ar"] .ps-4 { padding-right: 1.5rem !important; padding-left: 0 !important; }
    [lang="ar"] .pe-4 { padding-left: 1.5rem !important; padding-right: 0 !important; }
    .bg-dark-soft {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }
    [data-theme="dark"] .bg-dark-soft {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    .custom-search-select {
        position: relative !important;
        z-index: 2000;
    }
    .custom-search-select:focus-within {
        z-index: 9999; /* Stay above other inputs when active */
    }
    /* Only show search icon if the container has the .has-search class */
    .custom-search-select.has-search::before {
        content: '\F52A';
        font-family: 'bootstrap-icons';
        position: absolute;
        inset-inline-start: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        opacity: 0.5;
        z-index: 5;
        transition: all 0.3s;
    }
    .custom-search-select.has-search .search-input {
        padding-inline-start: 2.75rem !important;
    }
    .custom-search-select:focus-within::before {
        color: #6366f1;
        opacity: 1;
    }
    .dropdown-results {
        position: absolute !important;
        top: calc(100% + 5px) !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 9999999 !important;
        background-color: #0f172a !important; /* Extremely solid dark blue */
        background: #0f172a !important; /* Fallback */
        max-height: 350px;
        overflow-y: auto;
        border: 2px solid #6366f1 !important; /* Stronger border for visibility */
        box-shadow: 0 30px 60px rgba(0,0,0,1) !important; /* Very heavy shadow */
        border-radius: 16px;
        padding: 10px;
        display: none;
        opacity: 1 !important; /* Force full opacity */
        visibility: hidden;
        transform: none !important; /* Remove transform to avoid rendering issues */
    }
    
    .dropdown-results:not(.results-hidden) {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .results-hidden {
        display: none !important;
    }
    .result-item {
        padding: 12px 15px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 12px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 15px;
        border: 1px solid transparent;
    }
    .result-item:hover, .result-item.active {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.2);
        transform: translateX(5px);
    }
    [lang="ar"] .result-item:hover, [lang="ar"] .result-item.active {
        transform: translateX(-5px);
    }
    
    .result-icon {
        width: 42px;
        height: 42px;
        background: rgba(99, 102, 241, 0.1);
        color: #818cf8;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: all 0.3s;
    }
    .result-item:hover .result-icon {
        background: #6366f1;
        color: white;
        transform: scale(1.1);
    }
    
    .add-new-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: rgba(99, 102, 241, 0.05);
        color: #818cf8;
        font-weight: 700;
        text-decoration: none;
        border-radius: 12px;
        margin-top: 8px;
        border: 1px dashed rgba(99, 102, 241, 0.3);
        transition: all 0.3s;
    }
    .add-new-btn:hover {
        background: rgba(99, 102, 241, 0.15);
        color: #fff;
        border-style: solid;
        border-color: #6366f1;
        transform: translateY(-2px);
    }
    
    .dropdown-results::-webkit-scrollbar { width: 6px; }
    .dropdown-results::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    
    .detail-group { margin-bottom: 0.5rem; }
    .detail-label { font-size: 0.75rem; font-weight: 800; text-uppercase: uppercase; color: #6366f1; letter-spacing: 0.5px; margin-bottom: 0.25rem; }
    .detail-value { font-size: 0.95rem; color: var(--text-main); }

    .btn-plus-genius {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        z-index: 10;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        backdrop-filter: blur(5px);
    }

    .btn-plus-genius:hover {
        background: var(--primary-gradient);
        transform: rotate(180deg) scale(1.2);
        border-color: transparent;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.6);
    }

    @keyframes genius-pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.2); }
        70% { box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }

    .btn-plus-genius {
        animation: genius-pulse 3s infinite;
    }

    .btn-plus-genius:hover {
        animation: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple Select Logic (Fixed)
    function initSimpleSelect(id) {
        console.log("Initializing SimpleSelect:", id);
        const wrapper = document.getElementById(id);
        if(!wrapper) return;
        const toggle = wrapper.querySelector('.method-toggle');
        const selectedText = wrapper.querySelector('.selected-text');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const resultsDiv = wrapper.querySelector('.dropdown-results');

        if (!toggle || !resultsDiv) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log("Toggle clicked for:", id);
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-results').forEach(d => {
                if(d !== resultsDiv) d.classList.add('results-hidden');
            });
            resultsDiv.classList.toggle('results-hidden');
        });

        resultsDiv.querySelectorAll('.result-item').forEach(item => {
            item.addEventListener('click', () => {
                selectedText.innerText = item.innerText;
                hiddenInput.value = item.getAttribute('data-value');
                resultsDiv.classList.add('results-hidden');
            });
        });
    }

    // Searchable Selects - Called FIRST to ensure it works even if other JS fails
    initSearchSelect('recipientSelectDeliver', 'recipient');

    // Initializing custom dropdowns and selects
    initSimpleSelect('methodSelect');
    initSimpleSelect('methodSelectDeliver');
    initSimpleSelect('methodSelectBulk');
    initSimpleSelect('statusSelectDeliver');

    // Initialize Search Selects
    initSearchSelect('senderSelectDeliver', 'sender');
    initSearchSelect('recipientSelectDeliver', 'recipient');
    initSearchSelect('senderSelectReceive', 'sender');
    initSearchSelect('recipientSelectReceive', 'recipient');
    initSearchSelect('senderBulkSelect', 'sender');
    initSearchSelect('recipientBulkSelect', 'recipient');

    // Initialize tooltips in a safe block
    try {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            const b = window.bootstrap || bootstrap;
            if (typeof b !== 'undefined' && b.Tooltip) return new b.Tooltip(tooltipTriggerEl);
        });
    } catch (e) {
        console.warn("Tooltips could not be initialized:", e);
    }

    // Searchable Select Logic (Bulletproof Version)
    function initSearchSelect(id, type) {
        console.log("DEBUG: Initializing initSearchSelect for ID:", id, "Type:", type);
        const wrapper = document.getElementById(id);
        if(!wrapper) {
            console.error("DEBUG ERR: Wrapper not found for ID:", id);
            return;
        }
        
        const input = wrapper.querySelector('.search-input');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const resultsDiv = wrapper.querySelector('.dropdown-results');

        if(!input || !resultsDiv) {
            console.error("DEBUG ERR: Input or resultsDiv missing in wrapper:", id);
            return;
        }

        let timeout = null;
        let selectedIndex = -1;
        const searchUrl = "{{ route('contacts.search') }}";

        const updateUI = (query) => {
            const trimmed = query.trim();
            console.log("DEBUG: updateUI called with query:", trimmed);
            if (trimmed.length === 0) {
                resultsDiv.classList.add('results-hidden');
                resultsDiv.innerHTML = '';
                return;
            }

            const createText = type === 'sender' ? "{{ __('Create New Sender') }}" : "{{ __('Create New Recipient') }}";
            const addBtnHtml = `<a href="/contacts/create?type=${type}&name=${encodeURIComponent(trimmed)}" class="add-new-btn"><i class="bi bi-plus-circle-fill"></i> ${createText}: <span class="ms-1 fw-800">${trimmed}</span></a>`;
            
            resultsDiv.innerHTML = `<div class="records-area"><div class="p-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div></div>` + addBtnHtml;
            resultsDiv.classList.remove('results-hidden');
            console.log("DEBUG: resultsDiv shown (removed results-hidden)");
        };

        async function fetchResults(query) {
            const trimmed = query.trim();
            if (trimmed.length === 0) return;

            console.log("DEBUG: Starting fetch for:", trimmed);
            try {
                const response = await fetch(`${searchUrl}?type=${type}&q=${encodeURIComponent(trimmed)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                console.log("DEBUG: Response status:", response.status);
                const data = await response.json();
                console.log("DEBUG: Data received, count:", data.length);
                
                const limitedData = data.slice(0, 10);
                const recordsArea = resultsDiv.querySelector('.records-area');
                if (!recordsArea) {
                    console.warn("DEBUG WARN: recordsArea missing in resultsDiv during fetch");
                    return;
                }

                let html = '';
                if (limitedData.length > 0) {
                    limitedData.forEach(item => {
                        html += `
                            <div class="result-item" data-id="${item.id}" data-name="${item.name}">
                                <div class="result-icon"><i class="bi bi-person-fill"></i></div>
                                <div class="flex-grow-1 text-start">
                                    <div class="fw-bold text-main">${item.name}</div>
                                    <div class="small text-muted opacity-75">${item.phone || ''}</div>
                                </div>
                                <i class="bi bi-chevron-right small opacity-25"></i>
                            </div>
                        `;
                    });
                } else {
                    console.log("DEBUG: No records found for query");
                    html = `<div class="p-3 text-center small text-muted opacity-50">{{ __('No records found') }}</div>`;
                }
                
                recordsArea.innerHTML = html;
                
                recordsArea.querySelectorAll('.result-item').forEach(el => {
                    el.addEventListener('click', () => {
                        console.log("DEBUG: Result item clicked:", el.getAttribute('data-name'));
                        input.value = el.getAttribute('data-name');
                        hiddenInput.value = el.getAttribute('data-id');
                        resultsDiv.classList.add('results-hidden');
                    });
                });
            } catch (err) {
                console.error("DEBUG ERR: Search fetch failed:", err);
            }
        }

        input.addEventListener('input', function() {
            const query = this.value;
            console.log("DEBUG: Input event fired:", query);
            updateUI(query);
            clearTimeout(timeout);
            if (query.trim().length > 0) {
                timeout = setTimeout(() => fetchResults(query), 300);
            }
        });

        input.addEventListener('focus', function() {
            console.log("DEBUG: Input focus fired, value:", this.value);
            if (this.value.trim().length > 0) {
                updateUI(this.value);
                fetchResults(this.value);
            }
        });

        const updateSelection = () => {
            const items = resultsDiv.querySelectorAll('.result-item');
            items.forEach((item, index) => {
                if (index === selectedIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest' });
                } else item.classList.remove('active');
            });
        };

        input.addEventListener('keydown', (e) => {
            if (resultsDiv.classList.contains('results-hidden')) return;
            const items = resultsDiv.querySelectorAll('.result-item');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection();
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    e.preventDefault();
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                resultsDiv.classList.add('results-hidden');
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                resultsDiv.classList.add('results-hidden');
            }
        });
    }

    // Auto-calculate Net Collection
    const deliveryInput = document.getElementById('deliveryPrice');
    const collectionInput = document.getElementById('collectionAmount');
    const netInput = document.getElementById('netCollection');

    if (deliveryInput && collectionInput && netInput) {
        const calc = () => {
            const d = parseFloat(deliveryInput.value) || 0;
            const c = parseFloat(collectionInput.value) || 0;
            netInput.value = (c - d).toFixed(2);
        };
        deliveryInput.addEventListener('input', calc);
        collectionInput.addEventListener('input', calc);
    }

    // Receive Form Submission
    const receiveForm = document.getElementById('receiveForm');
    if (receiveForm) {
        receiveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Saving...') }}`;
            btn.disabled = true;

            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if(value && key !== '_token') data[key] = value;
            });

            fetch('/parcels', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(async res => {
                const result = await res.json();
                if (res.ok) {
                    showToast("{{ __('Success') }}", result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    let details = '';
                    if (result.errors) {
                        details = Object.values(result.errors).flat().join('<br>');
                    }
                    showToast("{{ __('Failed') }}", result.message || "{{ __('Validation Error') }}", 'error', details);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }

    // Net Collection calculation for both modals
    function setupNetCollection(prefix = '') {
        const delPrice = document.querySelector(prefix + (prefix ? ' .bulk-delivery-price' : ' #deliveryPrice'));
        const collAmount = document.querySelector(prefix + (prefix ? ' .bulk-collection-amount' : ' #collectionAmount'));
        const netColl = document.querySelector(prefix + (prefix ? ' .bulk-net-collection' : ' #netCollection'));

        if (!delPrice || !collAmount || !netColl) return;

        const calc = () => {
            const d = parseFloat(delPrice.value) || 0;
            const c = parseFloat(collAmount.value) || 0;
            netColl.value = (c - d).toFixed(2);
        };

        delPrice.addEventListener('input', calc);
        collAmount.addEventListener('input', calc);
    }

    setupNetCollection(); // For deliverModal
    setupNetCollection('#bulkParcelDetailsModal'); // For bulk modal

    // --- Quick Status Modal Logic ---
    const quickModalEl = document.getElementById('quickStatusModal');
    const bulkDetailModalEl = document.getElementById('bulkParcelDetailsModal');
    let quickModal = null;
    let bulkDetailModal = null;
    const bs = window.bootstrap || bootstrap;
    
    if (quickModalEl && bs) quickModal = new bs.Modal(quickModalEl);
    if (bulkDetailModalEl && bs) bulkDetailModal = new bs.Modal(bulkDetailModalEl);

    // Store details for each row
    let bulkRowsData = {};

    window.handlePlusClick = function(statusId, statusName, modalType) {
        document.getElementById('quickStatusId').value = statusId;
        document.getElementById('quickStatusName').innerText = statusName;
        document.getElementById('quickModalType').value = modalType || 'receive';
        bulkRowsData = {}; // Reset data
        
        const tbody = document.querySelector('#barcodeTable tbody');
        tbody.innerHTML = '';
        document.getElementById('emptyBarcodeState').classList.remove('d-none');
        
        if (quickModal) quickModal.show();
        setTimeout(() => document.getElementById('bulkScanInput').focus(), 500);
    };

    const bulkScanInput = document.getElementById('bulkScanInput');
    const btnAddBarcode = document.getElementById('btnAddBarcode');

    if (bulkScanInput) {
        bulkScanInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBarcodeToList();
            }
        });
    }

    if (btnAddBarcode) {
        btnAddBarcode.addEventListener('click', addBarcodeToList);
    }

    async function addBarcodeToList() {
        const barcode = bulkScanInput.value.trim();
        if (!barcode) return;

        const modalType = document.getElementById('quickModalType').value;

        // 1. Check if exists in current UI list
        const existing = Array.from(document.querySelectorAll('.barcode-val')).map(el => el.innerText.trim());
        if (existing.includes(barcode)) {
            showToast("{{ __('Attention') }}", "{{ __('Barcode already added to this list') }}", 'warning');
            bulkScanInput.value = '';
            bulkScanInput.focus();
            return;
        }

        // 2. Database Check with Loading State
        const originalBtnHtml = btnAddBarcode.innerHTML;
        btnAddBarcode.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> {{ __('Checking...') }}`;
        btnAddBarcode.disabled = true;
        bulkScanInput.disabled = true;

        const rowIndex = Date.now();
        try {
            const findUrl = "{{ route('parcels.find', ['barcode' => ':BARCODE']) }}".replace(':BARCODE', encodeURIComponent(barcode));
            const res = await fetch(findUrl, { headers: { 'Accept': 'application/json' } });
            
            if (modalType === 'receive') {
                // For NEW parcels, reject if already in DB
                if (res.status === 200 || res.status === 400) {
                    showToast("{{ __('Rejected') }}", "{{ __('This barcode is already registered in the system.') }}", 'error');
                    resetAddState(originalBtnHtml);
                    return;
                }
            } else {
                // For DISPATCH/UPDATE, reject if NOT in DB
                if (res.status === 404) {
                    showToast("{{ __('Not Found') }}", "{{ __('This barcode does not exist in the system.') }}", 'error');
                    resetAddState(originalBtnHtml);
                    return;
                }
                // If found, pre-link the data for the final submission
                if (res.ok) {
                    const result = await res.json();
                    if (result.success && result.data) {
                        bulkRowsData[rowIndex] = result.data;
                    }
                }
            }
        } catch(e) {
            console.error("Database check failed", e);
        }

        // 3. Success - Add to Table
        const tbody = document.querySelector('#barcodeTable tbody');
        
        const row = document.createElement('tr');
        row.setAttribute('data-row-index', rowIndex);
        row.className = 'border-bottom border-white border-opacity-5';
        
        row.innerHTML = `
            <td class="ps-4">
                <span class="fw-bold barcode-val">${barcode}</span>
                <input type="hidden" class="barcode-input" value="${barcode}">
            </td>
            <td class="text-end pe-4">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-info-soft rounded-pill btn-sm px-3 btn-add-info" data-row="${rowIndex}">
                        <i class="bi bi-info-circle me-1"></i> <small>{{ __('Info') }}</small>
                    </button>
                    <button type="button" class="btn btn-danger-soft rounded-circle btn-sm btn-remove-barcode" style="width: 32px; height: 32px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        
        tbody.prepend(row);
        document.getElementById('emptyBarcodeState').classList.add('d-none');
        showToast("{{ __('Success') }}", "{{ __('Barcode added to list') }}", 'success');
        resetAddState(originalBtnHtml);
    }

    function resetAddState(originalHtml) {
        btnAddBarcode.innerHTML = originalHtml;
        btnAddBarcode.disabled = false;
        bulkScanInput.disabled = false;
        bulkScanInput.value = '';
        bulkScanInput.focus();
    }
    // State for Bulk Context
    let currentBulkRowIndex = null;
    let isBulkContext = false;

    // Delegate actions
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-barcode')) {
            const row = e.target.closest('tr');
            delete bulkRowsData[row.getAttribute('data-row-index')];
            row.remove();
            if (document.querySelectorAll('#barcodeTable tbody tr').length === 0) {
                document.getElementById('emptyBarcodeState').classList.remove('d-none');
            }
        }

        if (e.target.closest('.btn-add-info')) {
            const btn = e.target.closest('.btn-add-info');
            currentBulkRowIndex = btn.getAttribute('data-row');
            const row = btn.closest('tr');
            const barcode = row.querySelector('.barcode-input').value;
            const modalType = document.getElementById('quickModalType').value;
            isBulkContext = true;

            if (!barcode.trim()) {
                showToast("{{ __('Attention') }}", "{{ __('Please enter a barcode first') }}", 'warning');
                return;
            }

            // Decide which modal to open
            const modalId = (modalType === 'receive' ? 'receiveModal' : 'deliverModal');
            const formId = (modalType === 'receive' ? 'receiveForm' : 'deliverForm');
            const barcodeFieldName = 'barcode_in'; // Both use barcode_in as the lookup/primary field

            // Pre-fill barcode
            const form = document.getElementById(formId);
            const barcodeField = form.querySelector(`[name="${barcodeFieldName}"]`);
            if (barcodeField) barcodeField.value = barcode;

            // Fetch data
            fetch(`/parcels/find/${barcode}`)
                .then(res => res.json())
                .then(result => {
                    const senderSelectId = (modalType === 'receive' ? 'senderSelectReceive' : 'senderSelectDeliver');
                    const recipientSelectId = (modalType === 'receive' ? 'recipientSelectReceive' : 'recipientSelectDeliver');

                    if (result.success && result.data) {
                        const p = result.data;
                        // Fill all fields
                        Object.keys(p).forEach(key => {
                            const field = form.querySelector(`[name="${key}"]`);
                            if (field) {
                                field.value = p[key] || '';
                                
                                // Handle Custom Search Selects
                                if (key === 'sender_contact_id' || key === 'recipient_contact_id') {
                                    const selectWrapperId = (key === 'sender_contact_id' ? senderSelectId : recipientSelectId);
                                    const selectWrapper = document.getElementById(selectWrapperId);
                                    if (selectWrapper && p[key.replace('_id', '')]) {
                                        const contact = p[key.replace('_id', '')];
                                        const textEl = selectWrapper.querySelector('.selected-text');
                                        const inputEl = selectWrapper.querySelector('.search-input');
                                        if(textEl) textEl.innerText = contact.name;
                                        if(inputEl) inputEl.value = contact.name;
                                    }
                                }
                            }
                        });
                    } else {
                        // Load from local state if already edited
                        const data = bulkRowsData[currentBulkRowIndex] || {};
                        Object.keys(data).forEach(key => {
                            const field = form.querySelector(`[name="${key}"]`);
                            if (field) field.value = data[key] || '';
                        });
                        
                        // Clear custom selects if no data
                        [senderSelectId, recipientSelectId].forEach(id => {
                            const sw = document.getElementById(id);
                            if (sw) {
                                sw.querySelector('.selected-text').innerText = "{{ __('Search...') }}";
                                sw.querySelector('.search-input').value = '';
                                sw.querySelector('input[type="hidden"]').value = '';
                            }
                        });
                    }
                    const modalEl = document.getElementById(modalId);
                    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    
                    if (quickModal) quickModal.hide();
                    bsModal.show();

                    modalEl.addEventListener('hidden.bs.modal', function() {
                        if (quickModal && !document.querySelector('.modal.show')) {
                            quickModal.show();
                        }
                    }, { once: true });
                })
                .catch(() => {
                    const modalEl = document.getElementById(modalId);
                    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (quickModal) quickModal.hide();
                    bsModal.show();
                    
                    modalEl.addEventListener('hidden.bs.modal', function() {
                        if (quickModal && !document.querySelector('.modal.show')) {
                            quickModal.show();
                        }
                    }, { once: true });
                });
        }
    });

    // Intercept Form Submissions for Bulk
    ['receiveForm', 'deliverForm'].forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                if (isBulkContext) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    
                    const formData = new FormData(this);
                    const data = {};
                    formData.forEach((v, k) => data[k] = v);
                    bulkRowsData[currentBulkRowIndex] = data;
                    
                    const btn = document.querySelector(`.btn-add-info[data-row="${currentBulkRowIndex}"]`);
                    if (btn) {
                        btn.classList.replace('btn-info-soft', 'btn-danger');
                        btn.classList.add('text-white');
                        btn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> <small>{{ __('Info Added') }}</small>`;
                    }

                    const modalId = (formId === 'receiveForm' ? 'receiveModal' : 'deliverModal');
                    const modalEl = document.getElementById(modalId);
                    const inst = bootstrap.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                    
                    isBulkContext = false;
                    currentBulkRowIndex = null;
                }
            }, true);
        }
    });



    // Final Bulk Submit
    const quickForm = document.getElementById('quickStatusForm');
    if (quickForm) {
        quickForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const statusId = document.getElementById('quickStatusId').value;
            const rows = document.querySelectorAll('#barcodeTable tbody tr');
            const payload = [];

            rows.forEach(row => {
                const idx = row.getAttribute('data-row-index');
                const barcode = row.querySelector('.barcode-input').value;
                if (barcode && barcode.trim()) {
                    payload.push({ barcode: barcode, details: bulkRowsData[idx] || {} });
                }
            });

            if (payload.length === 0) {
                showToast("{{ __('Attention') }}", "{{ __('Please add at least one barcode') }}", 'warning');
                return;
            }

            const btn = document.getElementById('btnConfirmQuickUpdate');
            const original = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Processing...') }}`;
            btn.disabled = true;

            fetch("{{ route('parcels.bulk.register') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ parcels: payload, status_id: statusId })
            })
            .then(async res => {
                const result = await res.json();
                if (res.ok && result.success) {
                    showToast("{{ __('Success') }}", result.message, 'success');
                    if (quickModal) quickModal.hide();
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showToast("{{ __('Warning') }}", result.message || "{{ __('Error') }}", 'warning', result.errors ? result.errors.join('<br>') : null);
                    btn.innerHTML = original;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                showToast("{{ __('Error') }}", "{{ __('System Error') }}", 'error');
                btn.innerHTML = original;
                btn.disabled = false;
            });
        });
    }
});
</script>

<script>
let statusModal;

document.addEventListener('DOMContentLoaded', function() {
    const statusModalEl = document.getElementById('statusUpdateModal');
    if (statusModalEl) {
        statusModalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-parcel-id');
            const title = button.getAttribute('data-parcel-title');
            const currentOrder = parseInt(button.getAttribute('data-current-order') || 0);

            document.getElementById('modalParcelId').value = id;
            document.getElementById('modalParcelTitle').innerText = title;
            
            const select = document.getElementById('nextStatusSelect');
            const options = select.querySelectorAll('option[data-order]');
            
            let availableCount = 0;
            options.forEach(opt => {
                const order = parseInt(opt.getAttribute('data-order'));
                if (order > currentOrder) {
                    opt.style.display = 'block';
                    availableCount++;
                } else {
                    opt.style.display = 'none';
                }
            });

            select.value = "";
        });
    }
});

document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('modalParcelId').value;
    const btn = document.getElementById('btnUpdateStatus');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Updating...') }}`;
    btn.disabled = true;

    const data = {
        status_id: document.getElementById('nextStatusSelect').value,
        notes: this.querySelector('textarea[name="notes"]').value
    };

    fetch(`/parcels/${id}/status`, {
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
            
            // Safe Modal Hide
            try {
                const modalEl = document.getElementById('statusUpdateModal');
                const b = window.bootstrap || bootstrap;
                if (b && modalEl) {
                    const inst = b.Modal.getInstance(modalEl);
                    if (inst) inst.hide();
                }
            } catch (v_err) { console.warn("Modal hide failed softly", v_err); }

            setTimeout(() => {
                window.location.reload();
            }, 1000);
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
</script>

<style>
/* Premium Modal Styling */
#statusUpdateModal .glass-card {
    background: rgba(23, 23, 33, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.text-main {
    color: #ffffff !important;
}

.form-select, 
.form-control {
    background-color: rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.form-select option {
    background-color: #1a1a2e;
    color: #ffffff;
}

.form-select:focus, 
.form-control:focus {
    background-color: rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25) !important;
    border-color: #6366f1 !important;
}

#statusUpdateModal .modal-header .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
</style>

<script>
(function() {
    const wrapper = document.getElementById('circles');
    if (!wrapper) return;
    const circleCount = 15;

    for (let i = 0; i < circleCount; i++) {
        const circle = document.createElement('div');
        circle.className = 'circle';
        
        const size = Math.random() * 400 + 150;
        circle.style.width = `${size}px`;
        circle.style.height = `${size}px`;
        circle.style.left = `${Math.random() * 100}%`;
        circle.style.top = `${Math.random() * 100}%`;
        circle.style.setProperty('--x', `${(Math.random() - 0.5) * 600}px`);
        circle.style.setProperty('--y', `${(Math.random() - 0.5) * 600}px`);
        circle.style.animationDuration = `${Math.random() * 30 + 20}s`;
        
        wrapper.appendChild(circle);
    }
})();
</script>
</div>
@include('parcels.partials.dispatch_modal')
@endsection
