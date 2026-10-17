<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every ability call an agent makes: who, what, how it went. Shown in Settings → AI Access.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alma_ai_tool_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ability', 191);
            $table->string('outcome', 32);
            $table->text('input_summary')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['ability', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alma_ai_tool_calls');
    }
};
