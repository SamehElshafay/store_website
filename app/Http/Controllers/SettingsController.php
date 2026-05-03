<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $all_contacts = \App\Models\Contact::orderBy('name')->get();
        $parcel_statuses = \App\Models\ParcelStatus::orderBy('sort_order')->get();
        
        $settings = [
            'default_receive_sender_id'    => \App\Models\Setting::get('default_receive_sender_id'),
            'default_receive_recipient_id' => \App\Models\Setting::get('default_receive_recipient_id'),
            'default_dispatch_sender_id'   => \App\Models\Setting::get('default_dispatch_sender_id'),
            'default_dispatch_recipient_id'=> \App\Models\Setting::get('default_dispatch_recipient_id'),
            'default_dispatch_status_id'   => \App\Models\Setting::get('default_dispatch_status_id'),
        ];

        return view('settings.index', compact('all_contacts', 'settings', 'parcel_statuses'));
    }

    public function updateDefaults(Request $request)
    {
        $data = $request->only([
            'default_receive_sender_id',
            'default_receive_recipient_id',
            'default_dispatch_sender_id',
            'default_dispatch_recipient_id',
            'default_dispatch_status_id',
        ]);

        foreach ($data as $key => $value) {
            if ($value !== null && $value !== '') {
                \App\Models\Setting::set($key, $value);
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('Default contacts updated successfully')
        ]);
    }

    public function clearParcels()
    {
        \App\Models\Parcel::truncate();
        return response()->json([
            'success' => true,
            'message' => __('All parcels have been deleted successfully')
        ]);
    }
}
