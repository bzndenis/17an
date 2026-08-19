<div
    class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2"
    x-data
>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="flex items-center gap-3 rounded-xl border px-4 py-3 shadow-lg min-w-[280px] max-w-sm"
            :class="{
                'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200': item.type === 'success',
                'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/50 dark:text-red-200': item.type === 'error',
                'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-900/50 dark:text-blue-200': item.type === 'info',
            }"
        >
            <i
                :data-lucide="item.type === 'success' ? 'check-circle' : item.type === 'error' ? 'alert-circle' : 'info'"
                class="h-5 w-5 shrink-0"
            ></i>
            <p class="flex-1 text-sm font-medium" x-text="item.message"></p>
            <button @click="$store.toast.remove(item.id)" class="rounded p-1 hover:bg-black/5 dark:hover:bg-white/10">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>
    </template>
</div>
