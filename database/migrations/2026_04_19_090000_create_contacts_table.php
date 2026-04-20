<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a contacts table for senders and recipients.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // Contact type: sender or recipient
            $table->enum('type', ['sender', 'recipient'])->default('sender'); // نوع: مرسل أو مستقبل

            // Personal info
            $table->string('name');                            // الاسم
            $table->string('phone')->nullable();              // الهاتف
            $table->string('phone_alt')->nullable();          // هاتف بديل
            $table->string('email')->nullable();              // البريد الإلكتروني

            // Address info
            $table->string('address')->nullable();            // العنوان
            $table->string('city')->nullable();               // المدينة
            $table->string('region')->nullable();             // المنطقة / المحافظة
            $table->string('postal_code')->nullable();        // الرمز البريدي

            // Business info (optional)
            $table->string('company_name')->nullable();       // اسم الشركة
            $table->string('tax_number')->nullable();         // الرقم الضريبي

            // Notes
            $table->text('notes')->nullable();                // ملاحظات

            // Linked to system user
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
