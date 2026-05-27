<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('actuator_states', function (Blueprint $table) {
            $table->id();
            $table->string('actuator');           // 'buzzer' or 'oled'
            $table->boolean('state');             // true = on, false = off
            $table->string('message')->nullable(); // optional text for OLED
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('actuator_states');
    }
};