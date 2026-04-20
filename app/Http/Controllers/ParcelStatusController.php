<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParcelStatusController extends Controller
{
    public function index()
    {
        $statuses = \App\Models\ParcelStatus::orderBy('sort_order', 'asc')->get();
        return view('settings.statuses.index', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        // Auto-assign next sort order
        $maxOrder = \App\Models\ParcelStatus::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        $validated['key'] = \Str::slug($validated['name_en']);
        $validated['name'] = $validated['name_ar'];
        
        $status = \App\Models\ParcelStatus::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('Status created successfully'),
            'data' => $status
        ]);
    }

    public function update(Request $request, $id)
    {
        $status = \App\Models\ParcelStatus::findOrFail($id);
        
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $status->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('Status updated successfully')
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        
        foreach ($request->order as $index => $id) {
            \App\Models\ParcelStatus::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true, 'message' => __('Order updated')]);
    }

    public function destroy($id)
    {
        $status = \App\Models\ParcelStatus::findOrFail($id);
        
        // Check if it's the default status
        if ($status->is_default) {
            return response()->json([
                'success' => false, 
                'message' => __('Cannot delete the default status.')
            ], 400);
        }

        // Check if any parcels are using this status
        $usageCount = \App\Models\Parcel::where('status_id', $id)->count();
        if ($usageCount > 0) {
            return response()->json([
                'success' => false, 
                'message' => __('This status is currently assigned to :count parcels and cannot be deleted.', ['count' => $usageCount])
            ], 400);
        }

        $status->delete();

        return response()->json([
            'success' => true,
            'message' => __('Status deleted successfully')
        ]);
    }

    public function setDefault($id)
    {
        \App\Models\ParcelStatus::where('is_default', true)->update(['is_default' => false]);
        \App\Models\ParcelStatus::where('id', $id)->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => __('Default status updated successfully')
        ]);
    }
}
