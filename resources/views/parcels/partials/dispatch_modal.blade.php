{{-- Dispatch/Deliver Modal Shared Partial --}}
<div class="modal fade" id="deliverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content glass-modal border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-bold text-main d-flex align-items-center">
                    <i class="bi bi-truck me-2 text-primary"></i>
                    {{ __('Register Outgoing Parcel') }}
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deliverForm">
                @csrf
                <input type="hidden" name="parcel_id" id="deliverParcelId">
                <div class="modal-body p-4 pt-0">
                    <div class="mb-4 bg-primary bg-opacity-10 p-3 rounded-4">
                        <p class="small text-primary mb-0 fw-bold"><i class="bi bi-info-circle me-1"></i> {{ __('Scan the original barcode to identify the parcel and finalize delivery details.') }}</p>
                    </div>

                    <div class="row g-4">
                        {{-- Identifiers Section --}}
                        <div class="col-lg-12">
                            <div class="glass-card p-3 rounded-4 border-0 shadow-sm" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05) !important;">
                                <h6 class="small fw-bold text-uppercase text-primary mb-3">{{ __('Parcel Identifiers') }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">{{ __('Scan Original Barcode') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark-soft border-0 text-muted"><i class="bi bi-upc-scan"></i></span>
                                            <input type="text" name="barcode_in" class="form-control border-0 bg-dark-soft text-main" placeholder="Original barcode..." required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">{{ __('Collection Barcode') }} ({{ __('Optional') }})</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark-soft border-0 text-muted"><i class="bi bi-barcode"></i></span>
                                            <input type="text" name="barcode_collection" class="form-control border-0 bg-dark-soft text-main" placeholder="Cashier barcode...">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">{{ __('Invoice/Order Number') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark-soft border-0 text-muted"><i class="bi bi-hash"></i></span>
                                            <input type="text" name="invoice_number" class="form-control border-0 bg-dark-soft text-main" placeholder="INV-XXXXX">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Core Information --}}
                        <div class="col-lg-6">
                            <div class="glass-card p-3 rounded-4 h-100 border-0 shadow-sm" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05) !important;">
                                <h6 class="small fw-bold text-uppercase text-primary mb-3">{{ __('Parcel Content & Source') }}</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">{{ __('Parcel Title / Description') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control border-0 bg-dark-soft text-main" placeholder="e.g. iPhone 15 Pro, Medical Box..." required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted mb-2">{{ __('Sender (Source)') }} <span class="text-danger">*</span></label>
                                        <div class="custom-search-select has-search" id="senderSelectDeliver">
                                            <input type="hidden" name="sender_contact_id" required>
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" placeholder="{{ __('Search senders...') }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted mb-2">{{ __('Recipient (Destination)') }} <span class="text-danger">*</span></label>
                                        <div class="custom-search-select has-search" id="recipientSelectDeliver">
                                            <input type="hidden" name="recipient_contact_id" required>
                                            <input type="text" class="form-control border-0 bg-dark-soft search-input text-main" placeholder="{{ __('Search recipients...') }}" autocomplete="off">
                                            <div class="dropdown-results shadow-lg border-0 rounded-3 results-hidden"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Financials & Notes --}}
                        <div class="col-lg-6">
                            <div class="glass-card p-3 rounded-4 h-100 border-0 shadow-sm" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05) !important;">
                                <h6 class="small fw-bold text-uppercase text-primary mb-3">{{ __('Financials & Delivery') }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Delivery Price') }}</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="delivery_price" class="form-control border-0 bg-dark-soft text-main" value="0.00">
                                            <span class="input-group-text bg-dark-soft border-0 text-muted">EGP</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Total Collection') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="collection_amount" class="form-control border-0 bg-dark-soft text-main" value="0.00" required>
                                            <span class="input-group-text bg-dark-soft border-0 text-muted">EGP</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Collection Method') }}</label>
                                        <select name="collection_method" class="form-select border-0 bg-dark-soft text-main">
                                            <option value="none">{{ __('None') }}</option>
                                            <option value="cash">{{ __('Cash') }}</option>
                                            <option value="card">{{ __('Card') }}</option>
                                            <option value="transfer">{{ __('Transfer') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Net Collection') }}</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="net_collection" class="form-control border-0 bg-dark-soft text-main" value="0.00" readonly>
                                            <span class="input-group-text bg-dark-soft border-0 text-muted">EGP</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Booking Date') }}</label>
                                        <input type="date" name="booking_date" class="form-control border-0 bg-dark-soft text-main">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">{{ __('Delivery Date (Est.)') }}</label>
                                        <input type="date" name="delivery_date" class="form-control border-0 bg-dark-soft text-main">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">{{ __('Additional Notes') }}</label>
                                        <textarea name="notes" class="form-control border-0 bg-dark-soft text-main" rows="3" placeholder="Any specific delivery instructions..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-lg btn-link text-muted text-decoration-none px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-premium rounded-pill px-5 py-2 fw-bold shadow-lg">
                        <i class="bi bi-check2-circle me-1"></i> {{ __('Register & Dispatch') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
