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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->string('pppoe_username');
            $table->string('pppoe_password');
            $table->string('ont_sn')->nullable();
            $table->string('service_package')->nullable();
            $table->foreignId('odp_id')->constrained('odps')->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('route_type', ['point-to-point', 'ikut-jalan', 'manual'])->default('point-to-point');
            $table->json('waypoints')->nullable(); // For manual route waypoints
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
