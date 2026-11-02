/**
 * Free Fire Badges & V-Badges Master Client SDK & Web Component v2.0.0
 * Ultra-fast CDN delivery, interactive Web Component, and instant DOM injection.
 * (c) Free Fire Badges CDN Project - MIT License
 */
(function(window, document) {
    'use strict';

    const DEFAULT_REPO = 'tamim1280/freefire-badges-cdn';
    const CDN_TEMPLATES = {
        jsdelivr: 'https://cdn.jsdelivr.net/gh/{REPO}@main/badges/',
        github_raw: 'https://raw.githubusercontent.com/{REPO}/main/badges/',
        statically: 'https://cdn.statically.io/gh/{REPO}/main/badges/'
    };

    let activeRepo = DEFAULT_REPO;
    let activeCDN = 'jsdelivr';
    let customBaseUrl = '';

    // Master Registry of Free Fire Badges
    const BADGES_DATABASE = {
        'official-v': {
            id: 'official-v',
            db_id: 2,
            name: 'OFFICIAL V BAGE',
            title: 'Official Influencer V-Badge',
            category: 'Official Partner (KOL)',
            file: 'FF_UI_Badge_KOL01.png',
            glowColor: 'rgba(255, 215, 0, 0.85)',
            glowClass: 'ff-glow-gold',
            priority: 2000000,
            defaultSize: 34,
            rarity: 'Mythic Partner',
            type: 'image'
        },
        'diamond-partner': {
            id: 'diamond-partner',
            db_id: 25,
            name: 'FF Diamond Partner',
            title: 'Diamond Partner / Elite VIP',
            category: 'Crystal VIP Partner',
            file: 'FF_UI_Badge_KOL03.png',
            glowColor: 'rgba(168, 85, 247, 0.85)',
            glowClass: 'ff-glow-purple',
            priority: 1500000,
            defaultSize: 34,
            rarity: 'Crystal VIP',
            type: 'image'
        },
        'esports-crown': {
            id: 'esports-crown',
            db_id: 21,
            name: 'FF Esports PRO Crown',
            title: 'Verified Esports PRO Crown',
            category: 'Competitive & Esports',
            file: 'FF_UI_Badge_ESPORTS01.png',
            glowColor: 'rgba(251, 191, 36, 0.85)',
            glowClass: 'ff-glow-amber',
            priority: 95000,
            defaultSize: 34,
            rarity: 'Tournament Champion',
            type: 'image'
        },
        'gm-staff': {
            id: 'gm-staff',
            db_id: 22,
            name: 'FF Official Staff / GM',
            title: 'Garena Official Staff & GM',
            category: 'Administration & Staff',
            file: 'FF_UI_Badge_GM01.png',
            glowColor: 'rgba(239, 68, 68, 0.85)',
            glowClass: 'ff-glow-red',
            priority: 90000,
            defaultSize: 34,
            rarity: 'Official Authority',
            type: 'image'
        },
        'craftland-creator': {
            id: 'craftland-creator',
            db_id: 23,
            name: 'FF Craftland Creator',
            title: 'Craftland Map Architect',
            category: 'Creator & Architect',
            file: 'craftland.png',
            glowColor: 'rgba(6, 182, 212, 0.85)',
            glowClass: 'ff-glow-cyan',
            priority: 85000,
            defaultSize: 34,
            rarity: 'Master Builder',
            type: 'image'
        },
        'emerging-streamer': {
            id: 'emerging-streamer',
            db_id: 24,
            name: 'FF Emerging Creator',
            title: 'Rising Streamer & Tier-2 Partner',
            category: 'Streamer & Creator',
            file: 'FF_UI_Badge_KOL02.png',
            glowColor: 'rgba(16, 185, 129, 0.85)',
            glowClass: 'ff-glow-emerald',
            priority: 80000,
            defaultSize: 34,
            rarity: 'Rising Talent',
            type: 'image'
        },
        'vbadge-classic': {
            id: 'vbadge-classic',
            name: 'Classic Gold V-Badge',
            title: 'Classic Free Fire V-Badge',
            category: 'Legacy & Classics',
            file: 'vbadge.png',
            glowColor: 'rgba(255, 215, 0, 0.85)',
            glowClass: 'ff-glow-gold',
            priority: 75000,
            defaultSize: 32,
            rarity: 'Classic Legacy',
            type: 'image'
        },
        'vbadge-vector': {
            id: 'vbadge-vector',
            name: 'Vector SVG V-Badge',
            title: 'Clean Vector V-Badge',
            category: 'Vector & SVG',
            file: 'vbadge.svg',
            glowColor: 'rgba(255, 215, 0, 0.85)',
            glowClass: 'ff-glow-gold',
            priority: 70000,
            defaultSize: 30,
            rarity: 'Ultra Vector',
            type: 'svg'
        },
        'moderator-shield': {
            id: 'moderator-shield',
            db_id: 5,
            name: 'MODERETOR',
            title: 'Guardian Shield Moderator',
            category: 'Community & Security',
            glowColor: 'rgba(255, 215, 0, 0.9)',
            priority: 10000,
            defaultSize: 22,
            rarity: 'Shield Guardian',
            type: 'svg_code',
            svgCode: '<svg viewBox="0 0 24 24" fill="none" style="width:100%;height:100%;animation:pulse_gold 2s infinite ease-in-out;"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z" fill="url(#mod_grad)"/><path d="M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l5.59-5.59L17 8.5 10 15.5z" fill="#000"/><defs><linearGradient id="mod_grad" x1="4" y1="2" x2="20" y2="22" gradientUnits="userSpaceOnUse"><stop stop-color="#FFE000"/><stop offset="1" stop-color="#799F0C"/></linearGradient></defs></svg>'
        },
        'pro-player': {
            id: 'pro-player',
            db_id: 4,
            name: 'PRO-PLYER',
            title: 'Pro Competitor Text Badge',
            category: 'Competitive & Ranked',
            glowColor: 'rgba(249, 212, 35, 0.9)',
            priority: 1000,
            defaultSize: 16,
            rarity: 'Competitive',
            type: 'text_code',
            textCode: '<span style="font-family:Orbitron,sans-serif;font-weight:900;font-size:13px;letter-spacing:1px;color:#f9d423;animation:fire_text 2s infinite alternate;text-transform:uppercase;">PRO</span>'
        },
        'legendary-diamond': {
            id: 'legendary-diamond',
            db_id: 3,
            name: 'Legendary',
            title: 'Legendary Mystic Diamond',
            category: 'Exclusive Ranks',
            glowColor: 'rgba(168, 85, 247, 0.95)',
            priority: 95,
            defaultSize: 24,
            rarity: 'Mythic Legend',
            type: 'svg_code',
            svgCode: '<svg viewBox="0 0 24 24" style="width:100%;height:100%;animation:glow_legend 2.5s infinite ease-in-out;"><defs><linearGradient id="leg_grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#8e2de2"/><stop offset="100%" stop-color="#4a00e0"/></linearGradient></defs><path fill="url(#leg_grad)" d="M12 .5L4.5 9l7.5 14.5L19.5 9L12 .5zm0 2.31L17.5 9H6.5L12 2.81z"/></svg>'
        },
        'hud-alive': {
            id: 'hud-alive',
            name: 'HUD Alive Badge',
            title: 'Free Fire Alive Counter HUD',
            category: 'Game UI & HUD',
            file: 'badge_alive_teal.png',
            glowColor: 'rgba(20, 184, 166, 0.85)',
            glowClass: 'ff-glow-cyan',
            priority: 500,
            defaultSize: 26,
            rarity: 'HUD Badge',
            type: 'image'
        },
        'hud-kill': {
            id: 'hud-kill',
            name: 'HUD Kill Badge',
            title: 'Free Fire Elimination HUD',
            category: 'Game UI & HUD',
            file: 'badge_kill_maroon.png',
            glowColor: 'rgba(225, 29, 72, 0.85)',
            glowClass: 'ff-glow-red',
            priority: 500,
            defaultSize: 26,
            rarity: 'HUD Badge',
            type: 'image'
        }
    };

    // Public SDK API
    const FFBadges = {
        version: '2.0.0',

        setRepo: function(repo) {
            activeRepo = repo;
            return this;
        },

        getRepo: function() {
            return activeRepo;
        },

        setCDN: function(providerOrUrl) {
            if (CDN_TEMPLATES[providerOrUrl]) {
                activeCDN = providerOrUrl;
                customBaseUrl = '';
            } else {
                customBaseUrl = providerOrUrl.replace(/\/?$/, '/');
            }
            return this;
        },

        getBaseUrl: function() {
            if (customBaseUrl) return customBaseUrl;
            return CDN_TEMPLATES[activeCDN].replace('{REPO}', activeRepo);
        },

        getImageUrl: function(file) {
            if (!file) return '';
            if (/^(https?:|\/\/|data:)/i.test(file)) return file;
            return this.getBaseUrl() + file;
        },

        get: function(keyOrId) {
            if (!keyOrId) return null;
            if (BADGES_DATABASE[keyOrId]) return BADGES_DATABASE[keyOrId];
            const lower = String(keyOrId).toLowerCase().replace(/[^a-z0-9]/g, '');
            for (const k in BADGES_DATABASE) {
                const b = BADGES_DATABASE[k];
                if (b.id.replace(/[^a-z0-9]/g, '') === lower ||
                    b.name.toLowerCase().replace(/[^a-z0-9]/g, '') === lower ||
                    String(b.db_id) === String(keyOrId)) {
                    return b;
                }
            }
            return null;
        },

        list: function() {
            return Object.values(BADGES_DATABASE);
        },

        render: function(badgeKey, options) {
            options = options || {};
            const b = this.get(badgeKey) || BADGES_DATABASE['official-v'];
            const size = options.size ? (typeof options.size === 'number' ? options.size + 'px' : options.size) : (b.defaultSize + 'px');
            const position = options.position || 'inline';
            const speed = options.speed || '2.6s';

            let posStyle = 'display:inline-flex;position:relative;vertical-align:middle;';
            if (position === 'top-right') {
                posStyle = 'position:absolute;top:-4px;right:-4px;z-index:25;';
            } else if (position === 'top-left') {
                posStyle = 'position:absolute;top:-4px;left:-4px;z-index:25;';
            } else if (position === 'bottom-right') {
                posStyle = 'position:absolute;bottom:-4px;right:-4px;z-index:25;';
            } else if (position === 'bottom-left') {
                posStyle = 'position:absolute;bottom:-4px;left:-4px;z-index:25;';
            }

            if (b.type === 'svg_code') {
                return '<div class="ff-badge" style="' + posStyle + 'width:' + size + ';height:' + size + ';user-select:none;" title="' + b.title + '">' + b.svgCode + '</div>';
            }

            if (b.type === 'text_code') {
                return '<span class="ff-badge" style="' + posStyle + 'user-select:none;" title="' + b.title + '">' + b.textCode + '</span>';
            }

            const imgUrl = this.getImageUrl(b.file);
            const filterStyle = 'filter:drop-shadow(0 0 6px ' + b.glowColor + ') drop-shadow(0 2px 4px rgba(0,0,0,0.9));';

            return '<span class="ff-shape-badge" style="' + posStyle + 'width:' + size + ';height:' + size + ';' + filterStyle + 'user-select:none;" title="' + b.title + '">\n' +
                   '  <img src="' + imgUrl + '" alt="' + b.name + '" style="width:100%;height:100%;object-fit:contain;display:block;background:transparent;border:none;">\n' +
                   '  <span class="ff-badge-shine" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;-webkit-mask-image:url(\'' + imgUrl + '\');mask-image:url(\'' + imgUrl + '\');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">\n' +
                   '    <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.2) 44%,rgba(255,255,255,0.95) 50%,rgba(255,255,255,0.2) 56%,transparent 65%);animation:ff_shape_sweep ' + speed + ' infinite linear;mix-blend-mode:screen;display:block;"></span>\n' +
                   '  </span>\n' +
                   '</span>';
        },

        attachTo: function(container, badgeKey, options) {
            const el = (typeof container === 'string') ? document.querySelector(container) : container;
            if (!el) return null;
            if (getComputedStyle(el).position === 'static') {
                el.style.position = 'relative';
            }
            const wrapper = document.createElement('div');
            wrapper.innerHTML = this.render(badgeKey, options);
            const badgeEl = wrapper.firstElementChild;
            el.appendChild(badgeEl);
            return badgeEl;
        }
    };

    // Custom Web Component: <ff-badge name="official-v" size="34" position="inline"></ff-badge>
    if (typeof customElements !== 'undefined' && !customElements.get('ff-badge')) {
        class FFBadgeElement extends HTMLElement {
            static get observedAttributes() {
                return ['name', 'type', 'size', 'position', 'speed'];
            }

            connectedCallback() {
                this.render();
            }

            attributeChangedCallback() {
                this.render();
            }

            render() {
                const name = this.getAttribute('name') || this.getAttribute('type') || 'official-v';
                const size = this.getAttribute('size') || undefined;
                const position = this.getAttribute('position') || 'inline';
                const speed = this.getAttribute('speed') || '2.6s';

                this.innerHTML = FFBadges.render(name, { size, position, speed });
            }
        }
        customElements.define('ff-badge', FFBadgeElement);
    }

    // Auto-inject CSS stylesheet if not present
    if (typeof document !== 'undefined') {
        const hasCss = document.querySelector('link[href*="ff-badges"], style[id="ff-badges-style"]');
        if (!hasCss) {
            const style = document.createElement('style');
            style.id = 'ff-badges-style';
            style.textContent = '@keyframes ff_shape_sweep{0%{transform:translate(-100%,-100%) rotate(45deg)}100%{transform:translate(100%,100%) rotate(45deg)}}@keyframes pulse_gold{0%,100%{filter:drop-shadow(0 0 2px gold) drop-shadow(0 2px 4px rgba(0,0,0,0.8))}50%{filter:drop-shadow(0 0 10px gold) drop-shadow(0 0 16px rgba(255,215,0,0.6))}}@keyframes fire_text{from{text-shadow:0 0 2px #ff8c00,0 0 5px #ff4500}to{text-shadow:0 0 6px #ff4500,0 0 14px #ff8c00,0 0 20px #ff2200}}@keyframes glow_legend{0%,100%{filter:drop-shadow(0 0 3px #8e2de2) drop-shadow(0 1px 3px rgba(0,0,0,0.9))}50%{filter:drop-shadow(0 0 12px #a855f7) drop-shadow(0 0 20px rgba(168,85,247,0.7))}}.ff-shape-badge,.ff-badge{position:relative;display:inline-flex;align-items:center;justify-content:center;background:transparent!important;border:none!important;user-select:none;transition:transform .25s ease}.ff-shape-badge:hover,.ff-badge:hover{transform:scale(1.15)}';
            document.head.appendChild(style);
        }
    }

    window.FFBadges = FFBadges;

})(typeof window !== 'undefined' ? window : this, typeof document !== 'undefined' ? document : null);
