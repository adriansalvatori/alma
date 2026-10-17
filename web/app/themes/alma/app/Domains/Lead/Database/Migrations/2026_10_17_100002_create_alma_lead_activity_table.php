<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The lead audit log. Each LeadCaptured event writes a "dispatched" row, and each listener writes
 * its own outcome row, so a listener that failed silently shows up as a dispatch with no matching
 * outcome instead of a lead that just never reached the CRM.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alma_lead_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('event', 100);
            $table->string('actor', 100);
            $table->string('outcome', 20);
            $table->json('detail')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alma_lead_activity');
    }
};
