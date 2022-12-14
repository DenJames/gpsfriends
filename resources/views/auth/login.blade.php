<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <div class="text-sm">
            Applikationen bruger websockets, ønsker du at se det "live" så login med de pre-udfyldte informationer også åben siden i en anden browser og brug en eller flere af følgende brugere:<br>

            <div class="mt-2 p-2 rounded bg-gray-100">
                <div class="flex justify-between">
                    <div class="flex gap-x-1">
                        <p id="demo2">
                            demo2@example.com
                        </p>
                    </div>

                    <button onclick="useAnotherAccount('demo2')" class="text-blue-600 cursor-pointer text-sm hover:underline hover:text-blue-800">Brug</button>
                </div>

                <div class="flex justify-between">
                    <div class="flex gap-x-1">
                        <p id="demo3">
                            demo3@example.com
                        </p>
                    </div>

                    <button onclick="useAnotherAccount('demo3')" class="text-blue-600 cursor-pointer text-sm hover:underline hover:text-blue-800">Brug</button>
                </div>

                <div class="flex justify-between">
                    <div class="flex gap-x-1">
                        <p id="demo4">
                            demo4@example.com
                        </p>
                    </div>

                    <button onclick="useAnotherAccount('demo4')" class="text-blue-600 cursor-pointer text-sm hover:underline hover:text-blue-800">Brug</button>
                </div>
            </div>
        </div>

        <script>
            function useAnotherAccount(accountId) {
                const el = document.getElementById(accountId);

                document.getElementById('email').value = el.innerHTML;
            }
        </script>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-6">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />

                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" value="demo@example.com" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                value="password"
                                required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('register'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                        {{ __('No account? Click here to register') }}
                    </a>
                @endif

                <x-primary-button class="ml-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
