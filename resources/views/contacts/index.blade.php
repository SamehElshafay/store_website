@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container-fluid py-4 px-4">
        <!-- ... existing content ... -->

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                <i class="bi {{ request('type') == 'sender' ? 'bi-person-up' : 'bi-person-down' }} me-2" style="color: #6366f1;"></i>
                {{ request('type') == 'sender' ? __('Senders') : __('Recipients') }}
                <span class="badge rounded-pill ms-2 small" style="background: rgba(99,102,241,0.15); color: #6366f1; font-size: 0.75rem;">
                    {{ $contacts->total() }}
                </span>
            </h4>
            <p class="text-muted small mb-0">{{ request('type') == 'sender' ? __('Manage all your parcel senders') : __('Manage all your parcel recipients') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contacts.create', ['type' => request('type', 'sender')]) }}" class="btn btn-lg rounded-pill px-4 shadow-sm" style="background: linear-gradient(135deg, #6366f1, #a855f7); color: white; border: none;">
                <i class="bi bi-person-plus-fill me-2"></i> 
                {{ request('type') == 'sender' ? __('Add New Sender') : __('Add New Recipient') }}
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
    <div class="alert border-0 rounded-4 mb-4 d-flex align-items-center gap-2" style="background: rgba(34,197,94,0.1); color: #16a34a;">
        <i class="bi bi-check-circle-fill"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters & Search --}}
    <div class="glass-card p-3 rounded-4 mb-4 d-flex align-items-center gap-3 flex-wrap">
        <div class="flex-grow-1">
            <form action="{{ route('contacts.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-lg flex-grow-1" style="max-width: 500px;">
                    <span class="input-group-text border-0 bg-dark-soft"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-dark-soft" placeholder="{{ __('Search by name, phone or address...') }}" value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-lg btn-primary rounded-pill px-5 fw-bold shadow-sm">{{ __('Search') }}</button>
            </form>
        </div>
    </div>

    {{-- Contacts Table --}}
    <div class="glass-card rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="small text-uppercase">
                        <th class="ps-4 py-3 border-0 text-muted">{{ request('type') == 'sender' ? __('Sender Name') : __('Recipient Name') }}</th>
                        <th class="py-3 border-0 text-muted">{{ __('Phone Number') }}</th>
                        <th class="py-3 border-0 text-muted">{{ __('Address') }}</th>
                        <th class="py-3 border-0 text-muted text-center">{{ __('Total Parcels') }}</th>
                        <th class="pe-4 py-3 border-0 text-muted text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $contact)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm"
                                     style="width: 40px; height: 40px; min-width: 40px; background: linear-gradient(135deg, #6366f1, #a855f7); font-size: 0.9rem;">
                                    {{ strtoupper(substr($contact->name, 0, 2)) }}
                                </div>
                                <div class="fw-bold fs-6" style="color: var(--text-main);">{{ $contact->name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold fs-6" style="color: var(--text-main);">{{ $contact->phone ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="small text-muted text-truncate" style="max-width: 300px;" title="{{ $contact->address }}">{{ $contact->address ?? '—' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill px-3 py-2" style="background: rgba(99,102,241,0.1); color: #6366f1; font-weight: 700;">
                                {{ request('type') == 'sender' ? $contact->sentParcels()->count() : $contact->receivedParcels()->count() }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-sm btn-icon btn-primary-soft rounded-circle" title="{{ __('View Details') }}">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm btn-icon btn-warning-soft rounded-circle" title="{{ __('Edit') }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form id="delete-form-{{ $contact->id }}" action="{{ route('contacts.destroy', $contact) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-icon btn-danger-soft rounded-circle delete-recipient-btn" 
                                            data-id="{{ $contact->id }}"
                                            data-name="{{ $contact->name }}"
                                            title="{{ __('Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 border-0">
                            <i class="bi bi-people fs-1 text-muted opacity-50 d-block mb-3"></i>
                            <p class="text-muted mb-2">{{ request('type') == 'sender' ? __('No senders found') : __('No recipients found') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($contacts->hasPages())
        <div class="p-3 border-top d-flex justify-content-end" style="border-color: var(--border-color) !important;">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX Delete Logic
    const deleteButtons = document.querySelectorAll('.delete-recipient-btn');
    const requestType = new URLSearchParams(window.location.search).get('type');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const row = this.closest('tr');
            const form = document.getElementById(`delete-form-${id}`);
            
            window.showConfirm(requestType === 'sender' ? "{{ __('Delete Sender') }}" : "{{ __('Delete Recipient') }}", `{{ __('Are you sure you want to delete') }} "${name}"?`, async () => {
                // UI: Loading State
                row.style.opacity = '0.5';
                row.style.pointerEvents = 'none';
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        // UI: SUCCESS - Smooth removal
                        row.style.transition = 'all 0.5s ease';
                        row.style.transform = 'translateX(20px)';
                        row.style.opacity = '0';
                        
                        if (window.showToast) {
                            const msg = requestType === 'sender' ? "{{ __('Sender deleted successfully.') }}" : "{{ __('Recipient deleted successfully.') }}";
                            window.showToast("{{ __('Success') }}", result.message || msg, 'success');
                        }
                        
                        setTimeout(() => row.remove(), 500);
                    } else {
                        throw result;
                    }
                } catch (error) {
                    // UI: FAILURE - Revert State
                    row.style.opacity = '1';
                    row.style.pointerEvents = 'all';
                    this.innerHTML = '<i class="bi bi-trash"></i>';
                    
                    if (window.showToast) {
                        window.showToast("{{ __('Error') }}", error.message || "{{ __('Failed to delete recipient.') }}", 'error');
                    }
                }
            });
        });
    });

    // Generate animated circles
    (function() {
        const wrapper = document.getElementById('circles');
        if (!wrapper) return;
        const circleCount = 12;
        for (let i = 0; i < circleCount; i++) {
            const circle = document.createElement('div');
            circle.className = 'circle';
            const size = Math.random() * 350 + 150;
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
