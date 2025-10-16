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
        Schema::create('complaint_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('reply_message');
            $table->enum('sender_role', ['customer', 'client', 'admin']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_conversations');
    }
};
