import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('app', {
    darkMode: localStorage.getItem('darkMode') === 'true',
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    mobileSidebarOpen: false,

    init() {
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        }
    },

    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', this.darkMode);
        document.documentElement.classList.toggle('dark', this.darkMode);
    },

    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
    },

    openMobileSidebar() {
        this.mobileSidebarOpen = true;
    },

    closeMobileSidebar() {
        this.mobileSidebarOpen = false;
    },
});

Alpine.store('toast', {
    items: [],
    nextId: 1,

    add(message, type = 'success', duration = 4000) {
        const id = this.nextId++;
        this.items.push({ id, message, type });
        setTimeout(() => this.remove(id), duration);
    },

    remove(id) {
        this.items = this.items.filter(item => item.id !== id);
    },
});

document.addEventListener('alpine:init', () => {
    Alpine.store('app').init();
});

Alpine.start();
