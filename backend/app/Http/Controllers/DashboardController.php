<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use App\Models\ActuatorState;
use App\Models\Setting;
use Illuminate\Http\Request;

class DashboardController extends Controller {
    public function index() {
        $latestTemp   = SensorLog::where('sensor_type', 'dht11_temp')->latest()->first();
        $latestMotion = SensorLog::where('sensor_type', 'pir')->latest()->first();
        $recentLogs   = SensorLog::latest()->limit(20)->get();

        $threshold = (float) Setting::getValue('temp_threshold', 35);
        $armed     = Setting::getValue('system_armed', 'false') === 'true';

        $tempHigh      = $latestTemp   && (float)$latestTemp->value >= $threshold;
        $motionPresent = $latestMotion && $latestMotion->triggered;
        $buzzerRecord  = ActuatorState::where('actuator', 'buzzer')->first();
        $buzzerActive  = $buzzerRecord ? (bool)$buzzerRecord->state : false;
        $ledRecord     = ActuatorState::where('actuator', 'led')->first();      // ← add
        $ledActive     = $ledRecord ? (bool)$ledRecord->state : false;          // ← add

        return view('dashboard', compact(
            'latestTemp', 'latestMotion', 'recentLogs',
            'threshold', 'armed', 'tempHigh', 'motionPresent',
            'buzzerActive', 'ledActive'                                         // ← add ledActive
        ));
    }

    public function live() {
        $latestTemp     = SensorLog::where('sensor_type', 'dht11_temp')->latest()->first();
        $latestHumidity = SensorLog::where('sensor_type', 'dht11_humidity')->latest()->first();
        $latestMotion   = SensorLog::where('sensor_type', 'pir')->latest()->first();
        $threshold      = (float) Setting::getValue('temp_threshold', 35);
        $armed          = Setting::getValue('system_armed', 'false') === 'true';
        $tempHigh       = $latestTemp && (float)$latestTemp->value >= $threshold;
        $motionPresent  = $latestMotion && $latestMotion->triggered;
        $buzzerActive   = $armed && $tempHigh && $motionPresent;
        $ledRecord  = ActuatorState::where('actuator', 'led')->first();
        $ledActive  = $ledRecord ? (bool)$ledRecord->state : false;

        return response()->json([
            'temp'      => $latestTemp     ? round($latestTemp->value, 1)     : null,
            'humidity'  => $latestHumidity ? round($latestHumidity->value)     : null,
            'motion'    => $motionPresent,
            'armed'     => $armed,
            'threshold' => $threshold,
            'temp_high' => $tempHigh,
            'buzzer'    => $buzzerActive,
            'motion_ago'=> $latestMotion   ? $latestMotion->updated_at->diffForHumans() : 'No data',
            'led' => $ledActive,
        ]);
}
    public function toggleActuator(Request $request) {
        $validated = $request->validate([
            'actuator' => 'required|in:buzzer,led',
            'state'    => 'required|in:on,off,auto',
        ]);

        $key = 'manual_' . $validated['actuator'];
        Setting::setValue($key, $validated['state']);

        return back()->with('success', ucfirst($validated['actuator']) . ' set to ' . $validated['state']);
    }
}