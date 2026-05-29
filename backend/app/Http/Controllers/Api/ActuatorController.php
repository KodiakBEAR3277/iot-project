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
        $armed     = Setting::getValue('system_armed', 'false') === 'true';
        $threshold = (float) Setting::getValue('temp_threshold', 35);
        $manualBuzzer = Setting::getValue('manual_buzzer', 'auto');
        $manualLed    = Setting::getValue('manual_led',    'auto');

        $latestTemp   = SensorLog::where('sensor_type', 'dht11_temp')->latest()->first();
        $latestMotion = SensorLog::where('sensor_type', 'pir')->latest()->first();

        $tempHigh      = $latestTemp   && (float)$latestTemp->value >= $threshold;
        $motionPresent = $latestMotion && $latestMotion->triggered;
        $alertActive   = $armed && $tempHigh && $motionPresent;

        // Check for manual overrides
        $buzzerOverride = ActuatorState::where('actuator', 'buzzer')
                            ->where('manual', true)->first();
        $ledOverride    = ActuatorState::where('actuator', 'led')
                            ->where('manual', true)->first();

        $buzzerManual = $buzzerOverride ? (bool)$buzzerOverride->state : false;
        $ledManual    = $ledOverride    ? (bool)$ledOverride->state    : false;

        // Alert takes priority, manual override only applies when no alert
        // 'on' = forced on, 'off' = forced off, 'auto' = follow alert
        $buzzerOn = match($manualBuzzer) {
            'on'  => true,
            'off' => false,
            default => $alertActive,
        };
        $ledOn = match($manualLed) {
            'on'  => true,
            'off' => false,
            default => $alertActive,
        };

        $oledMessage = implode(' | ', array_filter([
            $latestTemp   ? round($latestTemp->value, 1) . 'C' : null,
            $latestMotion ? ($latestMotion->triggered ? 'MOTION' : 'CLEAR') : null,
            $armed        ? 'ARMED' : 'DISARMED',
        ]));

        // Write auto state (manual=false)
        ActuatorState::updateOrCreate(
            ['actuator' => 'buzzer', 'manual' => false],
            ['state' => (int)$buzzerOn, 'message' => $buzzerOn ? 'ALERT' : 'SILENT']
        );
        ActuatorState::updateOrCreate(
            ['actuator' => 'led', 'manual' => false],
            ['state' => (int)$ledOn, 'message' => $ledOn ? 'ON' : 'OFF']
        );
        ActuatorState::updateOrCreate(
            ['actuator' => 'oled'],
            ['state' => 1, 'message' => $oledMessage]
        );

        return response()->json([
            'buzzer' => ['state' => $buzzerOn],
            'led'    => ['state' => $ledOn],
            'oled'   => ['state' => true, 'message' => $oledMessage],
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
            'actuator' => 'required|string|in:buzzer,oled,led',
            'state'    => 'required|boolean',
            'message'  => 'nullable|string|max:64',
        ]);

        // Instead of create(), use updateOrCreate so your UI and index() pull from the same records!
        $state = ActuatorState::updateOrCreate(
            ['actuator' => $validated['actuator'], 'manual' => true],
            [
                'state'   => (int)$validated['state'],
                'message' => $validated['state'] ? 'MANUAL_ON' : 'MANUAL_OFF',
            ]
        );

        return response()->json(['status' => 'ok', 'id' => $state->id], 200);
    }
}