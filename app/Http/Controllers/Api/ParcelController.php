<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Parcel;
use App\Models\ParcelStatus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ParcelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Parcel::with(['receiver', 'statusModel']);

        // Optional: Default to today if no date filter is provided
        // if (!$request->has('from_date') && !$request->has('to_date') && !$request->has('period')) {
        //     $query->whereDate('created_at', Carbon::today());
        // }

        // Period Filtering
        if ($request->filled('period') && $request->period != 'all') {
            switch ($request->period) {
                case 'today': $query->whereDate('created_at', Carbon::today()); break;
                case 'week': $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); break;
                case 'month': $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year); break;
                case 'year': $query->whereYear('created_at', Carbon::now()->year); break;
            }
        }

        // Date Range Filtering
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Global Search (Search Anything)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('barcode_in', 'like', "%$s%")
                  ->orWhere('barcode_out', 'like', "%$s%")
                  ->orWhere('invoice_number', 'like', "%$s%")
                  ->orWhere('id', 'like', "%$s%")
                  ->orWhere('recipient_phone', 'like', "%$s%")
                  ->orWhere('recipient_name', 'like', "%$s%")
                  ->orWhere('sender_name', 'like', "%$s%")
                  ->orWhereHas('recipientContact', function($rq) use ($s) {
                      $rq->where('phone', 'like', "%$s%")
                        ->orWhere('name', 'like', "%$s%");
                  })
                  ->orWhereHas('senderContact', function($sq) use ($s) {
                      $sq->where('phone', 'like', "%$s%")
                        ->orWhere('name', 'like', "%$s%");
                  });
            });
        }

        // Column Filtering
        if ($request->filled('title')) {
            $query->where('title', 'like', "%{$request->title}%");
        }
        if ($request->filled('barcode')) {
            $b = $request->barcode;
            $query->where(function($q) use ($b) {
                $q->where('barcode_in', 'like', "%$b%")
                  ->orWhere('barcode_out', 'like', "%$b%");
            });
        }
        if ($request->filled('status')) $query->where('status_id', $request->status);
        if ($request->filled('method')) $query->where('collection_method', $request->method);
        if ($request->filled('recipient')) {
            $r = $request->recipient;
            $query->where(function($q) use ($r) {
                $q->where('recipient_name', 'like', "%$r%")
                  ->orWhere('recipient_phone', 'like', "%$r%")
                  ->orWhereHas('recipientContact', function($rq) use ($r) {
                      $rq->where('name', 'like', "%$r%")
                        ->orWhere('phone', 'like', "%$r%");
                  });
            });
        }

        $parcels = $query->latest()->paginate(25)->withQueryString();
        $statuses = ParcelStatus::orderBy('sort_order')->get();

        // 1. Priority: AJAX calls from the site for the dynamic table
        if ($request->ajax() && !$request->wantsJson()) {
            return view('parcels.partials.table', compact('parcels', 'statuses'))->render();
        }

        // 2. Secondary: Pure JSON API requests
        if ($request->expectsJson()) {
            return response()->json($parcels);
        }

        // 3. Final: Initial page load
        return view('parcels.index', compact('parcels', 'statuses'));
    }

    /**
     * Store a newly created resource (Receive Parcel).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'barcode_in' => 'required|string|unique:parcels,barcode_in',
            'barcode_collection' => 'nullable|string|max:255',
            'sender_contact_id' => 'required|exists:contacts,id',
            'recipient_contact_id' => 'required|exists:contacts,id',
            'delivery_price' => 'nullable|numeric|min:0',
            'collection_amount' => 'nullable|numeric|min:0',
            'net_collection' => 'nullable|numeric',
            'invoice_number' => 'nullable|string|max:255',
            'collection_method' => 'nullable|in:cash,card,transfer,none',
            'collection_statement_barcode' => 'nullable|string|max:255',
            'service_type' => 'nullable|string|max:255',
            'booking_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Calculate net collection on backend for integrity
        $deliveryPrice = $request->input('delivery_price', 0);
        $collectionAmount = $request->input('collection_amount', 0);
        $net = $collectionAmount - $deliveryPrice;

        $defaultStatus = \App\Models\ParcelStatus::where('is_default', true)->first();

        $parcel = Parcel::create(array_merge($validated, [
            'status' => $defaultStatus ? $defaultStatus->key : 'ready', 
            'status_id' => $defaultStatus ? $defaultStatus->id : null,
            'received_by' => Auth::id(),
            'received_at' => Carbon::now(),
            'net_collection' => $net,
        ]));

        return response()->json([
            'success' => true,
            'message' => __('Outgoing parcel registered successfully'),
            'data' => $parcel
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $parcel = Parcel::where('id', $id)
            ->orWhere('barcode_in', $id)
            ->orWhere('barcode_out', $id)
            ->with(['receiver', 'senderContact', 'recipientContact'])
            ->firstOrFail();

        return response()->json($parcel);
    }

    /**
     * Find parcel by barcode.
     */
    public function findByBarcode($barcode)
    {
        $parcel = Parcel::where('barcode_in', $barcode)
            ->orWhere('barcode_out', $barcode)
            ->with(['senderContact', 'recipientContact', 'statusModel'])
            ->first();

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => __('Parcel not found with this barcode.')
            ], 404);
        }

        if ($parcel->status === 'delivered') {
            return response()->json([
                'success' => false,
                'message' => __('This parcel has already been dispatched/delivered.')
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $parcel
        ]);
    }

    /**
     * Update the parcel status to Delivered (Deliver Parcel).
     */
    public function deliver(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('Current Locale: ' . \App::getLocale());

        $parcel = Parcel::where('id', $id)
            ->orWhere('barcode_in', $id)
            ->first();

        if (!$parcel) {
            return response()->json([
                'message' => __('Parcel not found or barcode is incorrect.'),
                'errors' => ['barcode' => [__('The provided barcode does not match any registered parcel.')]]
            ], 404);
        }

        if ($parcel->status === 'delivered') {
            return response()->json(['message' => __('Parcel already delivered')], 400);
        }

        $validated = $request->validate([
            'delivered_to' => 'nullable|string|max:255',
            'sender_contact_id' => 'nullable|exists:contacts,id',
            'recipient_contact_id' => 'nullable|exists:contacts,id',
            'barcode_out' => 'nullable|string|unique:parcels,barcode_out,' . $parcel->id,
            'collection_amount' => 'nullable|numeric|min:0',
            'net_collection' => 'nullable|numeric|min:0',
            'collection_method' => 'nullable|in:cash,card,transfer,none',
            'delivery_date' => 'nullable|date',
            'booking_date' => 'nullable|date',
            'delivery_price' => 'nullable|numeric|min:0',
            'invoice_number' => 'nullable|string|max:255',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $parcel->update(array_merge($validated, [
            'status' => $validated['status'] ?? 'delivered',
            'delivered_by' => Auth::id(),
            'delivered_at' => Carbon::now(),
            'barcode_out' => $validated['barcode_out'] ?? $parcel->barcode_in,
            'delivered_to' => $validated['delivered_to'] ?? $parcel->recipient_name,
        ]));

        return response()->json([
            'message' => 'Parcel delivered successfully',
            'data' => $parcel
        ]);
    }

    /**
     * Update status (Strict forward-only based on sort_order)
     */
    public function updateStatus(Request $request, $id)
    {
        $parcel = Parcel::with('statusModel')->findOrFail($id);
        $currentOrder = $parcel->statusModel ? $parcel->statusModel->sort_order : 0;

        $request->validate([
            'status_id' => 'required|exists:parcel_statuses,id',
            'notes' => 'nullable|string'
        ]);

        $newStatus = \App\Models\ParcelStatus::findOrFail($request->status_id);

        if ($newStatus->sort_order <= $currentOrder) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot move to a previous or same status. Sequence must be forward only.')
            ], 422);
        }

        $parcel->update([
            'status_id' => $newStatus->id,
            'status' => $newStatus->key,
            'notes' => $request->notes ? $parcel->notes . "\n[" . now()->format('Y-m-d H:i') . "] " . $request->notes : $parcel->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Status updated successfully to :status', ['status' => $newStatus->display_name]),
            'data' => $parcel
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Bulk update parcel statuses.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'parcel_ids' => 'required|array',
            'parcel_ids.*' => 'exists:parcels,id',
            'status_id' => 'required|exists:parcel_statuses,id',
        ]);

        $newStatus = \App\Models\ParcelStatus::findOrFail($request->status_id);
        $parcels = Parcel::with('statusModel')->whereIn('id', $request->parcel_ids)->get();
        $updatedCount = 0;
        $errors = [];

        foreach ($parcels as $parcel) {
            $currentOrder = $parcel->statusModel ? $parcel->statusModel->sort_order : 0;
            
            if ($newStatus->sort_order <= $currentOrder) {
                $errors[] = "#{$parcel->id}: " . __('Cannot move backward to :status', ['status' => $newStatus->name]);
                continue;
            }

            $parcel->update([
                'status_id' => $newStatus->id,
                'status' => $newStatus->key
            ]);
            $updatedCount++;
        }

        return response()->json([
            'success' => $updatedCount > 0,
            'updated' => $updatedCount,
            'errors' => $errors,
            'message' => $updatedCount > 0 
                ? __(':count parcels updated successfully.', ['count' => $updatedCount])
                : __('No parcels were updated. Check sequence validation.'),
        ]);
    }

    /**
     * Bulk update parcel statuses by barcode.
     */
    public function bulkUpdateStatusByBarcode(Request $request)
    {
        $validated = $request->validate([
            'barcodes' => 'required|array',
            'barcodes.*' => 'required|string',
            'status_id' => 'required|exists:parcel_statuses,id',
        ]);

        $newStatus = \App\Models\ParcelStatus::findOrFail($request->status_id);
        $barcodes = array_unique($request->barcodes);
        
        $parcels = Parcel::with('statusModel')
            ->whereIn('barcode_in', $barcodes)
            ->orWhereIn('barcode_out', $barcodes)
            ->get();

        $updatedCount = 0;
        $errors = [];
        $foundBarcodes = [];

        foreach ($parcels as $parcel) {
            $foundBarcodes[] = $parcel->barcode_in;
            $foundBarcodes[] = $parcel->barcode_out;

            $currentOrder = $parcel->statusModel ? $parcel->statusModel->sort_order : 0;
            
            if ($newStatus->sort_order <= $currentOrder) {
                $errors[] = "#{$parcel->barcode_in}: " . __('Cannot move backward to :status', ['status' => $newStatus->display_name]);
                continue;
            }

            $parcel->update([
                'status_id' => $newStatus->id,
                'status' => $newStatus->key
            ]);
            $updatedCount++;
        }

        // Check for missing barcodes
        $missing = array_diff($barcodes, array_filter($foundBarcodes));
        foreach ($missing as $m) {
            $errors[] = "$m: " . __('Parcel not found');
        }

        return response()->json([
            'success' => $updatedCount > 0,
            'updated' => $updatedCount,
            'errors' => $errors,
            'message' => $updatedCount > 0 
                ? __(':count parcels updated successfully.', ['count' => $updatedCount])
                : __('No parcels were updated.'),
        ]);
    }

    /**
     * Bulk register or update parcels.
     */
    public function bulkRegister(Request $request)
    {
        $validated = $request->validate([
            'parcels' => 'required|array',
            'parcels.*.barcode' => 'required|string',
            'parcels.*.details' => 'nullable|array',
            'status_id' => 'required|exists:parcel_statuses,id',
        ]);

        $statusModel = \App\Models\ParcelStatus::findOrFail($request->status_id);
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        foreach ($request->parcels as $item) {
            $barcode = $item['barcode'];
            $details = $item['details'] ?? [];

            // Find existing or create new
            $parcel = Parcel::where('barcode_in', $barcode)
                ->orWhere('barcode_out', $barcode)
                ->first();

            $data = [
                'status_id' => $statusModel->id,
                'status' => $statusModel->key,
            ];

            // Map details if provided
            if (!empty($details)) {
                if (isset($details['title'])) $data['title'] = $details['title'];
                if (isset($details['sender_id'])) $data['sender_contact_id'] = $details['sender_id'];
                if (isset($details['recipient_id'])) $data['recipient_contact_id'] = $details['recipient_id'];
                if (isset($details['collection_amount'])) $data['collection_amount'] = $details['collection_amount'];
                if (isset($details['delivery_price'])) $data['delivery_price'] = $details['delivery_price'];
                
                // Auto-calc net collection
                $coll = floatval($details['collection_amount'] ?? 0);
                $deliv = floatval($details['delivery_price'] ?? 0);
                $data['net_collection'] = $coll - $deliv;

                if (isset($details['service_type'])) $data['service_type'] = $details['service_type'];
                if (isset($details['notes'])) $data['notes'] = $details['notes'];
                if (isset($details['booking_date'])) $data['booking_date'] = $details['booking_date'];
                if (isset($details['delivery_date'])) $data['delivery_date'] = $details['delivery_date'];
            }

            if ($parcel) {
                // Update existing
                $parcel->update($data);
                $results['updated']++;
            } else {
                // Create new
                $data['barcode_in'] = $barcode;
                if (!isset($data['title'])) $data['title'] = __('Bulk Registered Parcel') . " " . $barcode;
                
                Parcel::create($data);
                $results['created']++;
            }
        }

        $total = $results['created'] + $results['updated'];

        return response()->json([
            'success' => $total > 0,
            'message' => __(':total parcels processed (:created created, :updated updated).', [
                'total' => $total,
                'created' => $results['created'],
                'updated' => $results['updated']
            ]),
            'data' => $results
        ]);
    }

    public function destroy(string $id)
    {
        $parcel = Parcel::findOrFail($id);
        $parcel->delete();
        return response()->json(['message' => 'Parcel deleted successfully']);
    }

    /**
     * Export parcels to Excel (CSV format).
     */
    public function export()
    {
        $parcels = Parcel::with(['receiver', 'recipientContact'])->get();
        
        $filename = "parcels_export_" . date('Y-m-d_H-i') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'باركود الطرد', 'باركود التحصيل', 'إسم المرسل', 'إسم المستقبل', 
            'هاتف المستقبل', 'عنوان المستقبل', 'سعر التوصيل', 'التحصيل', 
            'صافي التحصيل', 'ملاحظات', 'رقم الفاتورة', 'طريقة التحصيل', 
            'باركود كشف التحصيل', 'نوع الخدمة', 'تاريخ الحجز', 'تاريخ التوصيل'
        ];

        $callback = function() use($parcels, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);

            foreach ($parcels as $parcel) {
                fputcsv($file, [
                    $parcel->barcode_in,
                    $parcel->barcode_collection,
                    $parcel->receiver->name ?? '---',
                    $parcel->recipient_name ?? ($parcel->recipientContact->name ?? '---'),
                    $parcel->recipient_phone ?? ($parcel->recipientContact->phone ?? '---'),
                    $parcel->recipient_address ?? ($parcel->recipientContact->address ?? '---'),
                    $parcel->delivery_price,
                    $parcel->collection_amount,
                    $parcel->net_collection,
                    $parcel->notes,
                    $parcel->invoice_number,
                    $parcel->collection_method,
                    $parcel->collection_statement_barcode,
                    $parcel->service_type,
                    $parcel->booking_date ? $parcel->booking_date->format('Y-m-d') : '---',
                    $parcel->delivery_date ? $parcel->delivery_date->format('Y-m-d') : '---',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
