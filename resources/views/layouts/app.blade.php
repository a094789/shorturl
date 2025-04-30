<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ShortURL') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased">
        <div class="min-h-full" 
            x-data="{ 
                mobileMenuOpen: false, 
                toggleMenu() { this.mobileMenuOpen = !this.mobileMenuOpen }
            }">
            <!-- 導航欄 -->
            <nav class="bg-white shadow-sm">
                <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <!-- 左側 Logo -->
                        <div class="flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-red-600">ShortURL</a>
                        </div>

                        <!-- 右側導航和用戶選單 -->
                        <div class="hidden sm:flex sm:items-center sm:space-x-4">
                            <!-- 主要導航選項 -->
                            <a href="{{ route('short-urls.create') }}" 
                               class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                                建立短網址
                            </a>
                            <a href="{{ route('short-urls.index') }}"
                               class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                                管理短網址
                            </a>
                            @if (Auth::user()->is_admin)
                            <!-- 管理員下拉選單 -->
                            <div class="relative" x-data="{ adminMenuOpen: false }" @click.away="adminMenuOpen = false">
                                <button type="button" 
                                        @click="adminMenuOpen = !adminMenuOpen"
                                        class="flex items-center text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium focus:outline-none">
                                    <span>管理員功能</span>
                                    <svg class="ml-1 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div x-show="adminMenuOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                    <a href="{{ route('admin.users.index') }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        管理使用者
                                    </a>
                                    <a href="{{ route('admin.user-locks.index') }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        登入鎖定管理
                                    </a>
                                </div>
                            </div>
                            @endif

                            <!-- 用戶選單 -->
                            <div class="relative ml-3" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
                                <div>
                                    <button type="button" 
                                            @click="open = !open"
                                            class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none" 
                                            id="user-menu-button">
                                        <span class="mr-2">{{ Auth::user()->name }}</span>
                                    </button>
                                </div>

                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                     role="menu">
                                    <a href="{{ route('profile.edit') }}" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                       role="menuitem">
                                        編輯個人資料
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" 
                                                role="menuitem">
                                            登出
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 手機版選單按鈕 -->
                        <div class="flex items-center sm:hidden">
                            <button type="button" 
                                    @click="toggleMenu()"
                                    class="inline-flex items-center justify-center p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500">
                                <span class="sr-only">開啟主選單</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 手機版選單 -->
                <div class="sm:hidden" 
                     x-show="mobileMenuOpen" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     x-cloak>
                    <div class="space-y-1 pb-3 pt-2">
                        <a href="{{ route('short-urls.create') }}" 
                            class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            建立短網址
                        </a>
                        <a href="{{ route('short-urls.index') }}"
                            class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            管理短網址
                        </a>
                        @if (Auth::user()->is_admin)
                        <!-- 手機版管理員選單 -->
                        <div x-data="{ mobileAdminMenuOpen: false }">
                            <button @click="mobileAdminMenuOpen = !mobileAdminMenuOpen" 
                                    class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                                <span>管理員功能</span>
                                <svg class="h-5 w-5" :class="mobileAdminMenuOpen ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="mobileAdminMenuOpen" class="pl-4">
                                <a href="{{ route('admin.users.index') }}"
                                   class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                                    管理使用者
                                </a>
                                <a href="{{ route('admin.user-locks.index') }}"
                                   class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                                    登入鎖定管理
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="border-t border-gray-200 pb-3 pt-4">
                        <div class="flex items-center">
                            <div class="ml-3">
                                <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('profile.edit') }}" 
                                class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                                編輯個人資料
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                                    登出
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- 主要內容區 -->
            <main class="py-6">
                {{ $slot }}
            </main>

            <!-- 頁尾 -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="max-w-[95%] mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} ShortURL. All rights reserved.
                    </div>
                </div>
            </footer>
        </div>

        <!-- 通知提示 -->
        @if (session('success'))
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed bottom-0 right-0 mb-4 mr-4 z-50">
            <div class="bg-green-50 p-4 rounded-lg shadow-lg border border-green-100 max-w-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button @click="show = false" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600">
                                <span class="sr-only">關閉</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @stack('scripts')
    </body>
</html>
