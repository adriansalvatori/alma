<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alma_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 255);
            // Lowercased from_path: the lookup key. Unique so one path has one destination.
            $table->string('from_key', 255)->unique();
            $table->string('to_url', 2048);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alma_redirects');
    }
};
