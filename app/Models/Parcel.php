<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    protected $fillable = [
        // Original fields
        'title',
        'barcode_in',
        'barcode_out',
        'status',
        'received_by',
        'delivered_to',
        'received_at',
        'delivered_at',
        'status_id',
        'notes',

        // New logistics fields
        'barcode_collection',
        'sender_name',
        'sender_contact_id',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'recipient_contact_id',
        'delivery_price',
        'collection_amount',
        'net_collection',
        'invoice_number',
        'collection_method',
        'collection_statement_barcode',
        'service_type',
        'booking_date',
        'delivery_date',
    ];

    protected $casts = [
        'received_at'       => 'datetime',
        'delivered_at'      => 'datetime',
        'booking_date'      => 'date',
        'delivery_date'     => 'date',
        'delivery_price'    => 'decimal:2',
        'collection_amount' => 'decimal:2',
        'net_collection'    => 'decimal:2',
    ];

    /**
     * The system user who received (logged) this parcel.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * The sender contact (from contacts table).
     */
    public function senderContact()
    {
        return $this->belongsTo(Contact::class, 'sender_contact_id');
    }

    /**
     * The recipient contact (from contacts table).
     */
    public function recipientContact()
    {
        return $this->belongsTo(Contact::class, 'recipient_contact_id');
    }

    /**
     * Get the dynamic status model.
     */
    public function statusModel()
    {
        return $this->belongsTo(ParcelStatus::class, 'status_id');
    }
}
