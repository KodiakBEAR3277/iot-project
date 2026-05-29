@extends('layouts.app')
@section('title', 'Dashboard — IoT Safety System')

@section('content')

{{-- ── ALERT BANNER ─────────────────────────────────────────── --}}
<div id="alert-banner" style="display: {{ $buzzerActive ? 'flex' : 'none' }}"
    class="mt-6 bg-red-900/50 border border-red-500 rounded-2xl px-6 py-4 flex items-center gap-4 animate-pulse">
    <span class="text-3xl">🚨</span>
    <div>
        <p class="font-bold text-red-300 text-lg">ALERT: Heat + Occupancy Detected</p>
        <p class="text-red-400 text-sm">Temperature exceeds threshold and motion is present. Buzzer is active.</p>
    </div>
</div>

{{-- ── STATUS CARDS ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">

    {{-- System --}}
    <div id="card-system-wrap" class="bg-gray-900 border {{ $armed ? 'border-teal-600' : 'border-gray-700' }} rounded-2xl p-5 flex flex-col gap-1">
        <span class="text-xs text-gray-500 uppercase tracking-widest">System</span>
        <span id="card-system" class="text-2xl font-bold {{ $armed ? 'text-teal-400' : 'text-gray-500' }}">
            {{ $armed ? 'ARMED' : 'DISARMED' }}
        </span>
        <span id="card-threshold" class="text-xs text-gray-600">Threshold: {{ $threshold }}°C</span>
    </div>

    {{-- Temperature --}}
    <div id="card-temp-wrap" class="bg-gray-900 border {{ $tempHigh ? 'border-red-500' : 'border-gray-700' }} rounded-2xl p-5 flex flex-col gap-1">
        <span class="text-xs text-gray-500 uppercase tracking-widest">Temperature</span>
        <span id="card-temp" class="text-2xl font-bold {{ $tempHigh ? 'text-red-400' : 'text-white' }}">
            {{ $latestTemp ? round($latestTemp->value, 1) . '°C' : 'N/A' }}
        </span>
        <span id="card-temp-sub" class="text-xs text-gray-600">
            {{ $tempHigh ? 'Above threshold' : 'Normal' }}
        </span>
    </div>

    {{-- Humidity --}}
    @php
        $latestHumidity = \App\Models\SensorLog::where('sensor_type','dht11_humidity')->latest()->first();
    @endphp
    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-5 flex flex-col gap-1">
        <span class="text-xs text-gray-500 uppercase tracking-widest">Humidity</span>
        <span id="card-humidity" class="text-2xl font-bold text-white">
            {{ $latestHumidity ? round($latestHumidity->value) . '%' : 'N/A' }}
        </span>
        <span class="text-xs text-gray-600">Relative humidity</span>
    </div>

    {{-- Motion --}}
    <div id="card-motion-wrap" class="bg-gray-900 border {{ $motionPresent ? 'border-yellow-500' : 'border-gray-700' }} rounded-2xl p-5 flex flex-col gap-1">
        <span class="text-xs text-gray-500 uppercase tracking-widest">Motion</span>
        <span id="card-motion" class="text-2xl font-bold {{ $motionPresent ? 'text-yellow-400' : 'text-gray-500' }}">
            {{ $motionPresent ? 'DETECTED' : 'CLEAR' }}
        </span>
        <span id="card-motion-ago" class="text-xs text-gray-600">
            {{ $latestMotion ? $latestMotion->updated_at->diffForHumans() : 'No data yet' }}
        </span>
    </div>

    {{-- Buzzer --}}
    <div id="card-buzzer-wrap" class="bg-gray-900 border {{ $buzzerActive ? 'border-red-500' : 'border-gray-700' }} rounded-2xl p-5 flex flex-col gap-1">
        <span class="text-xs text-gray-500 uppercase tracking-widest">Buzzer</span>
        <span id="card-buzzer" class="text-2xl font-bold {{ $buzzerActive ? 'text-red-400' : 'text-gray-500' }}">
            {{ $buzzerActive ? 'ACTIVE' : 'SILENT' }}
        </span>
        <span class="text-xs text-gray-600">Auto-controlled</span>
    </div>

    {{-- LED --}}
    <div id="card-led-wrap" class="bg-gray-900 border {{ $ledActive ? 'border-teal-500' : 'border-gray-700' }} rounded-2xl p-5 flex flex-col gap-1">
        <span class="text-xs text-gray-500 uppercase tracking-widest">LED</span>
        <span id="card-led" class="text-2xl font-bold {{ $ledActive ? 'text-teal-400' : 'text-gray-500' }}">
            {{ $ledActive ? 'ON' : 'OFF' }}
        </span>
        <span class="text-xs text-gray-600">Alert + manual</span>
    </div>
</div>

{{-- ── SETTINGS FORM ────────────────────────────────────────── --}}
<div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-6">
    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">System Settings</h2>

    <form method="POST" action="{{ route('settings.update') }}" class="flex flex-wrap items-end gap-4">
        @csrf

        <div>
            <label class="block text-sm text-gray-400 mb-1">Temp Threshold (°C)</label>
            <input
                type="number" name="temp_threshold"
                value="{{ old('temp_threshold', $threshold) }}"
                min="20" max="60" step="0.5"
                class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-sm text-white w-36 focus:outline-none focus:border-teal-500 transition"
            />
        </div>

        <div class="flex items-center gap-3 pb-2">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="system_armed" value="1" class="sr-only peer"
                    {{ $armed ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-teal-600
                            after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                            after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                            peer-checked:after:translate-x-full"></div>
            </label>
            <span class="text-sm text-gray-300">Arm System</span>
        </div>

        <button type="submit"
            class="bg-teal-600 hover:bg-teal-500 text-white text-sm font-semibold px-5 py-2 rounded-lg transition pb-2">
            Save Settings
        </button>

    </form>

    <div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">Manual Overrides</h2>
        <div class="flex flex-wrap gap-4">

            {{-- Buzzer --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-300 mr-1">Buzzer</span>
                <form method="POST" action="{{ route('actuator.toggle') }}">
                    @csrf
                    <input type="hidden" name="actuator" value="buzzer">
                    <input type="hidden" name="state" value="on">
                    <button class="bg-red-700 hover:bg-red-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Force ON
                    </button>
                </form>
                <form method="POST" action="{{ route('actuator.toggle') }}">
                    @csrf
                    <input type="hidden" name="actuator" value="buzzer">
                    <input type="hidden" name="state" value="off">
                    <button class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Force OFF
                    </button>
                </form>
                <form method="POST" action="{{ route('actuator.toggle') }}">
                    @csrf
                    <input type="hidden" name="actuator" value="buzzer">
                    <input type="hidden" name="state" value="auto">
                    <button class="bg-teal-800 hover:bg-teal-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Auto
                    </button>
                </form>
            </div>

            {{-- LED --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-300 mr-1">LED</span>
                <form method="POST" action="{{ route('actuator.toggle') }}">
                    @csrf
                    <input type="hidden" name="actuator" value="led">
                    <input type="hidden" name="state" value="on">
                    <button class="bg-teal-700 hover:bg-teal-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Turn ON
                    </button>
                </form>
                <form method="POST" action="{{ route('actuator.toggle') }}">
                    @csrf
                    <input type="hidden" name="actuator" value="led">
                    <input type="hidden" name="state" value="off">
                    <button class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Turn OFF
                    </button>
                </form>
                <form method="POST" action="{{ route('actuator.toggle') }}">
                    @csrf
                    <input type="hidden" name="actuator" value="led">
                    <input type="hidden" name="state" value="auto">
                    <button class="bg-teal-800 hover:bg-teal-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Auto
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
</div>

{{-- ── SENSOR LOG TABLE ─────────────────────────────────────── --}}
<div class="mt-6 bg-gray-900 border border-gray-800 rounded-2xl p-6">
    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-4">Recent Sensor Logs</h2>

    @if($recentLogs->isEmpty())
        <p class="text-gray-600 text-sm">No sensor data yet. Waiting for ESP32...</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-500 border-b border-gray-800">
                        <th class="pb-2 pr-6">Sensor</th>
                        <th class="pb-2 pr-6">Value</th>
                        <th class="pb-2 pr-6">Unit</th>
                        <th class="pb-2 pr-6">Triggered</th>
                        <th class="pb-2">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($recentLogs as $log)
                    <tr class="hover:bg-gray-800/40 transition">
                        <td class="py-2.5 pr-6 font-medium text-teal-400 uppercase text-xs tracking-wide">
                            {{ $log->sensor_type }}
                        </td>
                        <td class="py-2.5 pr-6 text-white">
                            {{ $log->value !== null ? round($log->value, 2) : '—' }}
                        </td>
                        <td class="py-2.5 pr-6 text-gray-500">
                            {{ $log->unit ?? '—' }}
                        </td>
                        <td class="py-2.5 pr-6">
                            @if($log->triggered !== null)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $log->triggered ? 'bg-yellow-900/50 text-yellow-400' : 'bg-gray-800 text-gray-500' }}">
                                    {{ $log->triggered ? 'YES' : 'NO' }}
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="py-2.5 text-gray-500">
                            {{ $log->created_at->format('M d, H:i:s') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>


<script>
async function fetchLive() {
    try {
        const res  = await fetch('/dashboard/live');
        const data = await res.json();

        // Temperature
        document.getElementById('card-temp').textContent =
            data.temp !== null ? data.temp + '°C' : 'N/A';
        document.getElementById('card-temp-sub').textContent =
            data.temp_high ? 'Above threshold' : 'Normal';
        document.getElementById('card-temp-wrap').className =
            'bg-gray-900 border ' + (data.temp_high ? 'border-red-500' : 'border-gray-700') + ' rounded-2xl p-5 flex flex-col gap-1';

        // Humidity
        document.getElementById('card-humidity').textContent =
            data.humidity !== null ? data.humidity + '%' : 'N/A';

        // Motion
        document.getElementById('card-motion').textContent =
            data.motion ? 'DETECTED' : 'CLEAR';
        document.getElementById('card-motion').className =
            'text-2xl font-bold ' + (data.motion ? 'text-yellow-400' : 'text-gray-500');
        document.getElementById('card-motion-ago').textContent = data.motion_ago;
        document.getElementById('card-motion-wrap').className =
            'bg-gray-900 border ' + (data.motion ? 'border-yellow-500' : 'border-gray-700') + ' rounded-2xl p-5 flex flex-col gap-1';

        // System
        document.getElementById('card-system').textContent =
            data.armed ? 'ARMED' : 'DISARMED';
        document.getElementById('card-system').className =
            'text-2xl font-bold ' + (data.armed ? 'text-teal-400' : 'text-gray-500');

        // Buzzer
        document.getElementById('card-buzzer').textContent =
            data.buzzer ? 'ACTIVE' : 'SILENT';
        document.getElementById('card-buzzer-wrap').className =
            'bg-gray-900 border ' + (data.buzzer ? 'border-red-500' : 'border-gray-700') + ' rounded-2xl p-5 flex flex-col gap-1';

        // LED
        document.getElementById('card-led').textContent = data.led ? 'ON' : 'OFF';
        document.getElementById('card-led').className =
            'text-2xl font-bold ' + (data.led ? 'text-teal-400' : 'text-gray-500');
        document.getElementById('card-led-wrap').className =
            'bg-gray-900 border ' + (data.led ? 'border-teal-500' : 'border-gray-700') + ' rounded-2xl p-5 flex flex-col gap-1';
        
        // Alert banner
        document.getElementById('alert-banner').style.display =
            data.buzzer ? 'flex' : 'none';

    } catch (e) {
        console.error('Live update failed:', e);
    }
}

// Poll every 0.5 seconds
setInterval(fetchLive, 500);
fetchLive(); // run immediately on load
</script>


@endsection

