@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('home') }}" class="btn btn-dark-soft rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px;height:40px;">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h4 class="fw-800 mb-0">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                        {{ __('Import from Excel') }}
                    </h4>
                    <p class="text-muted small mb-0">
                        {{ __('Status') }}: <span class="fw-bold" style="color: {{ $status->color }}">{{ $status->display_name }}</span>
                        &nbsp;&bull;&nbsp;
                        {{ $status->modal_type === 'dispatch' ? __('Dispatch Mode (2 sheets: IN + OUT)') : __('Receive Mode (1 sheet: IN)') }}
                    </p>
                </div>
            </div>

            {{-- Upload Card --}}
            <div class="glass-container p-4 mb-4 shadow-lg">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">
                    <i class="bi bi-upload me-2"></i>{{ __('Upload Excel File') }}
                </h6>

                @if($status->modal_type === 'dispatch')
                <div class="alert alert-info bg-info bg-opacity-10 border-0 rounded-4 small mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>{{ __('Dispatch Mode:') }}</strong>
                    {{ __('Upload the file once to register parcels as received, then import again to dispatch them.') }}
                </div>
                @endif

                <div id="dropZone" class="border border-2 border-dashed rounded-4 p-5 text-center position-relative"
                     style="border-color: rgba(99,102,241,0.3) !important; background: rgba(99,102,241,0.03); cursor: pointer; transition: all 0.3s;"
                     ondragover="event.preventDefault(); this.style.borderColor='#6366f1'; this.style.background='rgba(99,102,241,0.08)'"
                     ondragleave="this.style.borderColor='rgba(99,102,241,0.3)'; this.style.background='rgba(99,102,241,0.03)'"
                     ondrop="handleDrop(event)">
                    <i class="bi bi-file-earmark-excel fs-1 text-success d-block mb-2"></i>
                    <p class="fw-bold mb-1">{{ __('Drag & Drop your Excel file here') }}</p>
                    <p class="text-muted small mb-3">{{ __('or click to browse') }}</p>
                    <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" class="d-none" onchange="handleFileSelect(this)">
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-folder2-open me-2"></i>{{ __('Browse File') }}
                    </button>
                    <div id="fileInfo" class="mt-3 d-none">
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6" id="fileName"></span>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-premium rounded-pill px-5 py-2 shadow-sm fw-bold" id="btnParse" disabled onclick="parseFile()">
                        <i class="bi bi-search me-2"></i>{{ __('Preview Import') }}
                    </button>
                </div>
            </div>

            {{-- Loading State --}}
            <div id="loadingState" class="glass-container p-5 text-center d-none mb-4 shadow-lg">
                <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                <p class="fw-bold text-main mb-0">{{ __('Analyzing file...') }}</p>
                <p class="text-muted small">{{ __('Checking barcodes and contacts in the database...') }}</p>
            </div>

            {{-- Preview Table --}}
            <div id="previewSection" class="d-none">
                <div class="glass-container p-4 shadow-lg">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-primary"></i>{{ __('Import Preview') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ __('Total rows') }}: <span id="totalRows" class="fw-bold text-primary"></span>
                                &nbsp;&bull;&nbsp;
                                <span class="text-warning" id="existingCount"></span>
                                &nbsp;&bull;&nbsp;
                                <span class="text-danger" id="newContactCount"></span>
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light rounded-pill px-3 btn-sm" onclick="resetImport()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reset') }}
                            </button>
                            <button class="btn btn-success rounded-pill px-4 btn-sm fw-bold shadow-sm" id="btnCommit" onclick="commitImport()">
                                <i class="bi bi-check-circle me-2"></i>{{ __('Confirm Import') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0 small" id="previewTable">
                            <thead class="sticky-top" style="z-index:10;">
                                <tr class="text-uppercase text-muted small">
                                    <th class="ps-3 py-3">{{ __('Barcode') }}</th>
                                    <th>{{ __('Sender') }}</th>
                                    <th>{{ __('Recipient') }}</th>
                                    <th>{{ __('Collection') }}</th>
                                    <th>{{ __('Content') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Result Section --}}
            <div id="resultSection" class="d-none glass-container p-4 shadow-lg text-center">
                <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
                <h5 class="fw-bold" id="resultTitle"></h5>
                <div id="resultDetails" class="text-muted small"></div>
                <div id="resultErrors" class="mt-3 text-danger small text-start"></div>
                <div class="mt-4 d-flex justify-content-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-house me-2"></i>{{ __('Dashboard') }}
                    </a>
                    <button class="btn btn-outline-primary rounded-pill px-4" onclick="resetImport()">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ __('Import More') }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.glass-container {
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border-color);
    border-radius: 1.5rem;
}
.btn-premium {
    background: var(--primary-gradient);
    color: white;
    border: none;
}
.btn-premium:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.badge-new-contact {
    background: rgba(239,68,68,0.1);
    color: #ef4444;
    border: 1px solid rgba(239,68,68,0.2);
}
.badge-exists {
    background: rgba(34,197,94,0.1);
    color: #22c55e;
    border: 1px solid rgba(34,197,94,0.2);
}
.badge-already {
    background: rgba(234,179,8,0.1);
    color: #eab308;
    border: 1px solid rgba(234,179,8,0.2);
}
thead th {
    background: var(--nav-bg);
}
</style>

