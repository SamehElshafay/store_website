{{-- ── Top Bar: Record Count + Per-Page Selector ── --}}
<div class="px-4 py-3 d-flex align-items-center justify-content-between border-bottom border-white-10 flex-wrap gap-2">
    {{-- Pagination Summary --}}
    <div class="d-flex align-items-center gap-2">
        <div class="bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold small d-flex align-items-center gap-2">
            <i class="bi bi-info-circle"></i>
            <span>
                {{ __('Showing') }} 
                <span class="text-main">{{ $parcels->firstItem() ?? 0 }}</span> 
                {{ __('to') }} 
                <span class="text-main">{{ $parcels->lastItem() ?? 0 }}</span> 
                {{ __('of') }} 
                <span class="text-main fw-800">{{ number_format($parcels->total()) }}</span> 
                {{ $parcels->total() > 1 ? __('results') : __('result') }}
            </span>
        </div>
    </div>

    {{-- Per-Page Controls --}}
    <form method="GET" action="{{ route('parcels.index') }}" id="perPageForm" class="d-flex align-items-center gap-2 flex-wrap">
        {{-- Preserve all current filters --}}
        @foreach(request()->except('per_page', 'page') as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach

        <label class="small text-muted fw-bold mb-0 me-1">{{ __('Rows per page') }}:</label>

        {{-- Quick Presets --}}
        @php
            $currentPerPage = request('per_page', 25);
            $presets = [10, 25, 50, 100, 250];
            $isCustom = !in_array($currentPerPage, $presets) && $currentPerPage !== 'all';
        @endphp
        @foreach($presets as $size)
            <button type="submit" name="per_page" value="{{ $size }}"
                class="btn btn-sm rounded-pill px-3 fw-bold {{ $currentPerPage == $size ? 'btn-primary' : 'btn-dark-soft' }}">
                {{ $size }}
            </button>
        @endforeach

        {{-- All Records --}}
        <button type="button" 
            class="btn btn-sm rounded-pill px-3 fw-bold {{ $currentPerPage === 'all' ? 'btn-warning text-dark' : 'btn-dark-soft' }}"
            onclick="confirmShowAll()">
            <i class="bi bi-infinity me-1"></i>{{ __('All') }}
        </button>

        {{-- Divider --}}
        <span class="text-muted opacity-25">|</span>

        {{-- Custom Number Input --}}
        <div class="d-flex align-items-center gap-1">
            <input type="number"
                   name="per_page"
                   id="customPerPageInput"
                   min="1"
                   max="99999"
                   placeholder="{{ __('Custom') }}..."
                   value="{{ $isCustom ? $currentPerPage : '' }}"
                   class="form-control form-control-sm border-0 bg-dark-soft rounded-pill text-center fw-bold {{ $isCustom ? 'border border-primary border-opacity-50' : '' }}"
                   style="width: 90px;"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();this.form.submit();}">
            <button type="submit" class="btn btn-sm btn-dark-soft rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;" title="{{ __('Apply') }}">
                <i class="bi bi-arrow-right-short fs-5"></i>
            </button>
        </div>
    </form>
</div>

{{-- ── Table ── --}}
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
                            <div class="fw-bold text-main">{{ $parcel->title ?? __('Untitled Parcel') }}</div>
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
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('parcels.show', $parcel->id) }}" class="btn btn-dark-soft rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" data-bs-toggle="tooltip" title="{{ __('View Details') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button onclick="event.preventDefault(); event.stopPropagation(); window.openParcelEditModal({{ $parcel->id }}, '{{ $parcel->statusModel->modal_type ?? 'receive' }}', this)" class="btn btn-primary-soft rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" data-bs-toggle="tooltip" title="{{ __('Edit Parcel') }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="opacity-25 mb-3"><i class="bi bi-inbox fs-1"></i></div>
                    <h5 class="text-muted">{{ __('No historical records match your current filters') }}</h5>
                    <a href="{{ route('parcels.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">{{ __('Clear All Filters') }}</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Pagination Footer ── --}}
@if($parcels->hasPages())
<div class="px-4 py-3 d-flex align-items-center justify-content-between border-top border-white-10 flex-wrap gap-2">
    <div class="small text-muted">
        {{ __('Page') }} <strong class="text-main">{{ $parcels->currentPage() }}</strong> {{ __('of') }} <strong class="text-main">{{ $parcels->lastPage() }}</strong>
    </div>
    <div>{{ $parcels->appends(request()->except('page'))->links() }}</div>
</div>
@endif
