<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alma_integration_calls', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20);
            $table->string('method', 10);
            $table->string('host', 255)->index();
            $table->string('url', 2048);
            $table->unsignedSmallInteger('status')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('request_headers')->nullable();
            $table->mediumText('request_body')->nullable();
            $table->mediumText('response_body')->nullable();
            $table->string('error', 1000)->nullable();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alma_integration_calls');
    }
};
