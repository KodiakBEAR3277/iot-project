<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login — IoT Safety System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-sm bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-xl">

        <div class="text-center mb-8">
            <div class="text-4xl mb-2">🌡️</div>
            <h1 class="text-xl font-bold text-white">Heat Safety Monitor</h1>
            <p class="text-gray-500 text-sm mt-1">Sign in to your dashboard</p>
        </div>

        @if($errors->any())
            <div class="bg-red-800/40 border border-red-600 text-red-300 px-4 py-2 rounded-lg text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm text-gray-400 mb-1">Email</label>
                <input
                    type="email" name="email" value="{{ old('email') }}"
                    required autofocus
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-teal-500 transition"
                    placeholder="admin@iot.local"
                />
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Password</label>
                <input
                    type="password" name="password"
                    required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-teal-500 transition"
                    placeholder="••••••••"
                />
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="accent-teal-500"/>
                <label for="remember" class="text-sm text-gray-400">Remember me</label>
            </div>

            <button
                type="submit"
                class="w-full bg-teal-600 hover:bg-teal-500 text-white font-semibold py-2.5 rounded-lg transition text-sm mt-2">
                Sign in
            </button>
        </form>

    </div>

</body>
</html>