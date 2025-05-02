<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('名稱')" />
            <x-text-input id="name" class="block mt-1 w-full" 
                type="text" 
                name="name" 
                :value="old('name')" 
                required 
                autofocus 
                autocomplete="name"
                minlength="2"
                pattern="[\u4e00-\u9fa5a-zA-Z\-]+"
            />
            <div class="mt-1 text-sm text-gray-600">
                名稱只能包含中文、英文和連字符(-)
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Name ID -->
        <div class="mt-4">
            <x-input-label for="name_id" :value="__('名稱 ID')" />
            <x-text-input 
                id="name_id" 
                class="block mt-1 w-full" 
                type="text" 
                name="name_id" 
                :value="old('name_id')" 
                required 
                pattern="[a-zA-Z0-9_-]+"
                minlength="3"
                maxlength="30"
            />
            <div class="mt-1 text-sm text-gray-600">
                <ul class="list-disc list-inside">
                    <li>只能包含英文字母、數字、底線(_)和連字符(-)</li>
                    <li>長度必須在 3-30 個字元之間</li>
                    <li>註冊後無法更改</li>
                </ul>
            </div>
            <x-input-error :messages="$errors->get('name_id')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('密碼')" />
            <x-text-input 
                id="password" 
                class="block mt-1 w-full"
                type="password"
                name="password"
                required 
                autocomplete="new-password"
                minlength="6"
                maxlength="50"
            />
            <div class="mt-1 text-sm text-gray-600">
                密碼必須至少為6位數，並包含至少一個小寫字母、一個大寫字母和一個數字。
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('確認密碼')" />
            <x-text-input 
                id="password_confirmation" 
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation" 
                required 
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- reCAPTCHA -->
        <div class="mt-4">
            <x-input-label :value="__('人機驗證')" />
            <div class="border border-gray-300 rounded-md p-4 bg-gray-50">
                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            </div>
            @if($errors->has('g-recaptcha-response'))
                <div class="mt-2 text-sm text-red-600">{{ $errors->first('g-recaptcha-response') }}</div>
            @endif
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('已經註冊過了？') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('註冊') }}
            </x-primary-button>
        </div>
    </form>

    <!-- reCAPTCHA JS -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</x-guest-layout>
