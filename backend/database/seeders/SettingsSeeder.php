<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder {
    public function run(): void {
        Setting::upsert([
            ['key' => 'temp_threshold', 'value' => '35'],   // °C
            ['key' => 'system_armed',   'value' => 'false'],
        ], ['key'], ['value']);
    }
}