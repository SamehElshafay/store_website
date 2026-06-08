<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Contact;
use App\Models\Parcel;
use App\Models\ParcelStatus;
use Carbon\Carbon;

class ExcelImportController extends Controller
{
    /**
     * Show the import page (preview before committing).
     * GET /parcels-import/preview
     */
    public function preview(Request $request, $statusId)
    {
        $status = ParcelStatus::findOrFail($statusId);
        return view('parcels.import', compact('status'));
    }

    /**
     * Parse uploaded Excel and return a preview JSON.
     * POST /parcels-import/parse
     */
    public function parse(Request $request)
    {
        $request->validate([
            'file'      => 'required|file', // More lenient validation
            'status_id' => 'required|exists:parcel_statuses,id',
        ]);

        \Log::info('Excel Parse Started', [
            'status_id' => $request->status_id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'mime_type' => $request->file('file')->getMimeType()
        ]);

        $status = ParcelStatus::findOrFail($request->status_id);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, false, true, false);
        } catch (\Exception $e) {
            \Log::error('Excel Read Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => __('Failed to read file: ') . $e->getMessage(),
            ], 422);
        }

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => __('The file is empty or has no data rows.'),
            ], 422);
        }

        // Map Arabic headers to indices
        $headers = array_map('trim', $rows[0]);
        $map     = array_flip($headers);

        $col = [
            'barcode'         => $map['باركود الشحنة']        ?? null,
            'recipient_name'  => $map['اسم المستلم']          ?? null,
            'recipient_phone' => $map['هاتف المستلم']         ?? null,
            'city'            => $map['المدينة']               ?? null,
            'region'          => $map['الحي']                  ?? null,
            'address'         => $map['الشارع']                ?? null,
            'price'           => $map['السعر']                 ?? null,
            'collection'      => $map['التحصيل']               ?? null,
            'invoice_number'  => $map['رقم الإرسالية']         ?? null,
            'notes'           => $map['الملاحظات']             ?? null,
            'special_notes'   => $map['ملاحظات خاصة']          ?? null,
            'payment_method'  => $map['طريقة الدفع']           ?? null,
            'content'         => $map['محتوى الطرد']           ?? null,
            'sender_name'     => $map['إسم المرسل']            ?? null,
            'sender_phone'    => $map['هاتف المرسل']           ?? null,
            'create_date'     => $map['تاريخ الانشاء']         ?? null,
            'delivery_date'   => $map['تاريخ التوصيل']         ?? null,
            'expected_date'   => $map['تاريخ التوصيل المتوقع'] ?? null,
        ];

        $preview   = [];
        $isDispatch = $status->modal_type === 'dispatch';

        foreach (array_slice($rows, 1) as $row) {
            $barcode = trim($row[$col['barcode']] ?? '');
            if (empty($barcode)) continue;

            $senderName  = trim($row[$col['sender_name']]  ?? '');
            $senderPhone = $this->normalizePhone(trim($row[$col['sender_phone']] ?? ''));
            $recipName   = trim($row[$col['recipient_name']]  ?? '');
            $recipPhone  = $this->normalizePhone(trim($row[$col['recipient_phone']] ?? ''));
            $notes       = trim(implode(' | ', array_filter([
                $row[$col['notes']]        ?? '',
                $row[$col['special_notes']] ?? '',
            ])));

            // Check if barcode already in DB
            $existing = Parcel::where('barcode_in', $barcode)
                ->orWhere('barcode_out', $barcode)
                ->first();

            // Check if sender/recipient contacts already exist
            $senderContact    = $senderPhone  ? Contact::where('phone', $senderPhone)->first()  : null;
            $recipientContact = $recipPhone   ? Contact::where('phone', $recipPhone)->first()   : null;

            // Parse collection amount (remove commas and non-numeric)
            $collectionAmount = (float) str_replace(',', '', $row[$col['collection']] ?? 0);
            $deliveryPrice    = (float) str_replace(',', '', $row[$col['price']]      ?? 0);

            $paymentMethod = $this->mapPaymentMethod($row[$col['payment_method']] ?? '');

            $preview[] = [
                'barcode'             => $barcode,
                'sender_name'         => $senderName,
                'sender_phone'        => $senderPhone,
                'sender_exists'       => $senderContact ? true : false,
                'sender_contact_id'   => $senderContact?->id,
                'recipient_name'      => $recipName,
                'recipient_phone'     => $recipPhone,
                'recipient_city'      => trim($row[$col['city']] ?? ''),
                'recipient_region'    => trim($row[$col['region']] ?? ''),
                'recipient_address'   => trim($row[$col['address']] ?? ''),
                'recipient_exists'    => $recipientContact ? true : false,
                'recipient_contact_id'=> $recipientContact?->id,
                'title'               => trim($row[$col['content']] ?? ''),
                'delivery_price'      => $deliveryPrice,
                'collection_amount'   => $collectionAmount,
                'net_collection'      => $collectionAmount - $deliveryPrice,
                'invoice_number'      => trim($row[$col['invoice_number']] ?? ''),
                'collection_method'   => $paymentMethod,
                'notes'               => $notes,
                'booking_date'        => $this->parseDate($row[$col['create_date']] ?? ''),
                'delivery_date'       => $isDispatch ? $this->parseDate($row[$col['delivery_date']] ?? '') : null,
                'already_exists'      => $existing ? true : false,
                'existing_id'         => $existing?->id,
            ];
        }

        return response()->json([
            'success'    => true,
            'status'     => ['id' => $status->id, 'name' => $status->display_name, 'modal_type' => $status->modal_type],
            'is_dispatch'=> $isDispatch,
            'rows'       => $preview,
            'total'      => count($preview),
            'message'    => __('File parsed successfully. Review and confirm import.'),
        ]);
    }

    /**
     * Commit the import.
     * POST /parcels-import/commit
     */
    public function commit(Request $request)
    {
        $request->validate([
            'status_id' => 'required|exists:parcel_statuses,id',
            'rows'      => 'required|array|min:1',
        ]);

        $status     = ParcelStatus::findOrFail($request->status_id);
        $isDispatch = $status->modal_type === 'dispatch';
        $userId     = Auth::id();
        $defaultStatus = ParcelStatus::where('is_default', true)->first();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];

        DB::beginTransaction();
        try {
            foreach ($request->rows as $row) {
                $barcode = trim($row['barcode'] ?? '');
                if (empty($barcode)) { $skipped++; continue; }

                try {
                    // 1. Resolve / create sender contact
                    $senderContact = $this->resolveContact(
                        $row['sender_name']  ?? '',
                        $row['sender_phone'] ?? '',
                        $row['recipient_city'] ?? '',
                        'sender',
                        $userId
                    );

                    // 2. Resolve / create recipient contact
                    $recipientContact = $this->resolveContact(
                        $row['recipient_name']    ?? '',
                        $row['recipient_phone']   ?? '',
                        $row['recipient_city']    ?? '',
                        'recipient',
                        $userId,
                        $row['recipient_address'] ?? '',
                        $row['recipient_region']  ?? '',
                    );

                    // 3. Common parcel data
                    $data = [
                        'title'               => $row['title']             ?? null,
                        'barcode_in'          => $barcode,
                        'sender_contact_id'   => $senderContact?->id,
                        'recipient_contact_id'=> $recipientContact?->id,
                        'delivery_price'      => $row['delivery_price']    ?? 0,
                        'collection_amount'   => $row['collection_amount'] ?? 0,
                        'net_collection'      => ($row['collection_amount'] ?? 0) - ($row['delivery_price'] ?? 0),
                        'invoice_number'      => $row['invoice_number']    ?? null,
                        'collection_method'   => $row['collection_method'] ?? null,
                        'notes'               => $row['notes']             ?? null,
                        'booking_date'        => $row['booking_date']      ?? null,
                    ];

                    // 4. Find existing parcel by barcode
                    $parcel = Parcel::where('barcode_in', $barcode)
                        ->orWhere('barcode_out', $barcode)
                        ->first();

                    if ($isDispatch) {
                        // Dispatch mode: update status + delivery info
                        $data['status']       = $status->key;
                        $data['status_id']    = $status->id;
                        $data['barcode_out']  = $barcode;
                        $data['delivered_at'] = Carbon::now();
                        $data['delivered_by'] = $userId;
                        if (!empty($row['delivery_date'])) {
                            $data['delivery_date'] = $row['delivery_date'];
                        }

                        if ($parcel) {
                            $parcel->update($data);
                            $updated++;
                        } else {
                            // Create + mark as dispatched
                            $data['received_by'] = $userId;
                            $data['received_at'] = Carbon::now();
                            $data['status']      = $status->key;
                            $data['status_id']   = $status->id;
                            Parcel::create($data);
                            $created++;
                        }
                    } else {
                        // Receive mode: create or update with default status
                        $data['status']      = $defaultStatus?->key ?? 'ready';
                        $data['status_id']   = $defaultStatus?->id  ?? null;
                        $data['received_by'] = $userId;
                        $data['received_at'] = Carbon::now();

                        if ($parcel) {
                            $parcel->update($data);
                            $updated++;
                        } else {
                            Parcel::create($data);
                            $created++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "$barcode: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('Import failed: ') . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Import complete: :c created, :u updated, :s skipped.', [
                'c' => $created,
                'u' => $updated,
                's' => $skipped,
            ]),
            'data' => [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors'  => $errors,
            ],
        ]);
    }

    /**
     * Show Master Import Page
     */
    public function masterPreview()
    {
        return view('settings.master_import');
    }

    /**
     * Parse Master Excel (Dynamic Status)
     */
    public function parseMaster(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, false, true, false);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('Failed to read file: ') . $e->getMessage()], 422);
        }

        if (empty($rows) || count($rows) < 2) {
            return response()->json(['success' => false, 'message' => __('The file is empty.')], 422);
        }

        $headers = array_map('trim', $rows[0]);
        $map     = array_flip($headers);

        $col = [
            'barcode'         => $map['باركود الشحنة']        ?? null,
            'recipient_name'  => $map['اسم المستلم']          ?? null,
            'recipient_phone' => $map['هاتف المستلم']         ?? null,
            'city'            => $map['المدينة']               ?? null,
            'region'          => $map['الحي']                  ?? null,
            'address'         => $map['الشارع']                ?? null,
            'price'           => $map['السعر']                 ?? null,
            'collection'      => $map['التحصيل']               ?? null,
            'invoice_number'  => $map['رقم الإرسالية']         ?? null,
            'notes'           => $map['الملاحظات']             ?? null,
            'special_notes'   => $map['ملاحظات خاصة']          ?? null,
            'payment_method'  => $map['طريقة الدفع']           ?? null,
            'status'          => $map['الحالة']                ?? null,
            'content'         => $map['محتوى الطرد']           ?? null,
            'sender_name'     => $map['إسم المرسل']            ?? null,
            'sender_phone'    => $map['هاتف المرسل']           ?? null,
            'create_date'     => $map['تاريخ الانشاء']         ?? null,
            'delivery_date'   => $map['تاريخ التوصيل']         ?? null,
        ];

        $preview = [];
        foreach (array_slice($rows, 1) as $row) {
            $barcode = trim($row[$col['barcode']] ?? '');
            if (empty($barcode)) continue;

            $statusName = trim($row[$col['status']] ?? 'Received');
            $existing = Parcel::where('barcode_in', $barcode)->orWhere('barcode_out', $barcode)->first();
            
            $preview[] = [
                'barcode'             => $barcode,
                'sender_name'         => trim($row[$col['sender_name']]  ?? ''),
                'sender_phone'        => $this->normalizePhone(trim($row[$col['sender_phone']] ?? '')),
                'recipient_name'      => trim($row[$col['recipient_name']]  ?? ''),
                'recipient_phone'     => $this->normalizePhone(trim($row[$col['recipient_phone']] ?? '')),
                'recipient_city'      => trim($row[$col['city']] ?? ''),
                'recipient_region'    => trim($row[$col['region']] ?? ''),
                'recipient_address'   => trim($row[$col['address']] ?? ''),
                'title'               => trim($row[$col['content']] ?? ''),
                'delivery_price'      => (float) str_replace(',', '', $row[$col['price']] ?? 0),
                'collection_amount'   => (float) str_replace(',', '', $row[$col['collection']] ?? 0),
                'invoice_number'      => trim($row[$col['invoice_number']] ?? ''),
                'collection_method'   => $this->mapPaymentMethod($row[$col['payment_method']] ?? ''),
                'status_name'         => $statusName,
                'notes'               => trim(($row[$col['notes']] ?? '') . ' ' . ($row[$col['special_notes']] ?? '')),
                'booking_date'        => $this->parseDate($row[$col['create_date']] ?? ''),
                'already_exists'      => $existing ? true : false,
            ];
        }

        return response()->json([
            'success' => true,
            'rows'    => $preview,
            'total'   => count($preview),
        ]);
    }

    /**
     * Commit Master Import
     */
    public function commitMaster(Request $request)
    {
        $request->validate(['rows' => 'required|array|min:1']);
        $userId = Auth::id();
        $created = 0; $updated = 0; $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->rows as $row) {
                $barcode = trim($row['barcode'] ?? '');
                if (empty($barcode)) continue;

                // 1. Resolve / Create Status
                $status = $this->resolveStatus($row['status_name'] ?? 'Received');

                // 2. Resolve Contacts
                $senderContact = $this->resolveContact($row['sender_name'] ?? '', $row['sender_phone'] ?? '', '', 'sender', $userId);
                $recipientContact = $this->resolveContact($row['recipient_name'] ?? '', $row['recipient_phone'] ?? '', $row['recipient_city'] ?? '', 'recipient', $userId, $row['recipient_address'] ?? '', $row['recipient_region'] ?? '');

                // 3. Prep Data
                $data = [
                    'title'                => $row['title'] ?? null,
                    'barcode_in'           => $barcode,
                    'status'               => $status->key,
                    'status_id'            => $status->id,
                    'sender_contact_id'    => $senderContact?->id,
                    'recipient_contact_id' => $recipientContact?->id,
                    'delivery_price'       => $row['delivery_price'] ?? 0,
                    'collection_amount'    => $row['collection_amount'] ?? 0,
                    'net_collection'       => ($row['collection_amount'] ?? 0) - ($row['delivery_price'] ?? 0),
                    'invoice_number'       => $row['invoice_number'] ?? null,
                    'collection_method'    => $row['collection_method'] ?? 'cash',
                    'notes'                => $row['notes'] ?? null,
                    'booking_date'         => $row['booking_date'] ?? null,
                ];

                $parcel = Parcel::where('barcode_in', $barcode)->orWhere('barcode_out', $barcode)->first();

                if ($parcel) {
                    $parcel->update($data);
                    $updated++;
                } else {
                    $data['received_by'] = $userId;
                    $data['received_at'] = Carbon::now();
                    Parcel::create($data);
                    $created++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Import complete: :c created, :u updated.', ['c' => $created, 'u' => $updated]),
            'data'    => ['created' => $created, 'updated' => $updated, 'errors' => $errors]
        ]);
    }

    private function resolveStatus(string $name): ParcelStatus
    {
        $name = trim($name);
        $status = ParcelStatus::where('name_ar', $name)
            ->orWhere('name_en', $name)
            ->orWhere('name', $name)
            ->first();

        if ($status) return $status;

        // Create new status
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#71717a'];
        $icons  = ['bi-box-seam', 'bi-truck', 'bi-check-circle', 'bi-exclamation-triangle', 'bi-clock-history', 'bi-geo-alt'];
        
        return ParcelStatus::create([
            'name'       => $name,
            'name_ar'    => $name,
            'name_en'    => $name,
            'key'        => \Illuminate\Support\Str::slug($name),
            'color'      => $colors[array_rand($colors)],
            'icon'       => $icons[array_rand($icons)],
            'modal_type' => 'receive',
            'is_default' => false,
            'sort_order' => ParcelStatus::max('sort_order') + 1
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolveContact(
        string $name,
        string $phone,
        string $city    = '',
        string $type    = 'recipient',
        int    $userId  = 0,
        string $address = '',
        string $region  = '',
    ): ?Contact {
        if (empty($name) && empty($phone)) return null;

        // Find by phone first (unique identifier across types)
        if ($phone) {
            $contact = Contact::where('phone', $phone)->first();
            if ($contact) return $contact;
        }

        // Create new
        return Contact::create([
            'name'       => $name  ?: $phone,
            'phone'      => $phone ?: null,
            'type'       => $type,
            'city'       => $city,
            'address'    => trim("$region $address"),
            'created_by' => $userId,
        ]);
    }

    private function mapPaymentMethod(string $raw): ?string
    {
        $raw = trim($raw);
        if (str_contains($raw, 'نقد') || str_contains($raw, 'كاش')) return 'cash';
        if (str_contains($raw, 'بطاق') || str_contains($raw, 'Card')) return 'card';
        if (str_contains($raw, 'تحويل') || str_contains($raw, 'Transfer')) return 'transfer';
        return 'cash'; // default
    }

    private function parseDate(string $raw): ?string
    {
        if (empty(trim($raw))) return null;
        try {
            // Try common formats
            foreach (['d/m/Y H:i', 'd/m/Y', 'Y-m-d H:i:s', 'Y-m-d'] as $fmt) {
                try {
                    return Carbon::createFromFormat($fmt, trim(explode(' ', $raw)[0] . (str_contains($raw, ':') ? ' ' . explode(' ', $raw)[1] : '')))->format('Y-m-d');
                } catch (\Exception $e) {
                    // try next
                }
            }
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\s+/', '', $phone);
    }
}
