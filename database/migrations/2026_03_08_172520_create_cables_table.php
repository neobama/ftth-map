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
        Schema::create('cables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('from_type'); // 'router' or 'odp'
            $table->unsignedBigInteger('from_id');
            $table->string('to_type'); // 'router' or 'odp'
            $table->unsignedBigInteger('to_id');
            $table->enum('route_type', ['point-to-point', 'ikut-jalan', 'manual'])->default('point-to-point');
            $table->json('waypoints')->nullable(); // For manual route waypoints
            $table->integer('core_count');
            $table->decimal('length', 10, 2)->nullable(); // in meters
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cables');
    }
};
