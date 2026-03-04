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
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('court')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->string('probability')->nullable();
            $table->string('risk_level')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};
