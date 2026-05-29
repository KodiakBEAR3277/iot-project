<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'IoT Safety System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#0f766e', light: '#14b8a6', dark: '#0d4f4a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between">
        <span class="font-bold text-brand-light tracking-wide text-lg">
            🌡️ Heat Safety Monitor
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-sm text-gray-400 hover:text-white transition">
                Logout
            </button>
        </form>
    </nav>

    <!-- Flash messages -->
    <div class="max-w-5xl mx-auto px-4 pt-4">
        @if(session('success'))
            <div class="bg-green-800/40 border border-green-600 text-green-300 px-4 py-2 rounded-lg text-sm mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-800/40 border border-red-600 text-red-300 px-4 py-2 rounded-lg text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    <main class="max-w-5xl mx-auto px-4 pb-12">
        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>