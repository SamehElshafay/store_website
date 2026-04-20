@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container-fluid py-4 px-4">

        @push('modals')
        {{-- Status Update Modal (Moved to Stack for better stability) --}}
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
        @endpush
        
@push('modals')
        <!-- Bulk Status Modal -->
        <div class="modal fade" id="globalBulkStatusModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-card border-0 shadow-lg text-main-responsive">
                    <div class="modal-header border-0">
                        <h5 class="modal-title font-cairo fw-bold" id="globalBulkStatusModalLabel">
                            <i class="bi bi-layers text-primary me-2"></i>
                            {{ __('Bulk Status Update') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="mb-4">
                            <div class="bg-main-soft rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="bi bi-arrow-repeat text-primary fs-2"></i>
                            </div>
                            <h5 class="fw-bold">{{ __('Update status for') }} <span id="bulkUpdateCount" class="text-primary">0</span> {{ __('parcels') }}</h5>
                            <p class="text-muted">{{ __('Select the new status to apply to all selected items.') }}</p>
                        </div>

                        <div class="form-group text-start">
                            <label class="form-label small fw-bold text-muted">{{ __('New Status') }}</label>
                            <select id="newBulkStatus" class="form-select custom-select-dark bg-dark-soft border-white-10 text-white">
                                <option value="">{{ __('Select Status') }}</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-white-10">
                        <button type="button" class="btn btn-dark-soft px-4 rounded-pill" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="button" id="confirmBulkStatusBtn" class="btn btn-main px-5 rounded-pill shadow-main text-white">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
@endpush

        {{-- Header Section --}}
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6">
                <div>
                    <h1 class="h2 fw-bold text-main-responsive mb-1" style="color: var(--text-main);">
                        <i class="bi bi-journal-text me-2 text-primary"></i> {{ __('Parcel Movements Log') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Comprehensive record of all incoming and outgoing shipments') }}</p>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="{{ route('parcels.export') }}" class="btn btn-outline-primary rounded-pill px-4 shadow-sm border-0 me-2">
                    <i class="bi bi-file-earmark-excel me-1"></i> {{ __('Export Excel') }}
                </a>
                <a href="{{ route('home') }}" class="btn btn-light rounded-pill px-4 shadow-sm border-0">
                    <i class="bi bi-house-door me-1"></i> {{ __('Dashboard') }}
                </a>
            </div>
        </div>

        {{-- Filter Panel --}}
        <div class="glass-card p-4 rounded-4 mb-4 border-0 shadow-sm">
            <form action="{{ route('parcels.index') }}" method="GET" id="filterForm">
                <div class="row g-3 align-items-end">
                    {{-- Quick Periods --}}
                    <div class="col-xl-4 col-lg-12">
                        <label class="form-label small fw-bold text-muted d-block mb-2">{{ __('Quick Filters') }}</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm rounded-pill px-3 {{ !request('period') || request('period') == 'all' ? 'btn-primary' : 'btn-dark-soft' }}" onclick="setPeriod('all')">{{ __('All') }}</button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 {{ request('period') == 'today' ? 'btn-primary' : 'btn-dark-soft' }}" onclick="setPeriod('today')">{{ __('Today') }}</button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 {{ request('period') == 'week' ? 'btn-primary' : 'btn-dark-soft' }}" onclick="setPeriod('week')">{{ __('This Week') }}</button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 {{ request('period') == 'month' ? 'btn-primary' : 'btn-dark-soft' }}" onclick="setPeriod('month')">{{ __('This Month') }}</button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 {{ request('period') == 'year' ? 'btn-primary' : 'btn-dark-soft' }}" onclick="setPeriod('year')">{{ __('This Year') }}</button>
                            <input type="hidden" name="period" id="periodInput" value="{{ request('period', 'all') }}">
                        </div>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-xl-4 col-md-6">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-2 px-1">{{ __('Date Range') }}</label>
                        <div class="input-group">
                            <input type="date" name="from_date" class="form-control border-0 bg-dark-soft rounded-start-pill" value="{{ request('from_date') }}">
                            <span class="input-group-text border-0 bg-dark-soft px-1"><i class="bi bi-arrow-right small"></i></span>
                            <input type="date" name="to_date" class="form-control border-0 bg-dark-soft rounded-end-pill" value="{{ request('to_date') }}">
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="col-xl-3 col-md-6">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-2 px-1">{{ __('Search Anything') }}</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 opacity-50"></i>
                            <input type="text" name="search" class="form-control border-0 bg-dark-soft ps-5 rounded-pill live-filter" value="{{ request('search') }}" placeholder="{{ __('Search barcode, title...') }}">
                        </div>
                    </div>

                    <div class="col-xl-1">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-circle shadow-sm w-px-50 h-px-50 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" title="{{ __('Search') }}">
                                <i class="bi bi-arrow-repeat fs-5"></i>
                            </button>
                            <a href="{{ route('parcels.index') }}" class="btn btn-dark-soft rounded-circle shadow-sm w-px-50 h-px-50 d-flex align-items-center justify-content-center" data-bs-toggle="tooltip" title="{{ __('Clear All Filters') }}">
                                <i class="bi bi-x-lg fs-5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="collapse {{ request()->hasAny(['title', 'recipient', 'status', 'method']) ? 'show' : '' }}" id="advancedFilters">
                    <div class="row g-3 pt-4">
                        <div class="col-md-3">
                            <input type="text" name="title" class="form-control form-control-sm border-0 bg-dark-soft rounded-pill px-3 live-filter" placeholder="{{ __('Item Title') }}" value="{{ request('title') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="recipient" class="form-control form-control-sm border-0 bg-dark-soft rounded-pill px-3 live-filter" placeholder="{{ __('Recipient Name') }}" value="{{ request('recipient') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select form-select-sm border-0 bg-dark-soft rounded-pill px-3 live-filter">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="method" class="form-select form-select-sm border-0 bg-dark-soft rounded-pill px-3 live-filter">
                                <option value="">{{ __('All Methods') }}</option>
                                <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                                <option value="card" {{ request('method') == 'card' ? 'selected' : '' }}>{{ __('Card') }}</option>
                                <option value="transfer" {{ request('method') == 'transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-link text-muted extra-small text-uppercase fw-bold text-decoration-none" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="bi bi-sliders me-1"></i> {{ __('Toggle Advanced Column Filters') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Results Table Section --}}
        <div class="glass-container border-0 shadow-lg overflow-hidden" id="parcelTableContainer">
            @include('parcels.partials.table')
        </div>
        <!-- Bulk Actions Floating Bar -->
        <div id="bulkActionsBar" class="glass-card bulk-actions-bar position-fixed bottom-0 start-50 translate-middle-x mb-4 shadow-lg border border-white-10 py-3 px-4 d-none animate__animated animate__fadeInUp" style="z-index: 1030; min-width: 400px; border-radius: 20px;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="selection-count-badge bg-main text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <span id="selectedCountText">0</span>
                    </div>
                    <div>
                        <h6 class="mb-0 text-white">{{ __('Selected Parcels') }}</h6>
                        <small id="selectionStatusMsg" class="text-white-50">{{ __('Choose items to update') }}</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button id="cancelBulkBtn" class="btn btn-outline-light border-white-10 rounded-pill px-4">
                        {{ __('Cancel') }}
                    </button>
                    <button id="triggerBulkStatusBtn" class="btn btn-main rounded-pill px-4 text-white" data-bs-toggle="modal" data-bs-target="#globalBulkStatusModal">
                        <i class="fas fa-edit me-1"></i> {{ __('Change Status') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let statusModal;

// --- CRITICAL: GLOBAL SELECTION LOGIC ---
// This is moved to window to ensure it works even if AJAX reloads the table
window.toggleAllParcels = function(master) {
    const isChecked = master.checked;
    const items = document.querySelectorAll('.parcel-checkbox');
    items.forEach(cb => {
        cb.checked = isChecked;
    });
    window.updateBulkBar();
};

window.updateBulkBar = function() {
    const checkboxes = document.querySelectorAll('.parcel-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const triggerBulkBtn = document.getElementById('triggerBulkStatusBtn');
    const selectionStatusMsg = document.getElementById('selectionStatusMsg');
    
    if (!bulkBar) return;

    if (count > 0) {
        bulkBar.classList.remove('d-none');
        bulkBar.style.setProperty('display', 'block', 'important');
        
        const countText = document.getElementById('selectedCountText');
        if (countText) countText.innerText = count;

        const bulkUpdateCountElem = document.getElementById('bulkUpdateCount');
        if (bulkUpdateCountElem) bulkUpdateCountElem.innerText = count;

        // Validate status consistency
        const statusIds = Array.from(checkboxes).map(cb => {
            const tr = cb.closest('tr');
            return tr ? tr.dataset.statusId : null;
        }).filter(id => id !== null);

        const uniqueStatuses = [...new Set(statusIds)];

        if (uniqueStatuses.length > 1) {
            if (selectionStatusMsg) {
                selectionStatusMsg.innerText = "{{ __('Mixed statuses selected. All must match.') }}";
                selectionStatusMsg.className = "text-danger small";
            }
            if (triggerBulkBtn) {
                triggerBulkBtn.disabled = true;
                triggerBulkBtn.classList.add('opacity-50');
            }
        } else {
            if (selectionStatusMsg) {
                selectionStatusMsg.innerText = "{{ __('All selected items have the same status.') }}";
                selectionStatusMsg.className = "text-white-50 small text-success"; 
            }
            if (triggerBulkBtn) {
                triggerBulkBtn.disabled = false;
                triggerBulkBtn.classList.remove('opacity-50');
            }
        }
    } else {
        bulkBar.style.setProperty('display', 'none', 'important');
    }
};

function setPeriod(p) {
    const input = document.getElementById('periodInput');
    if (input) {
        input.value = p;
        document.getElementById('filterForm')?.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // --- Global Instances ---
    const b = window.bootstrap || bootstrap;
    
    // --- Status Update Modal Logic ---
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
            
            if (availableCount === 0) {
                console.warn("No available next statuses for this parcel.");
            }
        });
    }

    // Status Update Form Submit
    const statusForm = document.getElementById('statusUpdateForm');
    if (statusForm) {
        statusForm.addEventListener('submit', function(e) {
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
                    
                    if (statusModalEl) {
                        const inst = (window.bootstrap || bootstrap).Modal.getInstance(statusModalEl);
                        if (inst) inst.hide();
                    }
                    // Clean up backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';

                    setTimeout(() => { window.location.reload(); }, 800);
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

    // --- Filtering & Table Logic ---
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

    // Initialize Tooltips if bootstrap exists
    try {
        if(window.bootstrap) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        }
    } catch(e) {}

    // Live Debounced Filter logic
    const filterForm = document.getElementById('filterForm');
    const liveFilters = document.querySelectorAll('.live-filter');
    const dateInputs = filterForm.querySelectorAll('input[type="date"]');

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    const autoSubmit = debounce(() => {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const url = `${filterForm.action}?${params.toString()}`;

        // Update URL
        window.history.pushState({}, '', url);

        // Fetch AJAX
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error('Search failed');
            return res.text();
        })
        .then(html => {
            document.getElementById('parcelTableContainer').innerHTML = html;
            
            // Re-init state for bulk actions
            if (typeof updateBulkBar === 'function') updateBulkBar();
            
            // Re-init Tooltips for new content
            if(window.bootstrap) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
            }
        })
        .catch(err => {
            console.error(err);
            showToast("{{ __('Error') }}", "{{ __('Failed to update results') }}", 'error');
        });
    }, 600);

    // Initial event listeners for live filters
    liveFilters.forEach(el => {
        const eventType = el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(eventType, autoSubmit);
    });

    dateInputs.forEach(el => {
        el.addEventListener('change', autoSubmit);
    });

    // Handle AJAX Pagination
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;
            
            window.history.pushState({}, '', url);
            
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (!res.ok) throw new Error('Pagination failed');
                return res.text();
            })
            .then(html => {
                document.getElementById('parcelTableContainer').innerHTML = html;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Re-init state for bulk actions
                if (typeof updateBulkBar === 'function') updateBulkBar();

                // Re-init tooltips
                if(window.bootstrap) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                }
            })
            .catch(err => {
                console.error(err);
                showToast("{{ __('Error') }}", "{{ __('Failed to load page') }}", 'error');
            });
        }
    });

    // Handle Form Submit to avoid reload
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        autoSubmit();
    });

    // --- Bulk Actions Logic ---
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCountSpan = document.getElementById('selectedCountText');
    const selectionStatusMsg = document.getElementById('selectionStatusMsg');
    const triggerBulkBtn = document.getElementById('triggerBulkStatusBtn');
    
    // Use dynamic modal initiation to avoid errors if not loaded yet
    const getBulkModal = () => {
        const elem = document.getElementById('bulkStatusModal');
        return elem ? new bootstrap.Modal(elem) : null;
    };

    // --- Global Visibility for AJax updates (Already defined above) ---
    
    // Delegation for individual checkboxes since table is dynamic
    // Handling BOTH change and click to ensure it works regardless of theme/browser behavior
    const handleCheckboxInteraction = (e) => {
        if (e.target && e.target.classList.contains('parcel-checkbox')) {
            if (window.updateBulkBar) window.updateBulkBar();
            
            // Sync the Select All Checkbox state
            const all = document.querySelectorAll('.parcel-checkbox');
            const checkedBoxes = document.querySelectorAll('.parcel-checkbox:checked');
            const master = document.getElementById('selectAllParcels');
            if (master) {
                master.checked = all.length > 0 && all.length === checkedBoxes.length;
                master.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < all.length;
            }
        }
    };

    document.addEventListener('change', handleCheckboxInteraction);
    document.addEventListener('click', function(e) {
        // Handle checkbox interaction
        handleCheckboxInteraction(e);

        // Handle Row Click (optional but nice)
        const row = e.target.closest('.parcel-row');
        if (row && !e.target.closest('.parcel-checkbox') && !e.target.closest('a') && !e.target.closest('button')) {
            const cb = row.querySelector('.parcel-checkbox');
            if (cb) {
                cb.checked = !cb.checked;
                window.updateBulkBar();
                
                // Sync master
                const all = document.querySelectorAll('.parcel-checkbox');
                const checked = document.querySelectorAll('.parcel-checkbox:checked');
                const master = document.getElementById('selectAllParcels');
                if (master) {
                    master.checked = all.length > 0 && all.length === checked.length;
                    master.indeterminate = checked.length > 0 && checked.length < all.length;
                }
            }
        }
    });

    document.getElementById('cancelBulkBtn')?.addEventListener('click', function() {
        document.querySelectorAll('.parcel-checkbox').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAllParcels');
        if(selectAll) selectAll.checked = false;
        updateBulkBar();
    });

    // Modal trigger handled by data-bs-attributes for maximum reliability
    // But we keep the instance logic for hiding

    document.getElementById('confirmBulkStatusBtn')?.addEventListener('click', function() {
        const checked = document.querySelectorAll('.parcel-checkbox:checked');
        const ids = Array.from(checked).map(cb => cb.value);
        const newStatusId = document.getElementById('newBulkStatus').value;

        if (!newStatusId) {
            showToast("{{ __('Error') }}", "{{ __('Please select a new status') }}", 'error');
            return;
        }

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch("{{ route('parcels.bulk.status') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                parcel_ids: ids,
                status_id: newStatusId
            })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.success) {
                showToast("{{ __('Success') }}", data.message, 'success');
                
                const modalEl = document.getElementById('globalBulkStatusModal');
                const inst = (window.bootstrap || bootstrap).Modal.getInstance(modalEl);
                if (inst) inst.hide();
                
                // Manual backup cleanup for backdrop
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                if (window.updateBulkBar) {
                    document.querySelectorAll('.parcel-checkbox').forEach(cb => cb.checked = false);
                    window.updateBulkBar();
                }
                
                if (typeof autoSubmit === 'function') autoSubmit();
                else window.location.reload();
            } else {
                const errorMsg = data.message || "{{ __('Validation Error') }}";
                const details = data.errors ? data.errors.join('<br>') : null;
                showToast("{{ __('Warning') }}", errorMsg, 'warning', details);
            }
        })
        .catch(err => {
            console.error(err);
            showToast("{{ __('Error') }}", "{{ __('An error occurred') }}", 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
});
</script>

<style>
.w-px-50 { width: 50px; }
.h-px-50 { height: 50px; }
.h-px-45 { height: 45px; }
.w-px-45 { width: 45px; }
.btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
.extra-small { font-size: 0.7rem; }
.bg-dark-soft-card { background: rgba(0,0,0,0.02); }
[data-theme="dark"] .bg-dark-soft-card { background: rgba(255,255,255,0.02); }

.btn-dark-soft {
    background: rgba(0, 0, 0, 0.05) !important;
    color: var(--text-main) !important;
    border: none !important;
}

[data-theme="dark"] .btn-dark-soft {
    background: rgba(255, 255, 255, 0.05) !important;
    color: white !important;
}

.btn-dark-soft:hover {
    background: rgba(99, 102, 241, 0.1) !important;
    color: #6366f1 !important;
}

.bg-dark-soft {
    background: rgba(0, 0, 0, 0.05) !important;
}

[data-theme="dark"] .bg-dark-soft {
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Premium Modal Styling */
#statusUpdateModal .glass-card {
    background: rgba(43, 43, 63, 0.4);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.bulk-actions-bar {
    background: white !important;
    color: #1e293b !important;
    backdrop-filter: blur(15px);
    border: 1px solid rgba(99, 102, 241, 0.2) !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
}

.bulk-actions-bar h6, 
.bulk-actions-bar small {
    color: #1e293b !important;
}

[data-theme="dark"] .bulk-actions-bar {
    background: rgba(23, 31, 51, 0.9) !important;
    color: white !important;
    border: 1px solid rgba(114, 110, 252, 0.3) !important;
}

[data-theme="dark"] .bulk-actions-bar h6 {
    color: white !important;
}
[data-theme="dark"] .bulk-actions-bar small {
    color: rgba(255,255,255,0.6) !important;
}

[dir="rtl"] .bulk-actions-bar {
    left: 50% !important;
    right: auto !important;
    transform: translateX(50%) !important;
}

.custom-check .form-check-input {
    width: 20px;
    height: 20px;
    background-color: rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.1);
    cursor: pointer;
}

[data-theme="dark"] .custom-check .form-check-input {
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.custom-check .form-check-input:checked {
    background-color: var(--main-color);
    border-color: var(--main-color);
}

.text-main-responsive {
    color: var(--text-main) !important;
}

.form-select, 
.form-control {
    background-color: rgba(0, 0, 0, 0.03) !important;
    color: var(--text-main) !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
}

[data-theme="dark"] .form-select, 
[data-theme="dark"] .form-control {
    background-color: rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.form-select option {
    background-color: var(--bg-body);
    color: var(--text-main);
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

.pagination { margin-bottom: 0; justify-content: center; gap: 5px; }
.page-link { border: none; background: rgba(0,0,0,0.05); color: var(--text-main); border-radius: 8px !important; padding: 8px 15px; }
.page-item.active .page-link { background: #6366f1; color: white; }
[data-theme="dark"] .page-link { background: rgba(255,255,255,0.05); }

/* Missing Classes for Bulk Bar */
.bg-main { background-color: var(--main-color) !important; }
.bg-main-soft { background-color: rgba(99, 102, 241, 0.1) !important; }
.btn-main { background: var(--primary-gradient) !important; color: white !important; border: none !important; }
.btn-main:hover { opacity: 0.9; }
.shadow-main { box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4); }
.border-white-10 { border-color: rgba(255, 255, 255, 0.1) !important; }
</style>

@endsection
