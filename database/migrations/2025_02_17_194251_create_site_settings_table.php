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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('email', 255)->unique();
            $table->string('phone1', 20);
            $table->string('phone2', 20)->nullable();
            $table->string('logo', 255);
            $table->string('website_name', 50)->nullable();
            $table->string('slogan', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('linkedin', 255)->nullable();
            $table->string('youtube', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('refund')->nullable();
            $table->text('terms')->nullable();
            $table->text('privacy')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
