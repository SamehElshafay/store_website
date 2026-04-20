<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="ps-4" style="width: 40px;">
                    <div class="form-check custom-check">
                        <input class="form-check-input" type="checkbox" id="selectAllParcels" onchange="window.toggleAllParcels(this)">
                    </div>
                </th>
                <th style="width: 80px;">ID</th>
                <th>{{ __('Item Details') }}</th>
                <th>{{ __('Recipient') }}</th>
                <th>{{ __('Financials') }}</th>
                <th class="text-center">{{ __('Status') }}</th>
                <th>{{ __('Date') }}</th>
                <th class="pe-4 text-end">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parcels as $parcel)
            <tr class="parcel-row" data-parcel-id="{{ $parcel->id }}" data-status-id="{{ $parcel->status_id }}">
                <td class="ps-4">
                    <div class="form-check custom-check">
                        <input class="form-check-input parcel-checkbox" type="checkbox" value="{{ $parcel->id }}" onchange="window.updateBulkBar()">
                    </div>
                </td>
                <td>
                    <span class="badge bg-dark-soft text-main-responsive border-0 fw-bold">#{{ $parcel->id }}</span>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-soft text-primary rounded-3 h-px-45 w-px-45 d-flex align-items-center justify-content-center fs-5">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-main">{{ $parcel->title }}</div>
                            <div class="small text-muted opacity-75 font-monospace">{{ $parcel->barcode_in }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="fw-bold text-main">{{ $parcel->receiver ? $parcel->receiver->name : 'N/A' }}</div>
                    <div class="small text-muted">{{ $parcel->receiver ? $parcel->receiver->phone : '-' }}</div>
                </td>
                <td>
                    <div class="small">
                        <span class="text-muted d-block opacity-75">{{ __('Price') }}: <span class="fw-bold text-main">{{ number_format($parcel->delivery_price, 2) }}</span></span>
                        <span class="text-muted d-block opacity-75">{{ __('Net') }}: <span class="fw-bold text-success">{{ number_format($parcel->net_collection, 2) }}</span></span>
                    </div>
                </td>
                <td class="text-center">
                    @php $statusModel = $parcel->statusModel; @endphp
                    <span class="badge rounded-pill px-3 py-2 shadow-sm" style="background-color: {{ $statusModel ? $statusModel->color : '#6c757d' }};">
                        {{ $statusModel ? $statusModel->display_name : $parcel->status }}
                    </span>
                    <div class="extra-small text-muted mt-1 opacity-75">{{ $parcel->collection_method ?? '-' }}</div>
                </td>
                <td>
                    <div class="text-main fw-bold">{{ $parcel->created_at->format('Y-m-d') }}</div>
                    <div class="extra-small text-muted">{{ $parcel->created_at->format('h:i A') }}</div>
                </td>
                <td class="pe-4 text-end">
                    <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-dark-soft p-0">
                        <a href="{{ route('parcels.show', $parcel->id) }}" class="btn btn-dark-soft btn-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 38px;" data-bs-toggle="tooltip" title="{{ __('View Details') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center" 
                                style="width: 42px; height: 38px;"
                                data-bs-toggle="modal" 
                                data-bs-target="#statusUpdateModal"
                                data-parcel-id="{{ $parcel->id }}" 
                                data-parcel-title="{{ $parcel->title }}"
                                data-current-order="{{ $parcel->statusModel ? $parcel->statusModel->sort_order : 0 }}"
                                data-bs-toggle="tooltip" title="{{ __('Update Status') }}">
                            <i class="bi bi-arrow-right-circle"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="opacity-25 mb-3"><i class="bi bi-inbox fs-1"></i></div>
                    <h5 class="text-muted">{{ __('No historical records match your current filters') }}</h5>
                    <a href="{{ route('parcels.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">{{ __('Clear All Filters') }}</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($parcels->hasPages())
<div class="px-4 py-4 bg-dark-soft-card">
    {{ $parcels->links() }}
</div>
@endif
