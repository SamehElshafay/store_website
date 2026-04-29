<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper: Fill Receive Form
    window.fillReceiveForm = function(parcel) {
        const form = document.getElementById('receiveForm');
        if (!form) return;

        document.getElementById('receiveParcelId').value = parcel.id;
        form.querySelector('[name="title"]').value = parcel.title || '';
        form.querySelector('[name="barcode_in"]').value = parcel.barcode_in || '';
        form.querySelector('[name="barcode_collection"]').value = parcel.barcode_collection || '';
        form.querySelector('[name="invoice_number"]').value = parcel.invoice_number || '';
        form.querySelector('[name="booking_date"]').value = parcel.booking_date ? parcel.booking_date.split('T')[0] : '';
        form.querySelector('[name="delivery_date"]').value = parcel.delivery_date ? parcel.delivery_date.split('T')[0] : '';
        form.querySelector('[name="notes"]').value = parcel.notes || '';
        
        if (parcel.sender_contact_id) {
            const select = document.getElementById('senderSelectReceive');
            if (select) {
                select.querySelector('input[type="hidden"]').value = parcel.sender_contact_id;
                const inputEl = select.querySelector('.search-input');
                if (inputEl) inputEl.value = parcel.sender_name || (parcel.sender_contact ? parcel.sender_contact.name : '');
            }
        } else if (window.defaultSettings && window.defaultSettings.receive && window.defaultSettings.receive.sender_id) {
            const def = window.defaultSettings.receive;
            const select = document.getElementById('senderSelectReceive');
            if (select) {
                select.querySelector('input[type="hidden"]').value = def.sender_id;
                const inputEl = select.querySelector('.search-input');
                if (inputEl) inputEl.value = def.sender_name;
            }
        }

        if (parcel.recipient_contact_id) {
            const select = document.getElementById('recipientSelectReceive');
            if (select) {
                select.querySelector('input[type="hidden"]').value = parcel.recipient_contact_id;
                const inputEl = select.querySelector('.search-input');
                if (inputEl) inputEl.value = parcel.recipient_name || (parcel.recipient_contact ? parcel.recipient_contact.name : '');
            }
        } else if (window.defaultSettings && window.defaultSettings.receive && window.defaultSettings.receive.recipient_id) {
            const def = window.defaultSettings.receive;
            const select = document.getElementById('recipientSelectReceive');
            if (select) {
                select.querySelector('input[type="hidden"]').value = def.recipient_id;
                const inputEl = select.querySelector('.search-input');
                if (inputEl) inputEl.value = def.recipient_name;
            }
        }
    };

    // Initialize Modal Search Selects
    if (window.initSearchSelect) {
        window.initSearchSelect('senderSelectReceive', 'sender');
        window.initSearchSelect('recipientSelectReceive', 'recipient');
    }

    // Global Receive Modal Opener
    window.openReceiveModal = function(id = null, btn = null) {
        const modalEl = document.getElementById('receiveModal');
        if (!modalEl) return;
        
        const b = window.bootstrap || bootstrap;
        const modal = new b.Modal(modalEl);
        const form = document.getElementById('receiveForm');
        
        if (form) {
            form.reset();
            const today = new Date().toISOString().split('T')[0];
            const bookingField = form.querySelector('[name="booking_date"]');
            const deliveryField = form.querySelector('[name="delivery_date"]');
            if (bookingField) bookingField.value = today;
            if (deliveryField) deliveryField.value = today;

            form.querySelectorAll('.custom-search-select').forEach(wrapper => {
                wrapper.querySelector('input[type="hidden"]').value = '';
                const inputEl = wrapper.querySelector('.search-input');
                if (inputEl) inputEl.value = '';
            });
        }
        
        const parcelIdInput = document.getElementById('receiveParcelId');
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
                    fillReceiveForm(parcel);
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
            // Apply Defaults for New Parcel
            if (window.defaultSettings && window.defaultSettings.receive) {
                const def = window.defaultSettings.receive;
                if (def.sender_id) {
                    const wrap = document.getElementById('senderSelectReceive');
                    if (wrap) {
                        wrap.querySelector('input[type="hidden"]').value = def.sender_id;
                        wrap.querySelector('.search-input').value = def.sender_name;
                    }
                }
                if (def.recipient_id) {
                    const wrap = document.getElementById('recipientSelectReceive');
                    if (wrap) {
                        wrap.querySelector('input[type="hidden"]').value = def.recipient_id;
                        wrap.querySelector('.search-input').value = def.recipient_name;
                    }
                }
            }
            modal.show();
        }
    };

    // Global Parcel Edit Modal Switcher
    window.openParcelEditModal = function(id, modalType, btn = null) {
        if (modalType === 'dispatch') {
            if (typeof window.openDispatchModal === 'function') {
                window.openDispatchModal(id, btn);
            } else {
                console.error("openDispatchModal not found");
            }
        } else {
            window.openReceiveModal(id, btn);
        }
    };

    // Receive Form Submission
    const receiveForm = document.getElementById('receiveForm');
    if (receiveForm) {
        receiveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const parcelId = document.getElementById('receiveParcelId').value;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> {{ __('Saving...') }}`;
            btn.disabled = true;

            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if(value && key !== '_token') data[key] = value;
            });

            const url = parcelId ? `/parcels/${parcelId}` : '/parcels';
            const method = parcelId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
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
                    if (window.showToast) window.showToast("{{ __('Success') }}", result.message, 'success');
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
                if (window.showToast) window.showToast("{{ __('Error') }}", "{{ __('System Error occurred') }}", 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    }
});
</script>
