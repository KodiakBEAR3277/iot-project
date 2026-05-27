<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller {
    public function update(Request $request) {
        $request->validate([
            'temp_threshold' => 'required|numeric|min:20|max:60',
            'system_armed'   => 'sometimes|boolean',
        ]);

        Setting::setValue('temp_threshold', $request->temp_threshold);
        Setting::setValue('system_armed', $request->has('system_armed') ? 'true' : 'false');

        return back()->with('success', 'Settings saved.');
    }
}