/**
 * Free Fire Prime System - All-in-One Zero-Load CDN Web Component
 * 100% Transparent Background - Pure Client-Side Zero Server Load
 */
(function(window, document) {
  'use strict';

  // 1. Auto-inject Transparent CSS Stylesheet into <head>
  const css = "\/**\n * Free Fire Prime System - 1:1 In-Game Authentic Visual FX (OB55 Engine)\n * Extracted directly from Free Fire in-game lobby footage:\n *  1. Energetic Side Wing Flares (left & right wings emit blazing golden aura pulses)\n *  2. Crisp Pinpoint Diamond Star Sparkles (distinct 4-point vector stars popping & twinkling)\n *  3. In-Game Verify Badge Metallic Sheen Sweep (Alpha mask clipped directly to badge silhouette)\n *  4. Center Gem Glint & Wing Breathing\n *  5. 100% Transparent Background (Zero ugly yellow circles or background diagonal bars)\n *\/\n\n\/* ========================================================\n   MAIN BADGE WRAPPER (100% Transparent)\n   ======================================================== *\/\n.ff-prime-badge-wrap {\n  position: relative;\n  display: inline-flex;\n  align-items: center;\n  justify-content: center;\n  background: transparent !important;\n  user-select: none;\n  -webkit-user-select: none;\n  vertical-align: middle;\n  overflow: visible;\n}\n\n\/* Sizing Presets *\/\n.ff-prime-sm { width: 44px; height: 44px; }\n.ff-prime-md { width: 68px; height: 68px; }\n.ff-prime-lg { width: 110px; height: 110px; }\n.ff-prime-xl { width: 180px; height: 180px; }\n\n\/* The Silhouette Badge Image (Z-Index 5) *\/\n.ff-prime-badge-wrap .ff-badge-img {\n  width: 100%;\n  height: 100%;\n  object-fit: contain;\n  position: relative;\n  z-index: 5;\n  background: transparent !important;\n  pointer-events: none;\n  display: block;\n  transition: transform 0.3s ease, filter 0.3s ease;\n}\n\n\/* Remove old bulky elements completely *\/\n.ff-prime-badge-wrap .ff-core-aura,\n.ff-prime-badge-wrap .ff-energy-stream,\n.ff-prime-badge-wrap .ff-energy-beam,\n.ff-prime-badge-wrap .ff-prime-sparks-layer,\n.ff-prime-badge-wrap .ff-particle-canvas {\n  display: none !important;\n}\n\n\/* ========================================================\n   \u2605 1. ENERGETIC SIDE WING FLARES (\"\u09b8\u09be\u0987\u09a1 \u09a5\u09c7\u0995\u09c7 \u09b8\u09c1\u09a8\u09cd\u09a6\u09b0 \u0987\u09ab\u09c7\u0995\u09cd\u099f\") \u2605\n   Left & Right wings emit radiant golden fire aura pulses\n   ======================================================== *\/\n.ff-prime-badge-wrap .ff-side-wing-flare {\n  position: absolute;\n  width: 42%;\n  height: 60%;\n  top: 18%;\n  pointer-events: none;\n  z-index: 3;\n  mix-blend-mode: screen;\n  filter: blur(3px);\n  opacity: 0;\n  transition: opacity 0.3s ease;\n}\n\n.ff-prime-lvl-5 .ff-side-wing-flare,\n.ff-prime-lvl-6 .ff-side-wing-flare,\n.ff-prime-lvl-7 .ff-side-wing-flare,\n.ff-prime-lvl-8 .ff-side-wing-flare {\n  opacity: 1;\n}\n\n\/* Left Wing Flare *\/\n.ff-prime-badge-wrap .ff-side-wing-flare.left {\n  left: -8%;\n  background: radial-gradient(\n    ellipse at 75% 75%,\n    rgba(255, 245, 180, 0.95) 0%,\n    rgba(255, 196, 0, 0.75) 35%,\n    rgba(251, 133, 0, 0.35) 60%,\n    transparent 80%\n  );\n  transform-origin: bottom right;\n  animation: ffWingFlareLeft 2.2s infinite ease-in-out alternate;\n}\n\n\/* Right Wing Flare *\/\n.ff-prime-badge-wrap .ff-side-wing-flare.right {\n  right: -8%;\n  background: radial-gradient(\n    ellipse at 25% 75%,\n    rgba(255, 245, 180, 0.95) 0%,\n    rgba(255, 196, 0, 0.75) 35%,\n    rgba(251, 133, 0, 0.35) 60%,\n    transparent 80%\n  );\n  transform-origin: bottom left;\n  animation: ffWingFlareRight 2.2s infinite ease-in-out alternate;\n}\n\n@keyframes ffWingFlareLeft {\n  0% {\n    transform: rotate(-20deg) scale(0.92);\n    opacity: 0.5;\n  }\n  50% {\n    transform: rotate(-28deg) scale(1.18) translateY(-2px);\n    opacity: 0.95;\n    filter: blur(2.5px) brightness(1.35);\n  }\n  100% {\n    transform: rotate(-22deg) scale(0.98);\n    opacity: 0.6;\n  }\n}\n\n@keyframes ffWingFlareRight {\n  0% {\n    transform: rotate(20deg) scale(0.92);\n    opacity: 0.5;\n  }\n  50% {\n    transform: rotate(28deg) scale(1.18) translateY(-2px);\n    opacity: 0.95;\n    filter: blur(2.5px) brightness(1.35);\n  }\n  100% {\n    transform: rotate(22deg) scale(0.98);\n    opacity: 0.6;\n  }\n}\n\n\/* Level 4 Cyan Wing Flares *\/\n.ff-prime-lvl-4 .ff-side-wing-flare.left {\n  background: radial-gradient(ellipse at 75% 75%, rgba(220, 250, 255, 0.95) 0%, rgba(0, 180, 216, 0.75) 40%, transparent 75%);\n  animation: ffWingFlareLeft 2.8s infinite ease-in-out alternate;\n  opacity: 0.8;\n}\n.ff-prime-lvl-4 .ff-side-wing-flare.right {\n  background: radial-gradient(ellipse at 25% 75%, rgba(220, 250, 255, 0.95) 0%, rgba(0, 180, 216, 0.75) 40%, transparent 75%);\n  animation: ffWingFlareRight 2.8s infinite ease-in-out alternate;\n  opacity: 0.8;\n}\n\n\/* ========================================================\n   \u2605 2. PINPOINT 4-POINT DIAMOND STARS (\"\u09b8\u09cd\u09aa\u09b7\u09cd\u099f \u09a4\u09be\u09b0\u09be \u09ab\u09c1\u099f\u09ac\u09c7\") \u2605\n   Sharp, crisp vector diamond stars bursting directly on badge tips\n   ======================================================== *\/\n.ff-prime-stars-layer {\n  position: absolute;\n  inset: 0;\n  pointer-events: none;\n  z-index: 6;\n  overflow: visible;\n  background: transparent !important;\n}\n\n.ff-pinpoint-star {\n  position: absolute;\n  pointer-events: none;\n  transform: translate(-50%, -50%) scale(0);\n  opacity: 0;\n  mix-blend-mode: screen;\n  z-index: 6;\n  filter: drop-shadow(0 0 3px #ffffff) drop-shadow(0 0 6px #ffd700);\n}\n\n@keyframes ffStarSparkleBurst {\n  0% {\n    transform: translate(-50%, -50%) scale(0) rotate(0deg);\n    opacity: 0;\n  }\n  14% {\n    transform: translate(-50%, -50%) scale(1.35) rotate(20deg);\n    opacity: 1;\n    filter: drop-shadow(0 0 4px #ffffff) drop-shadow(0 0 8px #ffd700) brightness(1.6);\n  }\n  32% {\n    transform: translate(-50%, -50%) scale(0.92) rotate(45deg);\n    opacity: 0.95;\n    filter: drop-shadow(0 0 3px #ffffff) drop-shadow(0 0 6px #ffd700) brightness(1.25);\n  }\n  55% {\n    transform: translate(-50%, -50%) scale(1.1) rotate(65deg);\n    opacity: 0.8;\n  }\n  78% {\n    transform: translate(-50%, -50%) scale(0.2) rotate(90deg);\n    opacity: 0;\n  }\n  100% {\n    transform: translate(-50%, -50%) scale(0) rotate(90deg);\n    opacity: 0;\n  }\n}\n\n\/* Distinct, tight positions anchored directly on the badge emblem & wings *\/\n.star-pos-1 { top: 26%; left: 14%; width: 13px; height: 13px; animation: ffStarSparkleBurst 1.8s infinite 0.1s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-2 { top: 26%; left: 86%; width: 13px; height: 13px; animation: ffStarSparkleBurst 1.9s infinite 0.65s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-3 { top: 5%; left: 50%; width: 14px; height: 14px; animation: ffStarSparkleBurst 1.7s infinite 1.2s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-4 { top: 56%; left: 10%; width: 12px; height: 12px; animation: ffStarSparkleBurst 2.0s infinite 0.4s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-5 { top: 56%; left: 90%; width: 12px; height: 12px; animation: ffStarSparkleBurst 1.8s infinite 1.45s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-6 { top: 78%; left: 24%; width: 11px; height: 11px; animation: ffStarSparkleBurst 1.6s infinite 0.9s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-7 { top: 78%; left: 76%; width: 11px; height: 11px; animation: ffStarSparkleBurst 2.1s infinite 1.7s cubic-bezier(0.16, 1, 0.3, 1); }\n.star-pos-8 { top: 52%; left: 50%; width: 15px; height: 15px; animation: ffStarSparkleBurst 2.3s infinite 0.25s cubic-bezier(0.16, 1, 0.3, 1); z-index: 7; }\n\n\/* Level 4 Cyan Stars *\/\n.ff-prime-lvl-4 .ff-pinpoint-star {\n  filter: drop-shadow(0 0 3px #ffffff) drop-shadow(0 0 6px #00b4d8);\n}\n\n\/* ========================================================\n   \u2605 3. VERIFY BADGE METALLIC SHEEN MASK SWEEP (Z-Index 7) \u2605\n   The iconic Free Fire in-game light sheen clipped to the badge\n   ======================================================== *\/\n.ff-prime-badge-wrap .ff-badge-shine {\n  position: absolute;\n  inset: 0;\n  pointer-events: none;\n  overflow: hidden;\n  z-index: 7;\n  -webkit-mask-size: contain;\n  mask-size: contain;\n  -webkit-mask-repeat: no-repeat;\n  mask-repeat: no-repeat;\n  -webkit-mask-position: center;\n  mask-position: center;\n}\n\n.ff-prime-badge-wrap .ff-badge-shine-beam {\n  position: absolute;\n  top: -100%;\n  left: -100%;\n  width: 300%;\n  height: 300%;\n  background: linear-gradient(\n    45deg,\n    transparent 35%,\n    rgba(255, 255, 255, 0.2) 44%,\n    rgba(255, 255, 255, 0.95) 50%,\n    rgba(255, 255, 255, 0.2) 56%,\n    transparent 65%\n  );\n  animation: ff_shape_sweep 2.6s infinite linear;\n  mix-blend-mode: screen;\n  display: block;\n}\n\n@keyframes ff_shape_sweep {\n  0% { transform: translate(-100%, -100%) rotate(45deg); }\n  100% { transform: translate(100%, 100%) rotate(45deg); }\n}\n\n\/* ========================================================\n   \u2605 4. CENTER GEM STAR GLINT (Z-Index 8) \u2605\n   ======================================================== *\/\n.ff-prime-badge-wrap .ff-gem-glint {\n  position: absolute;\n  top: 48%;\n  left: 50%;\n  width: 18px;\n  height: 18px;\n  transform: translate(-50%, -50%) scale(0);\n  background: radial-gradient(circle, rgba(255, 255, 255, 1) 0%, rgba(255, 235, 120, 0.85) 35%, rgba(255, 183, 3, 0.3) 65%, transparent 75%);\n  border-radius: 50%;\n  pointer-events: none;\n  z-index: 8;\n  opacity: 0;\n  mix-blend-mode: screen;\n}\n\n.ff-prime-badge-wrap .ff-gem-glint::before,\n.ff-prime-badge-wrap .ff-gem-glint::after {\n  content: '';\n  position: absolute;\n  top: 50%;\n  left: 50%;\n  transform: translate(-50%, -50%);\n  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.98) 50%, transparent);\n  border-radius: 50%;\n}\n.ff-prime-badge-wrap .ff-gem-glint::before {\n  width: 180%;\n  height: 20%;\n}\n.ff-prime-badge-wrap .ff-gem-glint::after {\n  width: 20%;\n  height: 180%;\n}\n\n@keyframes ffGemSparkleStar {\n  0%, 70% {\n    opacity: 0;\n    transform: translate(-50%, -50%) scale(0.1) rotate(0deg);\n  }\n  78% {\n    opacity: 1;\n    transform: translate(-50%, -50%) scale(1.15) rotate(45deg);\n  }\n  86% {\n    opacity: 0.65;\n    transform: translate(-50%, -50%) scale(1.3) rotate(90deg);\n  }\n  94%, 100% {\n    opacity: 0;\n    transform: translate(-50%, -50%) scale(1.4) rotate(135deg);\n  }\n}\n\n\/* ========================================================\n   LEVEL-SPECIFIC CONFIGURATIONS (LEVEL 1 TO LEVEL 8)\n   Direct Contour Emission (Zero Background Blobs)\n   ======================================================== *\/\n\n\/* Level 1, 2, 3: Clean Metallic Badges *\/\n.ff-prime-lvl-1 .ff-badge-img,\n.ff-prime-lvl-2 .ff-badge-img,\n.ff-prime-lvl-3 .ff-badge-img {\n  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));\n  animation: none !important;\n}\n\n\/* Level 4: Cyan Crystal (Crisp contour) *\/\n.ff-prime-lvl-4 .ff-badge-img {\n  filter: \n    drop-shadow(0 0 5px rgba(0, 180, 216, 0.75)) \n    drop-shadow(0 0 10px rgba(0, 119, 182, 0.4)) \n    drop-shadow(0 2px 4px rgba(0, 0, 0, 0.6));\n}\n\n\/* Level 5: Radiant Gold Halo & Wing Pulse *\/\n.ff-prime-lvl-5 .ff-badge-img {\n  animation: ffWingsPulseSoft 3s ease-in-out infinite alternate;\n  filter: \n    drop-shadow(0 0 5px rgba(255, 183, 3, 0.75)) \n    drop-shadow(0 0 12px rgba(245, 158, 11, 0.4)) \n    drop-shadow(0 2px 5px rgba(0, 0, 0, 0.65));\n}\n\n@keyframes ffWingsPulseSoft {\n  0% { transform: scale(1); }\n  100% { transform: scale(1.025) translateY(-1px); }\n}\n\n\/* Level 6: Amber Flame Wings *\/\n.ff-prime-lvl-6 .ff-badge-img {\n  animation: ffWingsPulseSoft 2.8s ease-in-out infinite alternate;\n  filter: \n    drop-shadow(0 0 6px rgba(251, 133, 0, 0.8)) \n    drop-shadow(0 0 12px rgba(255, 183, 3, 0.45)) \n    drop-shadow(0 2px 5px rgba(0, 0, 0, 0.7));\n}\n\n\/* Level 7: Grandmaster Mythic (Star Twinkle & Crisp Gold Aura) *\/\n.ff-prime-lvl-7 .ff-gem-glint {\n  animation: ffGemSparkleStar 3.8s ease-in-out infinite;\n}\n.ff-prime-lvl-7 .ff-badge-img {\n  animation: ffWingsMythicSoft 2.6s ease-in-out infinite alternate;\n  filter: \n    drop-shadow(0 0 6px rgba(255, 215, 0, 0.85)) \n    drop-shadow(0 0 14px rgba(251, 133, 0, 0.45)) \n    drop-shadow(0 3px 6px rgba(0, 0, 0, 0.75));\n}\n\n@keyframes ffWingsMythicSoft {\n  0% { transform: scale(1) translateY(0); }\n  100% { transform: scale(1.03) translateY(-1.5px); }\n}\n\n\/* Level 8: SUPREME GOD TIER (Verify Sheen + Side Wing Flares + 8 Pinpoint Stars) *\/\n.ff-prime-lvl-8 .ff-gem-glint {\n  animation: ffGemSparkleStar 2.8s ease-in-out infinite;\n}\n\n.ff-prime-lvl-8 .ff-badge-img {\n  animation: ffWingsLegendarySoft 2.4s ease-in-out infinite alternate;\n  filter: \n    drop-shadow(0 0 6px rgba(255, 235, 120, 0.95)) \n    drop-shadow(0 0 14px rgba(255, 196, 0, 0.65)) \n    drop-shadow(0 0 20px rgba(251, 133, 0, 0.35)) \n    drop-shadow(0 3px 6px rgba(0, 0, 0, 0.75));\n}\n\n@keyframes ffWingsLegendarySoft {\n  0% { \n    transform: scale(1) translateY(0); \n  }\n  100% { \n    transform: scale(1.035) translateY(-2px); \n  }\n}\n\n\/* Profile Tag Shimmer *\/\n.ff-prime-profile-tag {\n  display: inline-block;\n  background: transparent !important;\n  vertical-align: middle;\n  filter: drop-shadow(0 0 6px rgba(255, 215, 0, 0.8));\n  animation: ffTagPulseSoft 2.2s infinite alternate;\n}\n@keyframes ffTagPulseSoft {\n  from { filter: drop-shadow(0 0 4px rgba(255, 196, 0, 0.5)); transform: scale(1); }\n  to { filter: drop-shadow(0 0 10px rgba(255, 235, 100, 0.95)); transform: scale(1.02); }\n}\n";
  if (!document.getElementById('ff-prime-badge-styles')) {
    const styleEl = document.createElement('style');
    styleEl.id = 'ff-prime-badge-styles';
    styleEl.textContent = css;
    document.head.appendChild(styleEl);
  }

  // 2. Prime Badge Logic
  /**
 * Free Fire Prime Badge - Refined Transparent VFX Engine
 * Natural In-Badge Emission with Right-to-Upward Ascending Energy Stream
 */

(function (global) {
  class PrimeBadge {
    constructor(target, options = {}) {
      this.container = typeof target === 'string' ? document.querySelector(target) : target;
      if (!this.container) return;

      this.level = Math.max(1, Math.min(8, parseInt(options.level) || 8));
      this.size = options.size || 'md';
      this.assetPath = (options.assetPath || './').replace(/\/+$/, '') + '/';
      this.enableTilt = options.tilt !== undefined ? options.tilt : (this.size === 'lg' || this.size === 'xl');
      
      this.particles = [];
      this.animId = null;
      this.canvas = null;
      this.ctx = null;

      this.render();
    }

    render() {
      this.container.innerHTML = '';
      
      // Wrapper
      this.wrapper = document.createElement('div');
      const sizeClass = ['sm', 'md', 'lg', 'xl'].includes(this.size) ? `ff-prime-${this.size}` : '';
      this.wrapper.className = `ff-prime-badge-wrap ff-prime-lvl-${this.level} ${sizeClass}`;
      
      if (!sizeClass && this.size) {
        this.wrapper.style.width = this.size;
        this.wrapper.style.height = this.size;
      }

      // 1. Energetic Side Wing Flares (Levels 4 to 8)
      if (this.level >= 4) {
        const flareLeft = document.createElement('div');
        flareLeft.className = 'ff-side-wing-flare left';
        const flareRight = document.createElement('div');
        flareRight.className = 'ff-side-wing-flare right';
        this.wrapper.appendChild(flareLeft);
        this.wrapper.appendChild(flareRight);
      }

      // 2. Pinpoint Diamond Stars Layer (Levels 4 to 8)
      if (this.level >= 4) {
        const starsLayer = document.createElement('div');
        starsLayer.className = 'ff-prime-stars-layer';
        
        let starList = [];
        if (this.level === 8) starList = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-5', 'star-pos-6', 'star-pos-7', 'star-pos-8'];
        else if (this.level === 7) starList = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-6', 'star-pos-7'];
        else if (this.level === 6) starList = ['star-pos-1', 'star-pos-2', 'star-pos-4', 'star-pos-5', 'star-pos-6'];
        else if (this.level === 5) starList = ['star-pos-1', 'star-pos-2', 'star-pos-6', 'star-pos-7'];
        else if (this.level === 4) starList = ['star-pos-1', 'star-pos-2'];

        starList.forEach(pos => {
          const starSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
          starSvg.setAttribute('class', `ff-pinpoint-star ${pos}`);
          starSvg.setAttribute('viewBox', '0 0 24 24');
          starSvg.innerHTML = '<path d="M12 0 L14.2 9.2 L24 12 L14.2 14.8 L12 24 L9.8 14.8 L0 12 L9.8 9.2 Z" fill="#FFF8B0"/><circle cx="12" cy="12" r="2.5" fill="#FFFFFF"/>';
          starsLayer.appendChild(starSvg);
        });
        this.wrapper.appendChild(starsLayer);
      }

      // 2. Badge Silhouette Image (Level 1 to 8)
      const fileName = `ff_ui_prime_icon_0${this.level}.png`;
      const fileFullPath = `${this.assetPath}${fileName}`;
      this.img = document.createElement('img');
      this.img.src = fileFullPath;
      this.img.alt = `Free Fire Prime Level ${this.level}`;
      this.img.className = 'ff-badge-img';
      this.wrapper.appendChild(this.img);

      // 3. In-Game Verify Badge Metallic Sheen Sweep (Levels 4 to 8)
      if (this.level >= 4) {
        const shine = document.createElement('span');
        shine.className = 'ff-badge-shine';
        shine.style.cssText = `position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${fileFullPath}');mask-image:url('${fileFullPath}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;`;
        const beam = document.createElement('span');
        beam.className = 'ff-badge-shine-beam';
        shine.appendChild(beam);
        this.wrapper.appendChild(shine);
      }

      // 4. Center Gem Specular Glint (Levels 7 & 8)
      if (this.level >= 7) {
        const glint = document.createElement('div');
        glint.className = 'ff-gem-glint';
        this.wrapper.appendChild(glint);
      }

      this.container.appendChild(this.wrapper);

      if (this.enableTilt) this.setupTilt();
      if (this.canvas) this.setupParticles();
    }

    setupTilt() {
      this.wrapper.addEventListener('mousemove', (e) => {
        const rect = this.wrapper.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;
        const rotateX = (-y / rect.height) * 16;
        const rotateY = (x / rect.width) * 16;
        this.img.style.transform = `perspective(600px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.04)`;
      });

      this.wrapper.addEventListener('mouseleave', () => {
        this.img.style.transform = 'perspective(600px) rotateX(0deg) rotateY(0deg) scale(1)';
      });
    }

    setupParticles() {
      const resize = () => {
        if (!this.canvas) return;
        this.canvas.width = this.wrapper.clientWidth * 1.8;
        this.canvas.height = this.wrapper.clientHeight * 1.8;
      };
      resize();

      const count = this.level === 8 ? 16 : (this.level === 7 ? 8 : 4);
      this.particles = [];

      for (let i = 0; i < count; i++) {
        this.particles.push(this.createParticle());
      }

      const animate = () => {
        if (!this.ctx || !this.canvas) return;
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        this.particles.forEach(p => {
          p.y -= p.speedY;
          p.x += p.speedX + Math.sin(p.life * 0.08) * 0.3;
          p.life++;
          p.alpha = Math.max(0, 1 - (p.life / p.maxLife));

          if (p.life >= p.maxLife || p.y < 0) {
            Object.assign(p, this.createParticle());
          }

          this.ctx.save();
          this.ctx.beginPath();
          this.ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
          this.ctx.fillStyle = p.color + p.alpha + ')';
          this.ctx.shadowColor = p.color + '0.8)';
          this.ctx.shadowBlur = 6;
          this.ctx.fill();
          this.ctx.restore();
        });

        this.animId = requestAnimationFrame(animate);
      };

      animate();
    }

    createParticle() {
      const w = this.canvas ? this.canvas.width : 160;
      const h = this.canvas ? this.canvas.height : 160;
      const colors = ['rgba(255, 235, 120, ', 'rgba(255, 200, 50, ', 'rgba(255, 160, 20, '];

      return {
        x: (w / 2) + (Math.random() - 0.5) * (w * 0.38),
        y: (h * 0.58) + (Math.random() - 0.5) * (h * 0.2),
        size: Math.random() * (this.level === 8 ? 1.6 : 1.2) + 0.8,
        speedY: Math.random() * 1.0 + 0.5,
        speedX: (Math.random() - 0.5) * 0.6,
        life: Math.random() * 25,
        maxLife: Math.random() * 60 + 40,
        alpha: 1,
        color: colors[Math.floor(Math.random() * colors.length)]
      };
    }

    setLevel(newLevel) {
      this.destroy();
      this.level = Math.max(1, Math.min(8, parseInt(newLevel) || 8));
      this.render();
    }

    destroy() {
      if (this.animId) cancelAnimationFrame(this.animId);
      this.particles = [];
      this.container.innerHTML = '';
    }
  }

  global.PrimeBadge = PrimeBadge;
  global.initPrimeBadges = function () {
    document.querySelectorAll('[data-ff-prime]').forEach(el => {
      const level = el.getAttribute('data-ff-prime');
      const size = el.getAttribute('data-size') || 'md';
      new PrimeBadge(el, { level, size });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', global.initPrimeBadges);
  } else {
    global.initPrimeBadges();
  }
})(typeof window !== 'undefined' ? window : this);


})(typeof window !== 'undefined' ? window : this, typeof document !== 'undefined' ? document : {});