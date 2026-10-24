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
        Schema::table('transports', function (Blueprint $table) {
            $table->string('brand')->nullable();
            $table->integer('model_year')->nullable();
            $table->decimal('mileage', 12, 2)->default(0);
            $table->date('last_oil_change')->nullable();
            $table->string('capacity')->nullable();
            $table->string('tire_condition')->nullable();
            $table->integer('fuel_level')->default(100);
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->string('cargo_type')->nullable();
            $table->dateTime('estimated_arrival')->nullable();
            $table->decimal('total_distance', 10, 2)->nullable();
            $table->string('weather')->nullable();
            $table->string('traffic_status')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model_year', 'mileage', 'last_oil_change', 'capacity', 'tire_condition', 'fuel_level']);
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['phone', 'license_number', 'date_of_birth', 'address', 'emergency_contact']);
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn(['cargo_type', 'estimated_arrival', 'total_distance', 'weather', 'traffic_status', 'notes']);
        });
    }
};