<script>
const STATUS_ID   = {{ $status->id }};
const PARSE_URL   = "{{ route('parcels.import.parse') }}";
const COMMIT_URL  = "{{ route('parcels.import.commit') }}";
const CSRF_TOKEN  = "{{ csrf_token() }}";

let parsedRows = [];
let selectedFile = null;

function handleDrop(e) {
    e.preventDefault();
    const dt = e.dataTransfer;
    if (dt.files.length > 0) {
        selectedFile = dt.files[0];
        showFileName(selectedFile.name);
    }
    const dz = document.getElementById('dropZone');
    dz.style.borderColor = 'rgba(99,102,241,0.3)';
    dz.style.background  = 'rgba(99,102,241,0.03)';
}

function handleFileSelect(input) {
    if (input.files.length > 0) {
        selectedFile = input.files[0];
        showFileName(selectedFile.name);
    }
}

function showFileName(name) {
    document.getElementById('fileInfo').classList.remove('d-none');
    document.getElementById('fileName').textContent = name;
    document.getElementById('btnParse').disabled = false;
}

async function parseFile() {
    if (!selectedFile) return;

    document.getElementById('loadingState').classList.remove('d-none');
    document.getElementById('previewSection').classList.add('d-none');
    document.getElementById('resultSection').classList.add('d-none');
    document.getElementById('btnParse').disabled = true;

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('status_id', STATUS_ID);
    formData.append('_token', CSRF_TOKEN);

    try {
        const resp = await fetch(PARSE_URL, { 
            method: 'POST', 
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });
        
        const contentType = resp.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await resp.json();
            document.getElementById('loadingState').classList.add('d-none');
            document.getElementById('btnParse').disabled = false;

            if (!data.success) {
                showToast('{{ __("Error") }}', data.message, 'error');
                return;
            }

            parsedRows = data.rows;
            renderPreview(data);
        } else {
            const text = await resp.text();
            console.error("Non-JSON response:", text);
            document.getElementById('loadingState').classList.add('d-none');
            document.getElementById('btnParse').disabled = false;
            showToast('{{ __("Error") }}', '{{ __("Server returned an invalid response. Please check the logs.") }}', 'error');
        }

    } catch (err) {
        console.error("Fetch error:", err);
        document.getElementById('loadingState').classList.add('d-none');
        document.getElementById('btnParse').disabled = false;
        showToast('{{ __("Error") }}', err.message, 'error');
    }
}

