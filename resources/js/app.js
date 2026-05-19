import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// App configuration (set by Blade layout)
window.AppConfig = window.AppConfig || { baseUrl: '' };

/**
 * Make API calls using the app base URL.
 */
window.appFetch = async function (path, options = {}) {
    const url = `${window.AppConfig.baseUrl}${path}`;
    const defaults = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    };

    const merged = {
        ...defaults,
        ...options,
        headers: { ...defaults.headers, ...options.headers },
    };

    const response = await fetch(url, merged);

    if (!response.ok) {
        throw new Error(`Request failed: ${response.status}`);
    }

    return response.json();
};

/**
 * Navigate using the app base URL.
 */
window.appNavigate = function (path) {
    window.location.href = `${window.AppConfig.baseUrl}${path}`;
};

/**
 * Sidebar toggle for mobile.
 */
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }
});
