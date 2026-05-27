<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorState;
use App\Models\SensorLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class ActuatorController extends Controller {
    public function index() {
        $armed     = Setting::getValue('system_armed', 'false') === 'true';
        $threshold = (float) Setting::getValue('temp_threshold', 35);

        // Latest readings
        $latestTemp   = SensorLog::where('sensor_type', 'dht11_temp')->latest()->first();
        $latestMotion = SensorLog::where('sensor_type', 'pir')->latest()->first();

        $tempHigh      = $latestTemp   && (float)$latestTemp->value >= $threshold;
        $motionPresent = $latestMotion && $latestMotion->triggered;

        // Buzzer fires only when ALL three conditions are true
        $buzzerOn = $armed && $tempHigh && $motionPresent;

        // OLED always gets current status to display
        $oledMessage = implode(' | ', array_filter([
            $latestTemp   ? round($latestTemp->value, 1) . 'C' : null,
            $latestMotion ? ($latestMotion->triggered ? 'MOTION' : 'CLEAR') : null,
            $armed        ? 'ARMED' : 'DISARMED',
        ]));

        return response()->json([
            'buzzer' => ['state' => $buzzerOn],
            'oled'   => ['state' => true, 'message' => $oledMessage],
            'system' => [
                'armed'     => $armed,
                'threshold' => $threshold,
                'temp_high' => $tempHigh,
                'motion'    => $motionPresent,
            ],
        ]);
    }

    // Keep this for manual overrides from the dashboard if needed
    public function store(Request $request) {
        $validated = $request->validate([
            'actuator' => 'required|string|in:buzzer,oled',
            'state'    => 'required|boolean',
            'message'  => 'nullable|string|max:64',
        ]);

        $state = ActuatorState::create($validated);
        return response()->json(['status' => 'ok', 'id' => $state->id], 201);
    }
}