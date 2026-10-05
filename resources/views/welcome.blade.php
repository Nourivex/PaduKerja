<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Nourivex') }}</title>

    <meta
        name="description"
        content="Nourivex Laravel Engineering Template - a modern foundation for building structured web applications."
    >

    {{-- Favicon --}}
    <link
        rel="icon"
        href="{{ asset('favicon.ico') }}"
        type="image/x-icon"
    >

    {{-- PNG logo for modern browsers / high-resolution contexts --}}
    <link
        rel="icon"
        href="{{ asset('logo.png') }}"
        type="image/png"
    >

    {{-- Mobile / Apple devices --}}
    <link
        rel="apple-touch-icon"
        href="{{ asset('logo.png') }}"
    >

    {{-- Theme --}}
    <meta name="theme-color" content="#0f172a">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">

    {{-- Background --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">

        {{-- Soft accent --}}
        <div class="absolute left-1/2 top-[-300px] h-[500px] w-[900px] -translate-x-1/2 rounded-full bg-cyan-100/60 blur-3xl"></div>

        {{-- Subtle grid --}}
        <div
            class="absolute inset-0 opacity-[0.45]"
            style="
                background-image:
                    linear-gradient(rgba(15,23,42,.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15,23,42,.035) 1px, transparent 1px);
                background-size: 48px 48px;
            "
        ></div>

    </div>

    {{-- Navigation --}}
    <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-6 lg:px-8">

        <a href="{{ url('/') }}" class="flex items-center gap-3">

           {{-- Nourivex Logo --}}
<span class="flex h-11 w-11 items-center justify-center">
    <img
        src="{{ asset('logo.png') }}"
        alt="Nourivex"
        class="h-10 w-10 object-contain"
    >
</span>

            <div>
                <div class="text-sm font-bold tracking-wide text-slate-900">
                    NOURIVEX
                </div>

                <div class="text-[11px] text-slate-500">
                    Engineering Template
                </div>
            </div>

        </a>

        <nav class="flex items-center gap-1 text-sm">

            <a
                href="https://laravel.com/docs"
                target="_blank"
                rel="noopener noreferrer"
                class="hidden rounded-lg px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 sm:inline-flex"
            >
                Documentation
            </a>

            <a
                href="https://github.com/Nourivex"
                target="_blank"
                rel="noopener noreferrer"
                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
            >
                GitHub
            </a>

            @if (Route::has('login'))
                @auth

                    <a
                        href="{{ url('/dashboard') }}"
                        class="ml-1 rounded-lg bg-slate-900 px-4 py-2 font-medium text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Dashboard
                    </a>

                @else

                    <a
                        href="{{ route('login') }}"
                        class="hidden px-3 py-2 text-slate-600 transition hover:text-slate-950 sm:inline-flex"
                    >
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
                        >
                            Register
                        </a>
                    @endif

                @endauth
            @endif

        </nav>

    </header>


    {{-- Main --}}
    <main class="mx-auto flex min-h-[calc(100vh-180px)] w-full max-w-6xl items-center px-6 py-16 lg:px-8">

        <div class="grid w-full gap-16 lg:grid-cols-[1.1fr_.9fr] lg:items-center">


            {{-- Hero --}}
            <section>

                {{-- Status badge --}}
                <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1.5 text-xs font-medium text-cyan-700">

                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                    Nourivex Engineering Template

                </div>


                {{-- Heading --}}
                <h1 class="max-w-3xl text-5xl font-semibold leading-[1.05] tracking-[-0.04em] text-slate-950 sm:text-6xl lg:text-7xl">

                    Build with
                    <span class="text-cyan-600"> purpose.</span>

                    <br>

                    Ship with
                    <span class="text-slate-500"> structure.</span>

                </h1>


                {{-- Description --}}
                <p class="mt-7 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">

                    A modern Laravel foundation designed by
                    <span class="font-semibold text-slate-900">Nourivex</span>
                    for structured, maintainable, and production-ready web applications.

                </p>


                {{-- Actions --}}
                <div class="mt-9 flex flex-wrap gap-3">

                    <a
                        href="https://laravel.com/docs"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-800"
                    >

                        Get Started

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 20 20"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 10h11M10 5l5 5-5 5"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                    </a>


                    <a
                        href="https://github.com/Nourivex"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
                    >
                        Explore Nourivex
                    </a>

                </div>


                {{-- Tech stack --}}
                <div class="mt-12 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs text-slate-500">

                    <span>Laravel 13</span>

                    <span class="text-slate-300">•</span>

                    <span>PHP 8.4+</span>

                    <span class="text-slate-300">•</span>

                    <span>Tailwind CSS</span>

                    <span class="text-slate-300">•</span>

                    <span>Vite</span>

                </div>

            </section>


            {{-- System Card --}}
            <section>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">

                    {{-- Card header --}}
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">

                        <div class="flex items-center gap-2">

                            <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>

                        </div>

                        <span class="font-mono text-[11px] text-slate-400">
                            nourivex-template
                        </span>

                    </div>


                    {{-- Terminal content --}}
                    <div class="bg-slate-950 px-6 py-7 font-mono text-sm leading-7 text-slate-300">

                        <div class="text-slate-500">
                            $ php artisan about
                        </div>


                        <div class="mt-5 text-slate-300">
                            Application
                        </div>


                        <div class="mt-2 space-y-1 text-[13px]">

                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">
                                    Framework
                                </span>

                                <span class="text-cyan-400">
                                    Laravel {{ Illuminate\Foundation\Application::VERSION }}
                                </span>
                            </div>


                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">
                                    Environment
                                </span>

                                <span class="text-emerald-400">
                                    {{ app()->environment() }}
                                </span>
                            </div>


                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">
                                    Status
                                </span>

                                <span class="text-emerald-400">
                                    ● Ready
                                </span>
                            </div>

                        </div>


                        <div class="mt-7 text-slate-300">
                            Architecture
                        </div>


                        <div class="mt-2 space-y-1 text-[13px]">

                            <div>
                                <span class="text-cyan-400">→</span>
                                Structured
                            </div>

                            <div>
                                <span class="text-cyan-400">→</span>
                                Maintainable
                            </div>

                            <div>
                                <span class="text-cyan-400">→</span>
                                Developer-first
                            </div>

                        </div>


                        <div class="mt-7 text-slate-600">
                            $ _
                        </div>

                    </div>

                </div>


                {{-- Status --}}
                <div class="mt-4 flex items-center justify-between px-2 text-xs">

                    <span class="text-slate-500">
                        Maintained by Nourivex
                    </span>

                    <span class="flex items-center gap-2 text-emerald-600">

                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                        System ready

                    </span>

                </div>

            </section>

        </div>

    </main>


    {{-- Footer --}}
    <footer class="mx-auto flex w-full max-w-6xl flex-col gap-2 border-t border-slate-200 px-6 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">

        <span>
            © {{ date('Y') }} Nourivex. All rights reserved.
        </span>

        <span>

            Laravel Engineering Template

            <span class="mx-1 text-slate-300">
                ·
            </span>

            v{{ Illuminate\Foundation\Application::VERSION }}

        </span>

    </footer>

</body>
</html>
