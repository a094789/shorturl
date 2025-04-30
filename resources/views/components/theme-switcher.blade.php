@props(['class' => ''])

<div x-data="{ 
    isOpen: false,
    theme: localStorage.getItem('theme') || 'system',
    isDark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
    setTheme(newTheme) {
        this.theme = newTheme;
        localStorage.setItem('theme', newTheme);
        
        if (newTheme === 'dark' || (newTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            this.isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            this.isDark = false;
        }
        
        this.isOpen = false;
    }
}" 
x-init="
    if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        isDark = true;
    } else {
        document.documentElement.classList.remove('dark');
        isDark = false;
    }
    
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (theme === 'system') {
            isDark = e.matches;
            document.documentElement.classList.toggle('dark', e.matches);
        }
    });"
class="{{ $class }} relative">
    <button 
        @click="isOpen = !isOpen"
        type="button"
        class="relative p-2 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors"
    >
        <span class="sr-only">切換主題</span>
        
        <!-- 淺色模式圖標 -->
        <svg x-show="!isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
        </svg>
        
        <!-- 深色模式圖標 -->
        <svg x-show="isDark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
        </svg>
    </button>

    <!-- 下拉選單 -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        @click.away="isOpen = false"
        class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white dark:bg-slate-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-slate-100 dark:divide-slate-700"
        style="display: none;"
    >
        <div class="py-1">
            <button
                @click="setTheme('light')"
                :class="{'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': theme === 'light'}"
                class="flex w-full items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50"
            >
                <svg class="mr-3 h-4 w-4" :class="theme === 'light' ? 'text-blue-500 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500'" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                </svg>
                淺色
            </button>

            <button
                @click="setTheme('dark')"
                :class="{'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': theme === 'dark'}"
                class="flex w-full items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50"
            >
                <svg class="mr-3 h-4 w-4" :class="theme === 'dark' ? 'text-blue-500 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500'" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                </svg>
                深色
            </button>

            <button
                @click="setTheme('system')"
                :class="{'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': theme === 'system'}"
                class="flex w-full items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50"
            >
                <svg class="mr-3 h-4 w-4" :class="theme === 'system' ? 'text-blue-500 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500'" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/>
                </svg>
                系統
            </button>
        </div>
    </div>
</div> 