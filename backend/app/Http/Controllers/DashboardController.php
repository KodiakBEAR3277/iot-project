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

        $tempHigh     = $latestTemp   && (float)$latestTemp->value >= $threshold;
        $motionPresent= $latestMotion && $latestMotion->triggered;
        $buzzerActive = $armed && $tempHigh && $motionPresent;

        return view('dashboard', compact(
            'latestTemp', 'latestMotion', 'recentLogs',
            'threshold', 'armed', 'tempHigh', 'motionPresent', 'buzzerActive'
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

        return response()->json([
            'temp'      => $latestTemp     ? round($latestTemp->value, 1)     : null,
            'humidity'  => $latestHumidity ? round($latestHumidity->value)     : null,
            'motion'    => $motionPresent,
            'armed'     => $armed,
            'threshold' => $threshold,
            'temp_high' => $tempHigh,
            'buzzer'    => $buzzerActive,
            'motion_ago'=> $latestMotion   ? $latestMotion->updated_at->diffForHumans() : 'No data',
        ]);
}
    public function toggleActuator(Request $request) {
        $validated = $request->validate([
            'actuator' => 'required|in:buzzer,oled',
            'state'    => 'required|boolean',
            'message'  => 'nullable|string|max:64',
        ]);

        ActuatorState::create($validated);
        return back()->with('success', ucfirst($validated['actuator']) . ' updated.');
    }
}