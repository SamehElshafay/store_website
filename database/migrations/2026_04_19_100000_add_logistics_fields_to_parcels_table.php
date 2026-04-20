<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds comprehensive logistics fields to the parcels table.
     */
    public function up(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            // Barcode fields
            $table->string('barcode_collection')->nullable()->after('barcode_out'); // باركود التحصيل

            // Sender info (can be linked to contacts table or free text)
            $table->string('sender_name')->nullable()->after('barcode_collection');       // إسم المرسل
            $table->unsignedBigInteger('sender_contact_id')->nullable()->after('sender_name'); // FK to contacts

            // Recipient info
            $table->string('recipient_name')->nullable()->after('sender_contact_id');    // إسم المستقبل
            $table->string('recipient_phone')->nullable()->after('recipient_name');      // هاتف المستقبل
            $table->text('recipient_address')->nullable()->after('recipient_phone');     // عنوان المستقبل
            $table->unsignedBigInteger('recipient_contact_id')->nullable()->after('recipient_address'); // FK to contacts

            // Financial fields
            $table->decimal('delivery_price', 10, 2)->default(0)->after('recipient_contact_id');   // سعر التوصيل
            $table->decimal('collection_amount', 10, 2)->default(0)->after('delivery_price');       // التحصيل (COD)
            $table->decimal('net_collection', 10, 2)->default(0)->after('collection_amount');       // صافي التحصيل

            // Invoice & billing
            $table->string('invoice_number')->nullable()->after('net_collection');                  // رقم الفاتورة
            $table->enum('collection_method', ['cash', 'card', 'transfer', 'none'])->default('none')->after('invoice_number'); // طريقة التحصيل
            $table->string('collection_statement_barcode')->nullable()->after('collection_method'); // باركود كشف التحصيل

            // Service info
            $table->string('service_type')->nullable()->after('collection_statement_barcode'); // نوع الخدمة

            // Dates
            $table->date('booking_date')->nullable()->after('service_type');   // تاريخ الحجز
            $table->date('delivery_date')->nullable()->after('booking_date');  // تاريخ التوصيل
        });

        // Add FK constraints after both tables exist
        Schema::table('parcels', function (Blueprint $table) {
            $table->foreign('sender_contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('recipient_contact_id')->references('id')->on('contacts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcels', function (Blueprint $table) {
            $table->dropForeign(['sender_contact_id']);
            $table->dropForeign(['recipient_contact_id']);

            $table->dropColumn([
                'barcode_collection',
                'sender_name',
                'sender_contact_id',
                'recipient_name',
                'recipient_phone',
                'recipient_address',
                'recipient_contact_id',
                'delivery_price',
                'collection_amount',
                'net_collection',
                'invoice_number',
                'collection_method',
                'collection_statement_barcode',
                'service_type',
                'booking_date',
                'delivery_date',
            ]);
        });
    }
};
