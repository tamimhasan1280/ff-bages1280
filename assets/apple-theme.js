/**
 * TURKY HUB — Centralized Apple Dark/Light Theme & Mobile Navigation Controller
 */
(function(window, document) {
    'use strict';

    const STORAGE_KEY = 'turky_theme';

    function getSystemTheme() {
        try {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
        } catch (e) {
            return 'dark';
        }
    }

    function getStoredTheme() {
        try {
            const val = window.localStorage.getItem(STORAGE_KEY);
            return (val === 'light' || val === 'dark') ? val : null;
        } catch (e) {
            return null;
        }
    }

    function applyTheme(theme, persist) {
        const next = (theme === 'light') ? 'light' : 'dark';
        const root = document.documentElement;
        root.setAttribute('data-theme', next);
        root.classList.remove('light', 'dark');
        root.classList.add(next);

        if (persist) {
            try { window.localStorage.setItem(STORAGE_KEY, next); } catch (e) {}
        }

        // Update accessible toggle states & labels
        document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
            const isLight = next === 'light';
            btn.setAttribute('aria-pressed', isLight ? 'true' : 'false');
            btn.setAttribute('aria-label', isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode');
            btn.setAttribute('title', isLight ? 'Switch to Dark Mode' : 'Switch to Light Mode');
            const label = btn.querySelector('[data-theme-label]');
            if (label) {
                label.textContent = isLight ? 'Light' : 'Dark';
            }
        });
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark', true);
    }

    function bindUI() {
        applyTheme(getStoredTheme() || getSystemTheme(), false);

        document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
            if (btn.dataset.themeBound) return;
            btn.dataset.themeBound = '1';
            btn.addEventListener('click', toggleTheme);
        });

        // Mobile Navigation Drawer Toggle
        document.querySelectorAll('[data-mobile-nav-toggle]').forEach(function(btn) {
            if (btn.dataset.navBound) return;
            btn.dataset.navBound = '1';
            btn.addEventListener('click', function() {
                const targetId = btn.getAttribute('aria-controls') || 'mobileNavDrawer';
                const drawer = document.getElementById(targetId);
                if (!drawer) return;
                const willOpen = drawer.classList.contains('hidden');
                drawer.classList.toggle('hidden', !willOpen);
                btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindUI);
    } else {
        bindUI();
    }

    window.TurkyTheme = {
        get: function() { return document.documentElement.getAttribute('data-theme') || 'dark'; },
        set: function(theme) { applyTheme(theme, true); },
        toggle: toggleTheme,
        init: bindUI
    };
})(window, document);
