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
            $rows        = $sheet->toArray(null, true, true, false);
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
