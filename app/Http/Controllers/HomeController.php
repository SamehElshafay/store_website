<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $deliveredStatus = \App\Models\ParcelStatus::where('key', 'delivered')->first();
        $deliveredId = $deliveredStatus ? $deliveredStatus->id : 0;

        $stats = [
            'total_stock' => \App\Models\Parcel::where('status_id', '!=', $deliveredId)->count(),
            'received_today' => \App\Models\Parcel::whereDate('created_at', \Carbon\Carbon::today())->count(),
            'delivered_today' => \App\Models\Parcel::where('status_id', $deliveredId)
                ->whereDate('updated_at', \Carbon\Carbon::today())
                ->count(),
        ];

        $status_counts = \App\Models\Parcel::select('status_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id');

        $recent_parcels = \App\Models\Parcel::with(['receiver', 'statusModel'])->latest()->take(5)->get();
        $statuses = \App\Models\ParcelStatus::orderBy('sort_order')->get();

        return view('home', compact('stats', 'recent_parcels', 'statuses', 'status_counts'));
    }

    public function showParcel($id)
    {
        $parcel = \App\Models\Parcel::with(['receiver', 'senderContact', 'recipientContact', 'statusModel'])->findOrFail($id);
        $statuses = \App\Models\ParcelStatus::orderBy('sort_order')->get();
        return view('parcels.show', compact('parcel', 'statuses'));
    }
}
