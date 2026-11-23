/**
 * BrandBuilder Theme Manager
 */
(function () {
    'use strict';

    const THEME_KEY = 'theme';
    const DARK = 'dark';
    const LIGHT = 'light';

    function getPreferredTheme() {
        const saved = localStorage.getItem(THEME_KEY);
        if (saved === DARK || saved === LIGHT) return saved;
        return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)
            ? DARK
            : LIGHT;
    }

    function applyTheme(theme) {
        const isDark = theme === DARK;
        const html = document.documentElement;
        const body = document.body;

        if (isDark) {
            html.classList.add('dark-mode');
            if (body) body.classList.add('dark-mode');
        } else {
            // MUST REMOVE FROM BOTH HTML AND BODY
            html.classList.remove('dark-mode');
            if (body) body.classList.remove('dark-mode');
        }

        // Update the toggle button UI
        const btn = document.getElementById('theme-toggle');
        if (btn) {
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            const icon = btn.querySelector('.theme-toggle-icon');
            if (icon) icon.textContent = isDark ? '☀️' : '🌙';
        }
    }

    function toggleTheme() {
        const isDark = document.body.classList.contains('dark-mode');
        const next = isDark ? LIGHT : DARK;
        localStorage.setItem(THEME_KEY, next);
        applyTheme(next);
    }

    // Apply immediately
    applyTheme(getPreferredTheme());

    // Bind when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getPreferredTheme());

        const btn = document.getElementById('theme-toggle');
        if (btn) {
            btn.addEventListener('click', toggleTheme);
        }
    });
})();