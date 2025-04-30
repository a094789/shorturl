<nav class="bg-[#1a1a1a] fixed w-full z-50 top-0 start-0 border-b border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <x-application-logo class="h-8" />
                <span class="self-center text-xl font-semibold whitespace-nowrap text-white ms-3">ShortURL</span>
            </a>

            <!-- Mobile menu button -->
            <button data-collapse-toggle="navbar-default" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-400 rounded-lg md:hidden hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-600" aria-controls="navbar-default" aria-expanded="false">
                <span class="sr-only">開啟主選單</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>

            <!-- Navigation Menu -->
            <div class="hidden md:flex md:items-center md:space-x-8" id="navbar-default">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('儀表板') }}
                </x-nav-link>
                
                <x-nav-link :href="route('short-urls.index')" :active="request()->routeIs('short-urls.*')">
                    {{ __('短網址管理') }}
                </x-nav-link>

                @if (Auth::user()->is_admin)
                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('使用者管理') }}
                </x-nav-link>
                <x-nav-link :href="route('admin.user-locks.index')" :active="request()->routeIs('admin.user-locks.*')">
                    {{ __('登入鎖定管理') }}
                </x-nav-link>
                @endif

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button type="button" class="flex items-center px-3 py-2 text-sm font-medium text-gray-300 hover:text-white" id="user-menu-button" aria-expanded="false" data-dropdown-toggle="user-dropdown" data-dropdown-placement="bottom">
                        {{ Auth::user()->name }}
                        <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div class="z-50 hidden my-4 text-base list-none bg-gray-800 divide-y divide-gray-700 rounded-lg shadow" id="user-dropdown">
                        <div class="px-4 py-3">
                            <span class="block text-sm text-white">{{ Auth::user()->name }}</span>
                            <span class="block text-sm text-gray-400 truncate">{{ Auth::user()->email }}</span>
                        </div>
                        <ul class="py-2" aria-labelledby="user-menu-button">
                            <li>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                    {{ __('個人資料') }}
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                        {{ __('登出') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div class="hidden w-full md:hidden" id="navbar-mobile">
                <ul class="flex flex-col p-4 mt-4 font-medium rounded-lg bg-gray-800">
                    <li>
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('儀表板') }}
                        </x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :href="route('short-urls.index')" :active="request()->routeIs('short-urls.*')">
                            {{ __('短網址管理') }}
                        </x-nav-link>
                    </li>
                    @if (Auth::user()->is_admin)
                    <li>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('使用者管理') }}
                        </x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :href="route('admin.user-locks.index')" :active="request()->routeIs('admin.user-locks.*')">
                            {{ __('登入鎖定管理') }}
                        </x-nav-link>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('profile.edit') }}" class="block py-2 px-3 text-gray-300 rounded hover:bg-gray-700 hover:text-white">
                            {{ __('個人資料') }}
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left py-2 px-3 text-gray-300 rounded hover:bg-gray-700 hover:text-white">
                                {{ __('登出') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
@auth
    <div class="px-4 py-2 text-white text-sm">
        👤 {{ Auth::user()->name }}
        @if (Auth::user()->is_admin)
            <span class="ml-2 px-2 py-1 bg-red-600 text-white rounded text-xs">管理員</span>
        @endif
    </div>

    @if (Auth::user()->is_admin)
        <div class="px-4 py-2">
            <a href="{{ route('admin.users.index') }}" class="block text-white hover:underline">
                🛠️ 使用者管理（後台）
            </a>
        </div>
    @endif
@endauth
