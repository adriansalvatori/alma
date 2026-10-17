<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alma_leads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->default('');
            $table->string('email');
            $table->string('phone', 64)->nullable();
            $table->string('company')->nullable();
            $table->text('message')->nullable();
            // Extra fields a project adds to a form, without a migration per field.
            $table->json('fields')->nullable();
            // Which form or campaign the lead came from ("contact", "pricing-demo"...).
            $table->string('form_key', 100)->default('default');
            $table->boolean('consent')->default(false);
            $table->timestamp('consented_at')->nullable();
            // First and last marketing touch: utm_*, click ids, referrer, landing page.
            $table->json('attribution')->nullable();
            // A salted hash, never the raw address: enough to spot abuse, not to identify a person.
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();

            $table->index('email');
            $table->index('created_at');
            $table->index('status');
            $table->index('form_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alma_leads');
    }
};
