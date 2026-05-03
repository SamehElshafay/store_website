@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('settings.index') }}" class="btn btn-dark-soft rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:40px;height:40px;">
                    <i class="bi bi-arrow-right"></i>
                </a>
                <div>
                    <h4 class="fw-800 mb-0">
                        <i class="bi bi-file-earmark-excel text-success me-2"></i>
                        {{ __('Master Import from Excel') }}
                    </h4>
                    <p class="text-muted small mb-0">
                        {{ __('Dynamic Status Creation enabled. New statuses will be created automatically.') }}
                    </p>
                </div>
            </div>

            {{-- Upload Card --}}
            <div class="glass-container p-4 mb-4 shadow-lg">
                <h6 class="fw-bold text-uppercase text-muted small mb-3">
                    <i class="bi bi-upload me-2"></i>{{ __('Upload Master Excel File') }}
                </h6>

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
                <p class="fw-bold text-main mb-2" id="loadingTitle">{{ __('Analyzing file...') }}</p>
                
                <div class="progress mb-3 rounded-pill" style="height: 10px; background: rgba(255,255,255,0.05);">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary rounded-pill" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted px-1">
                    <span id="progressText">{{ __('Preparing...') }}</span>
                    <span id="progressCounter">0/0</span>
                </div>

                <div id="currentProcess" class="font-monospace small text-primary mb-3 mt-3"></div>
            </div>

            {{-- Preview Table --}}
            <div id="previewSection" class="d-none">
                <div class="glass-container p-4 shadow-lg">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-primary"></i>{{ __('Import Preview') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ __('Total rows') }}: <span id="totalRows" class="fw-bold text-primary"></span>
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
                            <thead class="sticky-top" style="z-index:10; background: var(--nav-bg);">
                                <tr class="text-uppercase text-muted small">
                                    <th class="ps-3 py-3">{{ __('Barcode') }}</th>
                                    <th>{{ __('Sender') }}</th>
                                    <th>{{ __('Recipient') }}</th>
                                    <th>{{ __('Status in File') }}</th>
                                    <th>{{ __('Content') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
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
</style>

<script>
const PARSE_URL   = "{{ route('parcels.master_import.parse') }}";
const COMMIT_URL  = "{{ route('parcels.master_import.commit') }}";
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
    document.getElementById('btnParse').disabled = true;

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('_token', CSRF_TOKEN);

    try {
        const resp = await fetch(PARSE_URL, { method: 'POST', body: formData });
        const data = await resp.json();

        document.getElementById('loadingState').classList.add('d-none');
        document.getElementById('btnParse').disabled = false;

        if (!data.success) {
            showToast('{{ __("Error") }}', data.message, 'error');
            return;
        }

        parsedRows = data.rows;
        renderPreview(data);
    } catch (err) {
        document.getElementById('loadingState').classList.add('d-none');
        document.getElementById('btnParse').disabled = false;
        showToast('{{ __("Error") }}', err.message, 'error');
    }
}

function renderPreview(data) {
    const tbody = document.getElementById('previewBody');
    document.getElementById('totalRows').textContent = data.rows.length;

    tbody.innerHTML = '';
    data.rows.forEach(r => {
        const actionBadge = r.already_exists
            ? `<span class="badge rounded-pill bg-warning text-dark">{{ __('Update') }}</span>`
            : `<span class="badge rounded-pill bg-success">{{ __('New') }}</span>`;

        tbody.innerHTML += `
            <tr>
                <td class="ps-3 font-monospace fw-bold">${r.barcode}</td>
                <td>${r.sender_name || '—'}</td>
                <td>${r.recipient_name || '—'}</td>
                <td><span class="badge bg-light text-dark border">${r.status_name}</span></td>
                <td>${r.title || '—'}</td>
                <td class="text-center">${actionBadge}</td>
            </tr>
        `;
    });

    document.getElementById('previewSection').classList.remove('d-none');
}

async function commitImport() {
    if (parsedRows.length === 0) return;

    const totalRows = parsedRows.length;
    const batchSize = 50;
    let processedCount = 0;
    let totalCreated = 0;
    let totalUpdated = 0;

    document.getElementById('loadingState').classList.remove('d-none');
    document.getElementById('previewSection').classList.add('d-none');
    
    const pb = document.getElementById('progressBar');
    const pc = document.getElementById('progressCounter');
    const pt = document.getElementById('progressText');

    for (let i = 0; i < totalRows; i += batchSize) {
        const batch = parsedRows.slice(i, i + batchSize);
        try {
            const resp = await fetch(COMMIT_URL, {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ rows: batch }),
            });
            const res = await resp.json();
            if (res.success) {
                totalCreated += res.data.created;
                totalUpdated += res.data.updated;
            }
            processedCount += batch.length;
            const pct = Math.round((processedCount / totalRows) * 100);
            pb.style.width = pct + '%';
            pc.textContent = `${processedCount}/${totalRows}`;
            pt.textContent = `{{ __('Importing...') }} ${pct}%`;
        } catch (err) { console.error(err); }
    }

    document.getElementById('loadingState').classList.add('d-none');
    document.getElementById('resultTitle').textContent = '{{ __("Master Import Complete") }}';
    document.getElementById('resultDetails').innerHTML = `${totalCreated} {{ __('Created') }}, ${totalUpdated} {{ __('Updated') }}`;
    document.getElementById('resultSection').classList.remove('d-none');
}

function resetImport() {
    location.reload();
}
</script>
@endsection
