@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

@php
    $icons = [
        'bi-box', 'bi-truck', 'bi-check2-circle', 'bi-arrow-left-right', 
        'bi-exclamation-octagon', 'bi-clock', 'bi-shield-check', 
        'bi-archive', 'bi-geo-alt', 'bi-send', 'bi-house', 'bi-dot'
    ];
@endphp

<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container-fluid py-4 px-4">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                    <i class="bi bi-gear-fill me-2" style="color: #6366f1;"></i>
                    {{ __('Status Management') }}
                </h4>
                <p class="text-muted small mb-0">{{ __('Drag and drop statuses to reorder your workflow') }}</p>
            </div>
            <button class="btn btn-lg rounded-pill px-4 shadow-sm" style="background: linear-gradient(135deg, #6366f1, #a855f7); color: white; border: none;" data-bs-toggle="modal" data-bs-target="#addStatusModal">
                <i class="bi bi-plus-lg me-2"></i> {{ __('Add New Status') }}
            </button>
        </div>

        <div class="row g-4" id="status-grid">
            @foreach($statuses as $status)
            <div class="col-md-4 col-xl-3 status-card-wrapper" data-id="{{ $status->id }}">
                <div class="glass-card p-4 rounded-4 h-100 position-relative border-0 shadow-sm transition-all hover-translate-y cursor-grab active-cursor-grabbing">
                    <div class="position-absolute top-0 end-0 m-3 d-flex flex-column gap-1 align-items-end">
                        @if($status->is_default)
                        <span class="badge bg-success rounded-pill px-3 py-1 shadow-sm small">
                             {{ __('Default') }}
                        </span>
                        @endif
                        <div class="text-muted opacity-25">
                            <i class="bi bi-grip-vertical fs-4"></i>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3 mb-4 mt-2">
                        <div class="status-dot shadow-sm" style="width: 20px; height: 20px; border-radius: 50%; background-color: {{ $status->color }}; border: 3px solid rgba(255,255,255,0.2);"></div>
                        <i class="bi {{ $status->icon ?: 'bi-dot' }} fs-4" style="color: {{ $status->color }}"></i>
                        <h5 class="fw-bold mb-0" style="color: var(--text-main);">{{ $status->display_name }}</h5>
                    </div>

                    <div class="small text-muted mb-4 opacity-75">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold">{{ __('Action') }}:</span> 
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input cursor-pointer toggle-modal-type" type="checkbox" 
                                    role="switch" 
                                    data-id="{{ $status->id }}"
                                    {{ $status->modal_type == 'dispatch' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold {{ $status->modal_type == 'dispatch' ? 'text-danger' : 'text-info' }}" style="min-width: 60px; text-align: end;">
                                    {{ $status->modal_type == 'dispatch' ? __('Dispatch') : __('Receive') }}
                                </label>
                            </div>
                        </div>
                    </div>

                        <div class="d-flex gap-2 mt-auto">
                            <button class="btn btn-icon btn-primary-soft rounded-circle btn-sm" 
                                onclick='editStatus(@json($status))' title="{{ __('Edit') }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if(!$status->is_default)
                            <button class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 fw-bold" onclick="setDefault({{ $status->id }})">
                                <i class="bi bi-star-fill me-1"></i> {{ __('Default') }}
                            </button>
                            @endif
                            <button class="btn btn-icon btn-danger-soft rounded-circle btn-sm" onclick="deleteStatus({{ $status->id }}, '{{ $status->display_name }}')" title="{{ __('Delete') }}" {{ $status->is_default ? 'disabled' : '' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>

<!-- Add Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i> {{ __('Create New Status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStatusForm">
                @csrf
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Name (Arabic)') }}</label>
                            <input type="text" name="name_ar" class="form-control border-0 bg-dark-soft p-3 rounded-3" placeholder="مثال: جاهز" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Name (English)') }}</label>
                            <input type="text" name="name_en" class="form-control border-0 bg-dark-soft p-3 rounded-3" placeholder="e.g. Ready" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Status Color') }}</label>
                            <input type="color" name="color" class="form-control border-0 bg-dark-soft p-2 rounded-3 h-px-50 w-100" value="#6366f1">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Select Status Icon') }}</label>
                            <div class="d-flex flex-wrap gap-2 p-3 bg-dark-soft rounded-4 icon-picker-container">
                                @foreach($icons as $icon)
                                <div class="icon-option p-2 rounded-3 border border-transparent cursor-pointer transition-all d-flex align-items-center justify-content-center" 
                                     data-icon="{{ $icon }}" 
                                     onclick="selectIcon(this, '{{ $icon }}')"
                                     style="font-size: 1.5rem; width: 52px; height: 52px; background: rgba(255,255,255,0.05); color: #cbd5e1;">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                                @endforeach
                                <input type="hidden" name="icon" id="selectedStatusIcon" value="bi-dot">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Dialog Modal Type') }}</label>
                            <select name="modal_type" class="form-select border-0 bg-dark-soft p-3 rounded-3" required>
                                <option value="receive">{{ __('Receive Modal (Incoming)') }}</option>
                                <option value="dispatch">{{ __('Dispatch Modal (Outgoing)') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">{{ __('Save Status') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i> {{ __('Edit Status') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStatusForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_status_id">
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Name (Arabic)') }}</label>
                            <input type="text" name="name_ar" id="edit_name_ar" class="form-control border-0 bg-dark-soft p-3 rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Name (English)') }}</label>
                            <input type="text" name="name_en" id="edit_name_en" class="form-control border-0 bg-dark-soft p-3 rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Status Color') }}</label>
                            <input type="color" name="color" id="edit_color" class="form-control border-0 bg-dark-soft p-2 rounded-3 h-px-50 w-100">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Select Status Icon') }}</label>
                            <div class="d-flex flex-wrap gap-2 p-3 bg-dark-soft rounded-4 icon-picker-container">
                                @foreach($icons as $icon)
                                <div class="icon-option p-2 rounded-3 border border-transparent cursor-pointer transition-all d-flex align-items-center justify-content-center edit-icon-option" 
                                     data-icon="{{ $icon }}" 
                                     onclick="selectIconEdit(this, '{{ $icon }}')"
                                     style="font-size: 1.5rem; width: 52px; height: 52px; background: rgba(255,255,255,0.05); color: #cbd5e1;">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                                @endforeach
                                <input type="hidden" name="icon" id="editSelectedStatusIcon">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Dialog Modal Type') }}</label>
                            <select name="modal_type" id="edit_modal_type" class="form-select border-0 bg-dark-soft p-3 rounded-3" required>
                                <option value="receive">{{ __('Receive Modal (Incoming)') }}</option>
                                <option value="dispatch">{{ __('Dispatch Modal (Outgoing)') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">{{ __('Update Status') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Sortable
    const grid = document.getElementById('status-grid');
    if (grid) {
        new Sortable(grid, {
            animation: 250,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: async function() {
                const order = Array.from(grid.querySelectorAll('.status-card-wrapper')).map(el => el.getAttribute('data-id'));
                
                try {
                    const response = await fetch("{{ route('parcel-statuses.reorder') }}", {
                        method: 'POST',
                        body: JSON.stringify({ order }),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    
                    const result = await response.json();
                    if (response.ok) {
                        window.showToast("{{ __('Updated') }}", "{{ __('Order updated successfully') }}", 'success');
                    } else {
                        window.showToast("{{ __('Error') }}", result.message, 'error');
                    }
                } catch (err) {
                    window.showToast("{{ __('Error') }}", "{{ __('Failed to save order') }}", 'error');
                }
            }
        });
    }

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

async function setDefault(id) {
    try {
        const response = await fetch(`/parcel-statuses/${id}/default`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const result = await response.json();
        if (response.ok) {
            window.showToast("{{ __('Updated') }}", result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        }
    } catch (err) {
        window.showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
    }
}

async function deleteStatus(id, name) {
    window.showConfirm("{{ __('Delete Status') }}", `{{ __('Are you sure you want to delete status') }} "${name}"?`, async () => {
        try {
            const response = await fetch(`/parcel-statuses/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (response.ok) {
                window.showToast("{{ __('Deleted') }}", result.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showToast("{{ __('Failed') }}", result.message, 'error');
            }
        } catch (err) {
            window.showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
        }
    });
}

document.getElementById('addStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    
    try {
        const formData = new FormData(this);
        const response = await fetch("{{ route('parcel-statuses.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const result = await response.json();
        if (response.ok) {
            window.showToast("{{ __('Created') }}", result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.showToast("{{ __('Failed') }}", result.message, 'error');
            btn.disabled = false;
        }
    } catch (err) {
        window.showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
        btn.disabled = false;
    }
});

// Handle Modal Type Toggle
document.addEventListener('change', async function(e) {
    if (e.target.classList.contains('toggle-modal-type')) {
        const input = e.target;
        const id = input.getAttribute('data-id');
        const isDispatch = input.checked;
        const modalType = isDispatch ? 'dispatch' : 'receive';
        const label = input.nextElementSibling;

        // Visual feedback
        label.innerText = isDispatch ? "{{ __('Dispatch') }}" : "{{ __('Receive') }}";
        label.className = `form-check-label small fw-bold ${isDispatch ? 'text-danger' : 'text-info'}`;

        try {
            const response = await fetch(`/parcel-statuses/${id}/toggle-modal`, {
                method: 'POST',
                body: JSON.stringify({ modal_type: modalType }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error();
            window.showToast("{{ __('Updated') }}", "{{ __('Dialog type updated successfully') }}", 'success');
        } catch (err) {
            window.showToast("{{ __('Error') }}", "{{ __('Failed to update dialog type') }}", 'error');
            // Revert on error
            input.checked = !isDispatch;
            label.innerText = !isDispatch ? "{{ __('Dispatch') }}" : "{{ __('Receive') }}";
            label.className = `form-check-label small fw-bold ${!isDispatch ? 'text-danger' : 'text-info'}`;
        }
    }
});

function selectIcon(el, icon) {
    document.querySelectorAll('.icon-option').forEach(opt => {
        opt.classList.remove('selected-icon');
        opt.style.borderColor = 'transparent';
        opt.style.color = '#cbd5e1';
    });
    el.classList.add('selected-icon');
    el.style.borderColor = '#6366f1';
    el.style.color = '#ffffff';
    document.getElementById('selectedStatusIcon').value = icon;
}

function selectIconEdit(el, icon) {
    document.querySelectorAll('.edit-icon-option').forEach(opt => {
        opt.classList.remove('selected-icon');
        opt.style.borderColor = 'transparent';
        opt.style.color = '#cbd5e1';
    });
    el.classList.add('selected-icon');
    el.style.borderColor = '#6366f1';
    el.style.color = '#ffffff';
    document.getElementById('editSelectedStatusIcon').value = icon;
}

function editStatus(status) {
    document.getElementById('edit_status_id').value = status.id;
    document.getElementById('edit_name_ar').value = status.name_ar;
    document.getElementById('edit_name_en').value = status.name_en;
    document.getElementById('edit_color').value = status.color || '#6366f1';
    document.getElementById('edit_modal_type').value = status.modal_type || 'receive';
    
    // Select Icon
    const icon = status.icon || 'bi-dot';
    const iconOpt = document.querySelector(`.edit-icon-option[data-icon="${icon}"]`);
    if(iconOpt) selectIconEdit(iconOpt, icon);
    
    const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    modal.show();
}

document.getElementById('editStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('edit_status_id').value;
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    
    try {
        const formData = new FormData(this);
        const response = await fetch(`/parcel-statuses/${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const result = await response.json();
        if (response.ok) {
            window.showToast("{{ __('Updated') }}", result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.showToast("{{ __('Failed') }}", result.message, 'error');
            btn.disabled = false;
        }
    } catch (err) {
        window.showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
        btn.disabled = false;
    }
});

// Set initial selection
document.addEventListener('DOMContentLoaded', function() {
    const defaultIcon = document.querySelector('.icon-option[data-icon="bi-dot"]');
    if(defaultIcon) selectIcon(defaultIcon, 'bi-dot');
});
</script>

<style>
.hover-translate-y:hover { transform: translateY(-5px); }
.bg-dark-soft { background: rgba(0,0,0,0.05) !important; }
[data-theme="dark"] .bg-dark-soft { background: rgba(255,255,255,0.05) !important; }
.h-px-50 { height: 50px; }
.btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
.icon-option:hover { transform: scale(1.1); background: rgba(255,255,255,0.15) !important; color: #fff !important; }
.selected-icon { 
    border: 2px solid #6366f1 !important; 
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(168, 85, 247, 0.2)) !important;
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.4); 
    color: #fff !important;
}
.cursor-grab { cursor: grab; }
.active-cursor-grabbing:active { cursor: grabbing; }
.sortable-ghost { opacity: 0.3; transform: scale(0.95); }
.sortable-chosen { box-shadow: 0 15px 30px rgba(99, 102, 241, 0.2) !important; border: 2px solid #6366f1 !important; }

/* Custom Form Styling for Dark Theme */
[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select {
    background-color: rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    backdrop-filter: blur(5px);
}

[data-theme="dark"] .form-control::placeholder {
    color: rgba(255, 255, 255, 0.3) !important;
}

[data-theme="dark"] .form-select option {
    background-color: #1a1a2e !important;
    color: #ffffff !important;
}

[data-theme="dark"] .form-control:focus,
[data-theme="dark"] .form-select:focus {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25) !important;
}

[data-theme="dark"] .glass-modal {
    background: rgba(15, 23, 42, 0.9) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}
</style>
@endsection
