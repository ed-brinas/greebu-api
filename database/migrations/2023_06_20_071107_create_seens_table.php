<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('message_id')->index()->constrained(table: 'messages', column: 'id');
            $table->timestamp('seen_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seens');
    }
};
