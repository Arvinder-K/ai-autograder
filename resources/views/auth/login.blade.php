<x-layouts.auth>
    <div class="auth-shell">

        {{-- Brand Panel --}}
        <section class="auth-panel--brand">
            <div class="max-w-md text-center">

                <div class="w-20 h-20 bg-brand-accent rounded-xl flex items-center justify-center mx-auto mb-8">
                    <span class="font-heading font-black text-display text-txt-inverse">
                        AI
                    </span>
                </div>

                <h1 class="font-heading font-black text-display uppercase tracking-wide mb-4">
                    AI AUTO GRADER
                </h1>

                <p class="font-base text-body-lg text-txt-inverse/80 leading-relaxed">
                    Evaluate and grade assignments automatically
                    with the precision and consistency
                    of advanced AI.
                </p>

                <div class="mt-12 grid grid-cols-3 gap-6 text-center">

                    <div>
                        <div class="text-title-lg font-heading font-bold text-brand-accent">
                            Stories
                        </div>

                        <div class="text-body-sm text-txt-inverse/60 mt-1">
                            AI-Generated
                        </div>
                    </div>

                    <div>
                        <div class="text-title-lg font-heading font-bold text-brand-accent">
                            Features
                        </div>

                        <div class="text-body-sm text-txt-inverse/60 mt-1">
                            Multi-Format
                        </div>
                    </div>

                    <div>
                        <div class="text-title-lg font-heading font-bold text-brand-accent">
                            Prompts
                        </div>

                        <div class="text-body-sm text-txt-inverse/60 mt-1">
                            Micro-Optimized
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Login Form --}}
        <section class="auth-panel--form">

            <div class="w-full max-w-md">

                <div class="card">

                    <h2 class="font-heading font-bold text-title text-txt-primary mb-2">
                        Welcome Back
                    </h2>

                    <p class="text-body-sm text-txt-muted mb-8">
                        Login to continue to AI Auto Grader
                    </p>

                    @if (session('error'))
                        <div class="alert-danger mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">

                        @csrf

                        <div>
                            <label class="block mb-2 font-medium">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="admin@test.com"
                                class="w-full border rounded-lg px-4 py-3"
                                required
                            >
                        </div>

                        <div>
                            <label class="block mb-2 font-medium">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                value="12345678"
                                class="w-full border rounded-lg px-4 py-3"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="w-full py-3 bg-black text-white rounded-lg font-semibold"
                        >
                            Login
                        </button>

                    </form>

                </div>

                <p class="text-center text-caption text-txt-muted mt-6">
                    &copy; {{ date('Y') }} CLaaS2SaaS. All rights reserved.
                </p>

            </div>

        </section>

    </div>
</x-layouts.auth>