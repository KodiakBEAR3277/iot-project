<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder {
    public function run(): void {
        Setting::upsert([
            ['key' => 'temp_threshold',  'value' => '35'],
            ['key' => 'system_armed',    'value' => 'false'],
            ['key' => 'manual_buzzer',   'value' => 'auto'],  // 'auto', 'on', 'off'
            ['key' => 'manual_led',      'value' => 'auto'],
        ], ['key'], ['value']);
    }
}