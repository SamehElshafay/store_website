{{-- Shared Dispatch Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const b = window.bootstrap || bootstrap;

    // Helper: Initialize Search Select (Bulletproof)
    function initSearchSelect(id, type) {
        const wrapper = document.getElementById(id);
        if(!wrapper) return;
        
        const input = wrapper.querySelector('.search-input');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const resultsDiv = wrapper.querySelector('.dropdown-results');

        if(!input || !resultsDiv) return;

        let timeout = null;
        let selectedIndex = -1;

        const updateUI = (query) => {
            const trimmed = query.trim();
            if (trimmed.length === 0) {
                resultsDiv.classList.add('results-hidden');
                resultsDiv.innerHTML = '';
                return;
            }

            const createText = type === 'sender' ? "{{ __('Create New Sender') }}" : "{{ __('Create New Recipient') }}";
            const addBtnHtml = `<a href="/contacts/create?type=${type}&name=${encodeURIComponent(trimmed)}" class="add-new-btn"><i class="bi bi-plus-circle-fill"></i> ${createText}: <span class="ms-1 fw-800">${trimmed}</span></a>`;
            
            resultsDiv.innerHTML = `<div class="records-area"><div class="p-3 text-center"><div class="spinner-border spinner-border-sm text-primary"></div></div></div>` + addBtnHtml;
            resultsDiv.classList.remove('results-hidden');
        };

        async function fetchResults(query) {
            const trimmed = query.trim();
            if (trimmed.length === 0) return;

            try {
                const response = await fetch(`/contacts-search?type=${type}&q=${encodeURIComponent(trimmed)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                
                const limitedData = data.slice(0, 10);
                const recordsArea = resultsDiv.querySelector('.records-area');
                if (!recordsArea) return;

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
                    html = `<div class="p-3 text-center small text-muted opacity-50">{{ __('No records found') }}</div>`;
                }
                
                recordsArea.innerHTML = html;
                
                recordsArea.querySelectorAll('.result-item').forEach(el => {
                    el.addEventListener('click', () => {
                        input.value = el.getAttribute('data-name');
                        hiddenInput.value = el.getAttribute('data-id');
                        resultsDiv.classList.add('results-hidden');
                    });
                });
            } catch (err) {
                console.error("Search fetch failed:", err);
            }
        }

        input.addEventListener('input', function() {
            const query = this.value;
            updateUI(query);
            clearTimeout(timeout);
            if (query.trim().length > 0) {
                timeout = setTimeout(() => fetchResults(query), 300);
            }
        });

        input.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                updateUI(this.value);
                fetchResults(this.value);
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                resultsDiv.classList.add('results-hidden');
            }
        });
    }

    // Helper: Fill Dispatch Form
    window.fillDispatchForm = function(parcel) {
        const form = document.getElementById('deliverForm');
        if (!form) return;

        document.getElementById('deliverParcelId').value = parcel.id;
        form.querySelector('[name="title"]').value = parcel.title || '';
        form.querySelector('[name="barcode_in"]').value = parcel.barcode_in || '';
        form.querySelector('[name="barcode_collection"]').value = parcel.barcode_collection || '';
        form.querySelector('[name="invoice_number"]').value = parcel.invoice_number || '';
        form.querySelector('[name="delivery_price"]').value = parcel.delivery_price || 0;
        form.querySelector('[name="collection_amount"]').value = parcel.collection_amount || 0;
        form.querySelector('[name="net_collection"]').value = parcel.net_collection || 0;
        form.querySelector('[name="notes"]').value = parcel.notes || '';
        
        if (parcel.sender_contact_id) {
            const select = document.getElementById('senderSelectDeliver');
            if (select) {
                select.querySelector('input[type="hidden"]').value = parcel.sender_contact_id;
                select.querySelector('.search-input').value = parcel.sender_name || (parcel.sender_contact ? parcel.sender_contact.name : '');
            }
        }
        if (parcel.recipient_contact_id) {
            const select = document.getElementById('recipientSelectDeliver');
            if (select) {
                select.querySelector('input[type="hidden"]').value = parcel.recipient_contact_id;
                select.querySelector('.search-input').value = parcel.recipient_name || (parcel.recipient_contact ? parcel.recipient_contact.name : '');
            }
        }
        
        // Enable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = false;
    };

    // Initialize Modal Search Selects
    initSearchSelect('senderSelectDeliver', 'sender');
    initSearchSelect('recipientSelectDeliver', 'recipient');

    // Global Dispatch Modal Opener
    window.openDispatchModal = function(id = null) {
        const modalEl = document.getElementById('deliverModal');
        if (!modalEl) return;
        
        const modal = new b.Modal(modalEl);
        const form = document.getElementById('deliverForm');
        
        if (form) {
            form.reset();
            form.querySelectorAll('.custom-search-select').forEach(wrapper => {
                wrapper.querySelector('input[type="hidden"]').value = '';
                wrapper.querySelector('.search-input').value = '';
            });
            // Disable submit by default if no ID
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = id === null;
        }
        
        const parcelIdInput = document.getElementById('deliverParcelId');
        if (parcelIdInput) parcelIdInput.value = id || '';
        
        if (id) {
            fetch(`/parcels/${id}/json`)
                .then(res => res.json())
                .then(parcel => {
                    fillDispatchForm(parcel);
                    modal.show();
                })
                .catch(err => {
                    console.error("Error fetching parcel:", err);
                    if (window.showToast) window.showToast("{{ __('Error') }}", "{{ __('Failed to load parcel data') }}", 'error');
                });
        } else {
            modal.show();
        }
    };

    // Barcode Lookup Logic (Real-time Validation)
    const barcodeInput = document.querySelector('#deliverForm [name="barcode_in"]');
    if (barcodeInput) {
        let lookupTimeout = null;
        barcodeInput.addEventListener('input', function() {
            const barcode = this.value.trim();
            clearTimeout(lookupTimeout);
            
            if (barcode.length >= 4) {
                lookupTimeout = setTimeout(() => {
                    fetch(`/parcels/find/${encodeURIComponent(barcode)}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                fillDispatchForm(res.data);
                                if (window.showToast) window.showToast("{{ __('Found') }}", "{{ __('Parcel identified successfully') }}", 'success');
                            } else {
                                if (window.showToast) window.showToast("{{ __('Error') }}", res.message, 'error');
                                // Keep submit disabled
                                const submitBtn = document.querySelector('#deliverForm button[type="submit"]');
                                if (submitBtn) submitBtn.disabled = true;
                            }
                        })
                        .catch(err => console.error("Barcode lookup failed:", err));
                }, 600);
            }
        });
    }

    // Shared Form Submission
    const deliverForm = document.getElementById('deliverForm');
    if (deliverForm) {
        deliverForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const parcelId = document.getElementById('deliverParcelId').value;
            
            if (!parcelId) {
                if (window.showToast) window.showToast("{{ __('Error') }}", "{{ __('Please scan a valid parcel barcode first.') }}", 'error');
                return;
            }

            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Saving...') }}`;
            btn.disabled = true;

            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if(value && key !== '_token') data[key] = value;
            });

            fetch(`/parcels/${parcelId}/deliver`, {
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
                    if (window.showToast) window.showToast("{{ __('Success') }}", result.message || "{{ __('Parcel dispatched successfully') }}", 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    let details = '';
                    if (result.errors) {
                        details = Object.values(result.errors).flat().join('<br>');
                    }
                    if (window.showToast) window.showToast("{{ __('Failed') }}", result.message || "{{ __('Validation Error') }}", 'error', details);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error("Submission error:", err);
                if (window.showToast) window.showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });

        // Shared Calculation Logic
        const deliveryInput = deliverForm.querySelector('[name="delivery_price"]');
        const collectionInput = deliverForm.querySelector('[name="collection_amount"]');
        const netInput = deliverForm.querySelector('[name="net_collection"]');

        if (deliveryInput && collectionInput && netInput) {
            const calc = () => {
                const d = parseFloat(deliveryInput.value) || 0;
                const c = parseFloat(collectionInput.value) || 0;
                netInput.value = (c - d).toFixed(2);
            };
            deliveryInput.addEventListener('input', calc);
            collectionInput.addEventListener('input', calc);
        }
    }
});
</script>
