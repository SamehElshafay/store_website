<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelMovement extends Model
{
    protected $fillable = ['parcel_id', 'status_id', 'user_id', 'notes'];

    public function parcel()
    {
        return $this->belongsTo(Parcel::class);
    }

    public function status()
    {
        return $this->belongsTo(ParcelStatus::class, 'status_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