function renderPreview(data) {
    const rows      = data.rows;
    const tbody     = document.getElementById('previewBody');
    const existing  = rows.filter(r => r.already_exists).length;
    const newConts  = rows.filter(r => !r.sender_exists || !r.recipient_exists).length;

    document.getElementById('totalRows').textContent     = rows.length;
    document.getElementById('existingCount').textContent  = `${existing} {{ __('already in DB') }}`;
    document.getElementById('newContactCount').textContent = `${newConts} {{ __('need new contacts') }}`;

    tbody.innerHTML = '';
    rows.forEach(r => {
        const senderBadge    = r.sender_exists
            ? `<span class="badge rounded-pill badge-exists">✓ {{ __('Exists') }}</span>`
            : `<span class="badge rounded-pill badge-new-contact">+ {{ __('New') }}</span>`;
        const recipientBadge = r.recipient_exists
            ? `<span class="badge rounded-pill badge-exists">✓ {{ __('Exists') }}</span>`
            : `<span class="badge rounded-pill badge-new-contact">+ {{ __('New') }}</span>`;
        const rowBadge       = r.already_exists
            ? `<span class="badge rounded-pill badge-already">{{ __('Update') }}</span>`
            : `<span class="badge rounded-pill" style="background:rgba(99,102,241,0.1);color:#6366f1;border:1px solid rgba(99,102,241,0.2);">{{ __('New') }}</span>`;

        const collection = r.collection_amount ? `${r.collection_amount.toLocaleString()} / ${r.delivery_price}` : '—';

        tbody.innerHTML += `
            <tr>
                <td class="ps-3 font-monospace fw-bold">${r.barcode}</td>
                <td>
                    <div class="fw-bold">${r.sender_name || '—'}</div>
                    <div class="text-muted">${r.sender_phone || ''}</div>
                    ${senderBadge}
                </td>
                <td>
                    <div class="fw-bold">${r.recipient_name || '—'}</div>
                    <div class="text-muted">${r.recipient_phone || ''}</div>
                    <div class="text-muted" style="font-size:0.7rem">${r.recipient_city || ''}</div>
                    ${recipientBadge}
                </td>
                <td>
                    <div class="fw-bold">${collection}</div>
                    <div class="text-muted" style="font-size:0.7rem">${r.collection_method || ''}</div>
                </td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${r.title || ''}">${r.title || '—'}</td>
                <td class="text-center">${rowBadge}</td>
            </tr>
        `;
    });

    document.getElementById('previewSection').classList.remove('d-none');
}

async function commitImport() {
    if (parsedRows.length === 0) return;

    document.getElementById('btnCommit').disabled  = true;
    document.getElementById('btnCommit').innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Importing...') }}`;

    try {
        const resp = await fetch(COMMIT_URL, {
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept'      : 'application/json',
            },
            body: JSON.stringify({ status_id: STATUS_ID, rows: parsedRows }),
        });
        const data = await resp.json();

        document.getElementById('previewSection').classList.add('d-none');

        if (data.success) {
            const d = data.data;
            document.getElementById('resultTitle').textContent  = data.message;
            document.getElementById('resultDetails').innerHTML  =
                `<span class="text-success fw-bold">${d.created} {{ __('Created') }}</span> &bull; ` +
                `<span class="text-primary fw-bold">${d.updated} {{ __('Updated') }}</span> &bull; ` +
                `<span class="text-muted">${d.skipped} {{ __('Skipped') }}</span>`;

            if (d.errors?.length > 0) {
                document.getElementById('resultErrors').innerHTML =
                    '<strong>{{ __("Errors:") }}</strong><br>' + d.errors.map(e => `• ${e}`).join('<br>');
            }
        } else {
            document.getElementById('resultTitle').textContent = data.message;
        }
        document.getElementById('resultSection').classList.remove('d-none');

    } catch (err) {
        showToast('{{ __("Error") }}', err.message, 'error');
        document.getElementById('btnCommit').disabled  = false;
        document.getElementById('btnCommit').innerHTML = `<i class="bi bi-check-circle me-2"></i>{{ __('Confirm Import') }}`;
    }
}

function resetImport() {
    selectedFile = null;
    parsedRows   = [];
    document.getElementById('fileInput').value   = '';
    document.getElementById('fileInfo').classList.add('d-none');
    document.getElementById('previewSection').classList.add('d-none');
    document.getElementById('resultSection').classList.add('d-none');
    document.getElementById('btnParse').disabled = true;
}

// Use existing showToast if available, else fallback
if (typeof showToast === 'undefined') {
    window.showToast = function(title, msg, type) {
        alert(`${title}: ${msg}`);
    };
}
</script>
@endsection
