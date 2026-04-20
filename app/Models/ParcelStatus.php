<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelStatus extends Model
{
    protected $fillable = ['name', 'name_ar', 'name_en', 'key', 'color', 'is_default', 'sort_order'];

    public function getDisplayNameAttribute()
    {
        if (app()->getLocale() == 'ar') {
            return $this->name_ar ?: $this->name;
        }
        return $this->name_en ?: $this->name;
    }
}
