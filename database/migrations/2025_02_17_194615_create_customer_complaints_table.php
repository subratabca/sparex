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
        Schema::create('customer_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('sender_role', ['customer', 'client', 'admin']);
            $table->enum('status', ['pending', 'under_review', 'solved', 'further_investigation'])->default('pending');
            $table->text('message');
            $table->date('cmp_date')->nullable();
            $table->time('cmp_time')->nullable();
            $table->date('customer_cmp_date')->nullable();
            $table->time('customer_cmp_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_complaints');
    }
};
