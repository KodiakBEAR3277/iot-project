<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sensor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sensor_type');        // 'dht11' or 'pir'
            $table->float('value')->nullable();   // numeric reading (temp, humidity)
            $table->boolean('triggered')->nullable(); // for PIR: true/false
            $table->string('unit')->nullable();   // 'C', '%', null
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sensor_logs');
    }
};