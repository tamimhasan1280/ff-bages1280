/**
 * TURKY HUB - Advanced Autonomous Defense & Anti-Tampering Security Shield
 * Multi-layered defense: Anti-DevTools, Anti-Inspection, Anti-Scraping, Anti-Screenshot Blur Camouflage,
 * and AI Crawler Neutralization.
 * (c) Turky Hub Monster CDN Security - All Rights Reserved.
 */
(function(window, document) {
    'use strict';

    if (window.__TURKY_GUARD_INITIALIZED__) return;
    window.__TURKY_GUARD_INITIALIZED__ = true;

    // Determine if Super Admin mode is active
    const isAdmin = !!(window.__TURKY_ADMIN__ === true || (window.location.pathname.includes('admin.php') && document.cookie.includes('PHPSESSID')));

    // 1. INJECT SECURITY CSS STYLES
    const styleEl = document.createElement('style');
    styleEl.id = 'turky-guard-styles';
    styleEl.textContent = `
        /* Anti-Print Protection */
        @media print {
            body { display: none !important; }
            html::after {
                content: "TURKY HUB PROTECTED ASSET - REPRODUCTION AND PRINTING PROHIBITED";
                display: block !important;
                font-family: monospace;
                font-size: 20px;
                color: #ff0000;
                text-align: center;
                margin-top: 100px;
                font-weight: bold;
            }
        }

        /* Asset Shield: Prevent Dragging & Selection */
        .ff-badge-img, .ff-prime-badge-wrap, .ff-badge-shine, svg {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-user-drag: none !important;
        }

        /* Focus Loss / Snipping Tool Blur Camouflage */
        .turky-capture-blurred {
            filter: blur(30px) grayscale(95%) contrast(150%) !important;
            opacity: 0.08 !important;
            transition: filter 0.08s ease-out, opacity 0.08s ease-out !important;
            pointer-events: none !important;
        }

        /* Fullscreen Screen-Capture Deterrence Watermark */
        #turkyCaptureWatermarkOverlay {
            display: none;
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            z-index: 2147483647;
            background: rgba(4, 6, 15, 0.96);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            text-align: center;
            padding: 24px;
            pointer-events: none;
            box-sizing: border-box;
        }
        #turkyCaptureWatermarkOverlay.active {
            display: flex !important;
        }

        /* Micro Security Toast */
        #turkySecurityToast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: #111428;
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fbbf24;
            padding: 10px 18px;
            border-radius: 14px;
            font-size: 11px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            display: flex;
            items-center: center;
            gap: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 0 15px rgba(245, 158, 11, 0.2);
            z-index: 2147483646;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }
        #turkySecurityToast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    `;
    (document.head || document.documentElement).appendChild(styleEl);

    // 2. INJECT WATERMARK OVERLAY & TOAST CONTAINER INTO DOM
    function injectSecurityElements() {
        if (!document.getElementById('turkyCaptureWatermarkOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'turkyCaptureWatermarkOverlay';
            overlay.innerHTML = `
                <div style="max-width:440px;padding:32px;border-radius:24px;background:rgba(14,18,36,0.9);border:1px solid rgba(245,158,11,0.3);box-shadow:0 25px 50px -12px rgba(0,0,0,0.8);display:flex;flex-direction:column;align-items:center;gap:16px;">
                    <div style="width:64px;height:64px;border-radius:20px;background:linear-gradient(135deg,#f59e0b,#ef4444);display:flex;align-items:center;justify-content:center;box-shadow:0 0 30px rgba(239,68,68,0.4);font-size:28px;">
                        🛡️
                    </div>
                    <div style="font-size:18px;font-weight:900;letter-spacing:1px;text-transform:uppercase;color:#ffffff;">
                        Screen Capture Restricted
                    </div>
                    <p style="font-size:12px;color:#94a3b8;margin:0;line-height:1.6;">
                        Protected by Turky Hub Autonomous Shield. Badge assets and proprietary animations are strictly watermarked and access-controlled.
                    </p>
                    <div style="font-family:monospace;font-size:10px;color:#f59e0b;background:rgba(245,158,11,0.1);padding:6px 14px;border-radius:10px;border:1px solid rgba(245,158,11,0.2);">
                        AUTHENTICATED SECURE CDN LAYER
                    </div>
                </div>
            `;
            (document.body || document.documentElement).appendChild(overlay);
        }

        if (!document.getElementById('turkySecurityToast')) {
            const toast = document.createElement('div');
            toast.id = 'turkySecurityToast';
            toast.innerHTML = '<span>🛡️</span> <span id="turkyToastMsg">Security Policy: Right-click & inspection disabled.</span>';
            (document.body || document.documentElement).appendChild(toast);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectSecurityElements);
    } else {
        injectSecurityElements();
    }

    let toastTimer = null;
    function showSecurityToast(msg) {
        const toast = document.getElementById('turkySecurityToast');
        const msgEl = document.getElementById('turkyToastMsg');
        if (!toast) return;
        if (msgEl) msgEl.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 2200);
    }

    // 3. RIGHT-CLICK & CONTEXT MENU PREVENTION
    document.addEventListener('contextmenu', function(e) {
        if (isAdmin && e.target.closest('#badgeEditorForm, input, textarea, select')) {
            return; // Allow Super Admin inside input fields
        }
        e.preventDefault();
        e.stopPropagation();
        showSecurityToast('Security Policy: Right-click & asset inspection disabled.');
        return false;
    }, true);

    // 4. PREVENT DRAG & DROP OF BADGE ASSETS
    document.addEventListener('dragstart', function(e) {
        if (e.target && (e.target.nodeName === 'IMG' || e.target.nodeName === 'svg' || e.target.classList.contains('ff-badge-img') || e.target.closest('.ff-prime-badge-wrap'))) {
            e.preventDefault();
            return false;
        }
    }, true);

    // 5. DEVELOPER SHORTCUT INTERCEPTION (F12, CTRL+SHIFT+I, CTRL+U, CTRL+S, PRINT-SCREEN)
    window.addEventListener('keydown', function(e) {
        const key = e.key;
        const keyCode = e.keyCode || e.which;
        const ctrlOrMeta = e.ctrlKey || e.metaKey;

        // F12 Developer Tools
        if (key === 'F12' || keyCode === 123) {
            e.preventDefault();
            e.stopPropagation();
            showSecurityToast('Security Warning: Developer Tools shortcut blocked.');
            return false;
        }

        // Ctrl + Shift + I (Inspect), Ctrl + Shift + J (Console), Ctrl + Shift + C (Element Picker)
        if (ctrlOrMeta && e.shiftKey && (key === 'I' || key === 'i' || key === 'J' || key === 'j' || key === 'C' || key === 'c' || keyCode === 73 || keyCode === 74 || keyCode === 67)) {
            e.preventDefault();
            e.stopPropagation();
            showSecurityToast('Security Warning: Inspector shortcut blocked.');
            return false;
        }

        // Ctrl + U (View Source)
        if (ctrlOrMeta && (key === 'U' || key === 'u' || keyCode === 85)) {
            e.preventDefault();
            e.stopPropagation();
            showSecurityToast('Security Warning: Source code view blocked.');
            return false;
        }

        // Ctrl + S (Save Page)
        if (ctrlOrMeta && (key === 'S' || key === 's' || keyCode === 83)) {
            if (!isAdmin) {
                e.preventDefault();
                e.stopPropagation();
                showSecurityToast('Security Policy: Page saving disabled.');
                return false;
            }
        }

        // Ctrl + P (Print Page)
        if (ctrlOrMeta && (key === 'P' || key === 'p' || keyCode === 80)) {
            e.preventDefault();
            e.stopPropagation();
            showSecurityToast('Security Policy: Printing disabled by security policy.');
            return false;
        }

        // PrintScreen Key
        if (key === 'PrintScreen' || keyCode === 44) {
            e.preventDefault();
            triggerCaptureShield();
            showSecurityToast('Security Notice: Screen capture detected and watermarked.');
            return false;
        }
    }, true);

    // 6. SNIPPING TOOL & WINDOW FOCUS LOSS CAMOUFLAGE (Win+Shift+S / Snipping Tool Detection)
    let shieldTimeout = null;

    function triggerCaptureShield() {
        const overlay = document.getElementById('turkyCaptureWatermarkOverlay');
        const main = document.getElementById('mainAppContainer') || document.body;
        if (overlay) overlay.classList.add('active');
        if (main) main.classList.add('turky-capture-blurred');
    }

    function removeCaptureShield() {
        clearTimeout(shieldTimeout);
        shieldTimeout = setTimeout(function() {
            const overlay = document.getElementById('turkyCaptureWatermarkOverlay');
            const main = document.getElementById('mainAppContainer') || document.body;
            if (overlay) overlay.classList.remove('active');
            if (main) main.classList.remove('turky-capture-blurred');
        }, 120);
    }

    // Whenever window loses focus (Snipping Tool, Win+Shift+S, Alt+Tab, Screenshot Apps)
    window.addEventListener('blur', function() {
        if (!isAdmin) {
            triggerCaptureShield();
        }
    });

    // When tab visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            triggerCaptureShield();
        } else {
            removeCaptureShield();
        }
    });

    // When window regains focus
    window.addEventListener('focus', function() {
        removeCaptureShield();
    });

    // 7. ACTIVE DEBUGGER TRAP (FREEZES DEVTOOLS FOR UNAUTHORIZED VISITORS)
    if (!isAdmin) {
        setInterval(function() {
            (function() {
                return false;
            }['constructor']('debugger')());
        }, 300);

        // Console Neutralizer: Silently disable log dumping
        try {
            const emptyFn = function() {};
            ['log', 'info', 'warn', 'error', 'debug', 'dir', 'table'].forEach(function(m) {
                console[m] = emptyFn;
            });
            setInterval(function() {
                try { console.clear(); } catch(e) {}
            }, 1200);
        } catch(e) {}
    }

})(window, document);
