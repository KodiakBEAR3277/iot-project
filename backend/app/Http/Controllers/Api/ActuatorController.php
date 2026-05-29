<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActuatorState;
use App\Models\SensorLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class ActuatorController extends Controller {
    
    /**
     * Dispatch status to the ESP32 (Optimized rapid hardware dispatcher)
     * Target: GET /api/actuators
     */
    public function index() {
        // 1. Fetch system configuration properties
        $armed     = Setting::getValue('system_armed', 'false') === 'true';
        $threshold = (float) Setting::getValue('temp_threshold', 35);

        // 2. Gather latest sensor readings
        $latestTemp   = SensorLog::where('sensor_type', 'dht11_temp')->latest()->first();
        $latestMotion = SensorLog::where('sensor_type', 'pir')->latest()->first();

        $tempHigh      = $latestTemp   && (float)$latestTemp->value >= $threshold;
        $motionPresent = $latestMotion && $latestMotion->triggered;

        // 3. Calculate what the states SHOULD be according to your automation safety logic
        $calculatedBuzzer = $armed && $tempHigh && $motionPresent;

        $oledMessage = implode(' | ', array_filter([
            $latestTemp   ? round($latestTemp->value, 1) . 'C' : null,
            $latestMotion ? ($latestMotion->triggered ? 'MOTION' : 'CLEAR') : null,
            $armed        ? 'ARMED' : 'DISARMED',
        ]));

        // 4. WRITE TO DATABASE (Fulfill Semestral Requirements)
        // We use updateOrCreate so we don't spam infinite rows. We keep 1 row per actuator representing current status.
        $buzzerState = ActuatorState::updateOrCreate(
            ['actuator' => 'buzzer'],
            [
                'state' => (int)$calculatedBuzzer, 
                'message' => $calculatedBuzzer ? 'ALERT' : 'SILENT'
            ]
        );

        $oledState = ActuatorState::updateOrCreate(
            ['actuator' => 'oled'],
            [
                'state' => 1, 
                'message' => $oledMessage
            ]
        );

        // 5. Return JSON cleanly matching your ESP32 payload logic
        return response()->json([
            'buzzer' => ['state' => (bool)$buzzerState->state],
            'oled'   => ['state' => true, 'message' => $oledState->message],
            'system' => [
                'armed'     => $armed,
                'threshold' => $threshold,
                'temp_high' => $tempHigh,
                'motion'    => $motionPresent,
            ],
        ]);
    }

    /**
     * Handle Manual Dashboard Overrides & Mobile Command Toggles
     * Target: POST /api/actuators
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'actuator' => 'required|string|in:buzzer,oled',
            'state'    => 'required|boolean',
            'message'  => 'nullable|string|max:64',
        ]);

        // Instead of create(), use updateOrCreate so your UI and index() pull from the same records!
        $state = ActuatorState::updateOrCreate(
            ['actuator' => $validated['actuator']],
            [
                'state'   => (int)$validated['state'],
                'message' => $validated['message'] ?? ($validated['state'] ? 'MANUAL_ON' : 'MANUAL_OFF')
            ]
        );

        return response()->json(['status' => 'ok', 'id' => $state->id], 200);
    }
}