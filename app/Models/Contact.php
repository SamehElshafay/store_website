<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'phone',
        'phone_alt',
        'email',
        'address',
        'city',
        'region',
        'postal_code',
        'company_name',
        'tax_number',
        'notes',
        'created_by',
    ];

    /**
     * Parcels where this contact is the sender.
     */
    public function sentParcels()
    {
        return $this->hasMany(Parcel::class, 'sender_contact_id');
    }

    /**
     * Parcels where this contact is the recipient.
     */
    public function receivedParcels()
    {
        return $this->hasMany(Parcel::class, 'recipient_contact_id');
    }

    /**
     * The user who created this contact.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter by type.
     */
    public function scopeSenders($query)
    {
        return $query->where('type', 'sender');
    }

    public function scopeRecipients($query)
    {
        return $query->where('type', 'recipient');
    }
}
