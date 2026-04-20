<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display contacts list with filtering by type.
     */
    public function index(Request $request)
    {
        $type = $request->get('type'); // 'sender' | 'recipient' | null (all)

        $contacts = Contact::query()
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('contacts.index', compact('contacts', 'type'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        $defaultType = $request->get('type', 'sender');
        return view('contacts.create', compact('defaultType'));
    }

    /**
     * Store a new contact.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'          => 'required|in:sender,recipient',
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20|unique:contacts,phone',
            'address'       => 'required|string|max:500',
            'notes'         => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();

        $contact = Contact::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Recipient saved successfully!'),
                'redirect' => route('contacts.index')
            ]);
        }

        return redirect()->route('contacts.show', $contact)
                         ->with('success', __('Contact created successfully.'));
    }

    /**
     * Show a contact's details and their parcel history.
     */
    public function show(Contact $contact)
    {
        $contact->load(['sentParcels', 'receivedParcels', 'createdBy']);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show edit form.
     */
    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update contact details.
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'type'          => 'required|in:sender,recipient',
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:20|unique:contacts,phone,' . $contact->id,
            'address'       => 'required|string|max:500',
            'notes'         => 'nullable|string',
        ]);

        $contact->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Recipient updated successfully!'),
                'redirect' => route('contacts.index')
            ]);
        }

        return redirect()->route('contacts.show', $contact)
                         ->with('success', __('Contact updated successfully.'));
    }

    /**
     * Soft delete a contact.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Recipient deleted successfully.')
            ]);
        }

        return redirect()->route('contacts.index')
                         ->with('success', __('Contact deleted successfully.'));
    }

    /**
     * AJAX: search contacts for autocomplete in parcel forms.
     */
    public function search(Request $request)
    {
        $type = $request->get('type');
        $q    = $request->get('q', '');

        $results = Contact::when($type, fn($query) => $query->where('type', $type))
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'address', 'city']);

        return response()->json($results);
    }
}
