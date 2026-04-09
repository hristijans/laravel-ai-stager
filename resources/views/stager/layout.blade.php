<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Stager @hasSection('title')— @yield('title')@endif</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen text-gray-900 antialiased">

<header class="bg-white border-b border-gray-200">
    <div class="max-w-5xl mx-auto px-6 py-4 flex items-center gap-8">
        <span class="font-semibold text-gray-800 flex items-center gap-2">
            🎭 <span>AI Stager</span>
        </span>
        <nav class="flex gap-6 text-sm">
            <a href="{{ route('ai-stager.dashboard') }}"
               class="{{ request()->routeIs('ai-stager.dashboard') ? 'text-indigo-600 font-medium' : 'text-gray-500 hover:text-gray-800' }}">
                Dashboard
            </a>
            <a href="{{ route('ai-stager.logs') }}"
               class="{{ request()->routeIs('ai-stager.logs') ? 'text-indigo-600 font-medium' : 'text-gray-500 hover:text-gray-800' }}">
                Intercept Log
            </a>
        </nav>
    </div>
</header>

<main class="max-w-5xl mx-auto px-6 py-8">
    @yield('content')
</main>

@livewireScripts
</body>
</html>
