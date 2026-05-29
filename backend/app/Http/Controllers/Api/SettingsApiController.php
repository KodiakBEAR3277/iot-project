<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsApiController extends Controller {
    public function index() {
        return response()->json([
            'temp_threshold' => (float) Setting::getValue('temp_threshold', 35),
            'system_armed'   => Setting::getValue('system_armed', 'false') === 'true',
            'manual_buzzer'  => Setting::getValue('manual_buzzer', 'auto'),
            'manual_led'     => Setting::getValue('manual_led',    'auto'),
        ]);
    }

    public function update(Request $request) {
        $validated = $request->validate([
            'temp_threshold' => 'sometimes|numeric|min:20|max:60',
            'system_armed'   => 'sometimes|boolean',
            'manual_buzzer'  => 'sometimes|in:on,off,auto',
            'manual_led'     => 'sometimes|in:on,off,auto',
        ]);

        if (isset($validated['temp_threshold']))
            Setting::setValue('temp_threshold', $validated['temp_threshold']);
        if (isset($validated['system_armed']))
            Setting::setValue('system_armed', $validated['system_armed'] ? 'true' : 'false');
        if (isset($validated['manual_buzzer']))
            Setting::setValue('manual_buzzer', $validated['manual_buzzer']);
        if (isset($validated['manual_led']))
            Setting::setValue('manual_led', $validated['manual_led']);

        return response()->json(['status' => 'ok']);
    }
}