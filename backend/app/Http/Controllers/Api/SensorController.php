<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorLog;
use Illuminate\Http\Request;

class SensorController extends Controller {
    // ESP32 POSTs sensor readings here
    public function store(Request $request) {
        $validated = $request->validate([
            'sensor_type' => 'required|string|in:dht11_temp,dht11_humidity,pir',
            'value'       => 'nullable|numeric',
            'triggered'   => 'nullable|boolean',
            'unit'        => 'nullable|string',
        ]);

        $log = SensorLog::create($validated);

        return response()->json(['status' => 'ok', 'id' => $log->id], 201);
    }

    // Dashboard fetches latest readings
    public function latest() {
        $dht11 = SensorLog::where('sensor_type', 'dht11')
                    ->latest()->limit(10)->get();
        $pir   = SensorLog::where('sensor_type', 'pir')
                    ->latest()->limit(10)->get();

        return response()->json(['dht11' => $dht11, 'pir' => $pir]);
    }
}