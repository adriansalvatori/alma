<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alma_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('gateway', 50);
            // The provider's ID for this payment. Null until the checkout session exists.
            $table->string('reference', 150)->nullable();
            $table->string('status', 20)->index();
            $table->bigInteger('amount_minor');
            $table->char('currency', 3);
            $table->string('description')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->json('metadata')->nullable();
            // Soft link to the Lead module (which is optional), so no foreign key.
            $table->uuid('lead_uuid')->nullable()->index();
            $table->text('success_url')->nullable();
            $table->text('cancel_url')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'reference']);
        });

        // One row per provider event ever handled: the unique key is what makes a retried or
        // replayed webhook a no-op.
        Schema::create('alma_payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50);
            $table->string('event_id', 150);
            $table->string('type', 20);
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['gateway', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alma_payment_events');
        Schema::dropIfExists('alma_payments');
    }
};
