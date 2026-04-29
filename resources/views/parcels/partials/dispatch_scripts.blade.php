{{-- Shared Dispatch Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const b = window.bootstrap || bootstrap;

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
        } else if (window.defaultSettings && window.defaultSettings.dispatch && window.defaultSettings.dispatch.sender_id) {
            const def = window.defaultSettings.dispatch;
            const select = document.getElementById('senderSelectDeliver');
            if (select) {
                select.querySelector('input[type="hidden"]').value = def.sender_id;
                select.querySelector('.search-input').value = def.sender_name;
            }
        }

        if (parcel.recipient_contact_id) {
            const select = document.getElementById('recipientSelectDeliver');
            if (select) {
                select.querySelector('input[type="hidden"]').value = parcel.recipient_contact_id;
                select.querySelector('.search-input').value = parcel.recipient_name || (parcel.recipient_contact ? parcel.recipient_contact.name : '');
            }
        } else if (window.defaultSettings && window.defaultSettings.dispatch && window.defaultSettings.dispatch.recipient_id) {
            const def = window.defaultSettings.dispatch;
            const select = document.getElementById('recipientSelectDeliver');
            if (select) {
                select.querySelector('input[type="hidden"]').value = def.recipient_id;
                select.querySelector('.search-input').value = def.recipient_name;
            }
        }
        
        // Enable submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = false;
    };

    // Initialize Modal Search Selects
    if (window.initSearchSelect) {
        window.initSearchSelect('senderSelectDeliver', 'sender');
        window.initSearchSelect('recipientSelectDeliver', 'recipient');
    }

    // Global Dispatch Modal Opener
    window.openDispatchModal = function(id = null, btn = null) {
        const modalEl = document.getElementById('deliverModal');
        if (!modalEl) return;
        
        const modal = new b.Modal(modalEl);
        const form = document.getElementById('deliverForm');
        
        if (form) {
            form.reset();
            const today = new Date().toISOString().split('T')[0];
            const bookingField = form.querySelector('[name="booking_date"]');
            const deliveryField = form.querySelector('[name="delivery_date"]');
            if (bookingField) bookingField.value = today;
            if (deliveryField) deliveryField.value = today;

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
            let originalHtml = '';
            if (btn) {
                originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }

            fetch(`/parcels/${id}/json`)
                .then(res => res.json())
                .then(parcel => {
                    fillDispatchForm(parcel);
                    modal.show();
                })
                .catch(err => {
                    console.error("Error fetching parcel:", err);
                    if (window.showToast) window.showToast("{{ __('Error') }}", "{{ __('Failed to load parcel data') }}", 'error');
                })
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                });
        } else {
            // Apply Defaults for New Dispatch (Rare, but for consistency)
            if (window.defaultSettings && window.defaultSettings.dispatch) {
                const def = window.defaultSettings.dispatch;
                if (def.sender_id) {
                    const wrap = document.getElementById('senderSelectDeliver');
                    if (wrap) {
                        wrap.querySelector('input[type="hidden"]').value = def.sender_id;
                        wrap.querySelector('.search-input').value = def.sender_name;
                    }
                }
                if (def.recipient_id) {
                    const wrap = document.getElementById('recipientSelectDeliver');
                    if (wrap) {
                        wrap.querySelector('input[type="hidden"]').value = def.recipient_id;
                        wrap.querySelector('.search-input').value = def.recipient_name;
                    }
                }
            }
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
