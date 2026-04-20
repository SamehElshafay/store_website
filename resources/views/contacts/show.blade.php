@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="circles-wrapper" id="circles"></div>
    <div class="container-fluid py-4 px-4">
        <!-- ... existing content ... -->

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('contacts.index') }}" class="btn btn-sm rounded-circle" style="width:38px;height:38px;padding:0;display:flex;align-items:center;justify-content:center;background:var(--card-bg);border:1px solid var(--border-color);color:var(--text-main);">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                 style="width:52px;height:52px;background:linear-gradient(135deg,#6366f1,#a855f7);font-size:1.2rem;">
                {{ strtoupper(substr($contact->name, 0, 2)) }}
            </div>
            <div>
                <h4 class="fw-bold mb-1" style="color:var(--text-main);">{{ $contact->name }}</h4>
                <div class="d-flex align-items-center gap-2">
                    @if($contact->type === 'sender')
                        <span class="badge rounded-pill px-3 py-1" style="background:rgba(99,102,241,0.1);color:#6366f1;"><i class="bi bi-box-arrow-up me-1"></i>{{ __('Sender') }}</span>
                    @else
                        <span class="badge rounded-pill px-3 py-1" style="background:rgba(168,85,247,0.1);color:#a855f7;"><i class="bi bi-box-arrow-in-down me-1"></i>{{ __('Recipient') }}</span>
                    @endif
                    @if($contact->company_name)
                        <span class="small text-muted">{{ $contact->company_name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contacts.edit', $contact) }}" class="btn btn-sm rounded-pill px-3" style="background:rgba(234,179,8,0.1);color:#ca8a04;border:1px solid rgba(234,179,8,0.3);">
                <i class="bi bi-pencil me-1"></i> {{ __('Edit') }}
            </a>
            <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-sm rounded-pill px-3" style="background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.3);" onclick="return confirm('{{ __('Delete this contact?') }}')">
                    <i class="bi bi-trash me-1"></i> {{ __('Delete') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left: Info --}}
        <div class="col-lg-5">

            {{-- Contact Info Card --}}
            <div class="glass-card rounded-4 p-4 mb-4">
                <h6 class="fw-bold mb-3" style="color:var(--text-main);">{{ __('Contact Details') }}</h6>
                <div class="d-flex flex-column gap-3">
                    @if($contact->phone)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:34px;height:34px;background:rgba(99,102,241,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-telephone" style="color:#6366f1;font-size:0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Phone') }}</div>
                            <div class="fw-semibold" style="color:var(--text-main);">{{ $contact->phone }}</div>
                        </div>
                    </div>
                    @endif
                    @if($contact->phone_alt)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:34px;height:34px;background:rgba(99,102,241,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-telephone-plus" style="color:#6366f1;font-size:0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Alt. Phone') }}</div>
                            <div class="fw-semibold" style="color:var(--text-main);">{{ $contact->phone_alt }}</div>
                        </div>
                    </div>
                    @endif
                    @if($contact->email)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:34px;height:34px;background:rgba(168,85,247,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-envelope" style="color:#a855f7;font-size:0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Email') }}</div>
                            <div class="fw-semibold" style="color:var(--text-main);">{{ $contact->email }}</div>
                        </div>
                    </div>
                    @endif
                    @if($contact->address)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:34px;height:34px;background:rgba(234,179,8,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-geo-alt" style="color:#ca8a04;font-size:0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Address') }}</div>
                            <div class="fw-semibold" style="color:var(--text-main);">
                                {{ $contact->address }}
                                @if($contact->city || $contact->region)
                                <span class="text-muted">, {{ implode(', ', array_filter([$contact->city, $contact->region])) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($contact->tax_number)
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:34px;height:34px;background:rgba(34,197,94,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-receipt" style="color:#16a34a;font-size:0.85rem;"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Tax Number') }}</div>
                            <div class="fw-semibold" style="color:var(--text-main);">{{ $contact->tax_number }}</div>
                        </div>
                    </div>
                    @endif
                </div>
                @if($contact->notes)
                <hr style="border-color:var(--border-color);">
                <div class="small text-muted mb-1">{{ __('Notes') }}</div>
                <p class="small mb-0" style="color:var(--text-main);">{{ $contact->notes }}</p>
                @endif
            </div>
        </div>

        {{-- Right: Parcel History --}}
        <div class="col-lg-7">
            <div class="glass-card rounded-4 overflow-hidden">
                <div class="p-3 d-flex align-items-center justify-content-between border-bottom" style="border-color:var(--border-color)!important;">
                    <h6 class="fw-bold mb-0" style="color:var(--text-main);">
                        <i class="bi bi-boxes me-2" style="color:#6366f1;"></i>
                        {{ __('Parcel History') }}
                    </h6>
                </div>
                @php
                    $parcels = $contact->type === 'sender' ? $contact->sentParcels : $contact->receivedParcels;
                @endphp
                @if($parcels->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-box2 fs-1 text-muted opacity-50 d-block mb-3"></i>
                    <p class="text-muted mb-0">{{ __('No parcels found for this contact') }}</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="small text-uppercase">
                                <th class="ps-4 py-3 border-0 text-muted">{{ __('Parcel') }}</th>
                                <th class="py-3 border-0 text-muted">{{ __('Barcode') }}</th>
                                <th class="py-3 border-0 text-muted">{{ __('Status') }}</th>
                                <th class="py-3 border-0 text-muted">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parcels as $parcel)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold" style="color:var(--text-main);">{{ $parcel->title }}</div>
                                    @if($parcel->invoice_number)
                                    <div class="small text-muted"># {{ $parcel->invoice_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge font-monospace" style="background:rgba(99,102,241,0.1);color:#6366f1;">{{ $parcel->barcode_in }}</span>
                                </td>
                                <td>
                                    @php
                                        $sc = ['received'=>'rgba(34,197,94,0.1)','delivered'=>'rgba(99,102,241,0.1)','pending'=>'rgba(234,179,8,0.1)','returned'=>'rgba(239,68,68,0.1)'];
                                        $tc = ['received'=>'#16a34a','delivered'=>'#6366f1','pending'=>'#ca8a04','returned'=>'#dc2626'];
                                    @endphp
                                    <span class="badge rounded-pill px-2 py-1 text-capitalize" style="background:{{ $sc[$parcel->status] ?? 'rgba(107,114,128,0.1)' }};color:{{ $tc[$parcel->status] ?? '#6b7280' }};">
                                        {{ __($parcel->status) }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $parcel->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script>
// Generate animated circles
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
</script>
@endsection
