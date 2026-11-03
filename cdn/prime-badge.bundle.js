/**
 * Free Fire Prime System - All-in-One Zero-Load CDN Web Component
 * 100% Transparent Background - Pure Client-Side Zero Server Load
 */
(function(window, document) {
  'use strict';

  // 1. Auto-inject Transparent CSS Stylesheet into <head>
  const css = "\/**\r\n * Free Fire Prime System - 1:1 In-Game Authentic Visual FX (OB55 Engine)\r\n * Features:\r\n *  1. ZERO Yellow Background Blobs (100% Transparent, No side yellow halos, No background streams)\r\n *  2. Crackling Gunpowder Micro-Sparkles (\"\u09ac\u09be\u09b0\u09c1\u09a6 \u09b8\u09cd\u099f\u09be\u09b0 \u0987\u09ab\u09c7\u0995\u09cd\u099f\" - tiny, crisp, rapid popping diamond stars)\r\n *  3. In-Game Verify Badge Metallic Sheen Sweep (with customizable angles & directions)\r\n *  4. Center Gem Star Flare & Wing Pulse\r\n *\/\r\n\r\n\/* ========================================================\r\n   MAIN BADGE WRAPPER (100% Transparent)\r\n   ======================================================== *\/\r\n.ff-prime-badge-wrap {\r\n  position: relative;\r\n  display: inline-flex;\r\n  align-items: center;\r\n  justify-content: center;\r\n  background: transparent !important;\r\n  user-select: none;\r\n  -webkit-user-select: none;\r\n  vertical-align: middle;\r\n  overflow: visible;\r\n}\r\n\r\n\/* Sizing Presets *\/\r\n.ff-prime-sm { width: 44px; height: 44px; }\r\n.ff-prime-md { width: 64px; height: 64px; }\r\n.ff-prime-lg { width: 96px; height: 96px; }\r\n.ff-prime-xl { width: 140px; height: 140px; }\r\n\r\n\/* The Silhouette Badge Image (Z-Index 5) *\/\r\n.ff-prime-badge-wrap .ff-badge-img {\r\n  width: 100%;\r\n  height: 100%;\r\n  object-fit: contain;\r\n  position: relative;\r\n  z-index: 5;\r\n  background: transparent !important;\r\n  pointer-events: none;\r\n  display: block;\r\n  transition: transform 0.3s ease, filter 0.3s ease;\r\n}\r\n\r\n\/* STRICTLY REMOVE all yellow circular backgrounds, blurry side flares, and streams *\/\r\n.ff-prime-badge-wrap .ff-core-aura,\r\n.ff-prime-badge-wrap .ff-energy-stream,\r\n.ff-prime-badge-wrap .ff-energy-beam,\r\n.ff-prime-badge-wrap .ff-prime-sparks-layer,\r\n.ff-prime-badge-wrap .ff-particle-canvas,\r\n.ff-prime-badge-wrap .ff-side-wing-flare {\r\n  display: none !important;\r\n}\r\n\r\n\/* ========================================================\r\n   \u2605 1. GUNPOWDER MICRO-STARS (\"\u09ac\u09be\u09b0\u09c1\u09a6 \u09b8\u09cd\u099f\u09be\u09b0 \u0987\u09ab\u09c7\u0995\u09cd\u099f\") \u2605\r\n   Tiny, pinpoint, razor-sharp 4-point diamond sparkles\r\n   ======================================================== *\/\r\n.ff-prime-stars-layer {\r\n  position: absolute;\r\n  inset: 0;\r\n  pointer-events: none;\r\n  z-index: 6;\r\n  overflow: visible;\r\n  background: transparent !important;\r\n}\r\n\r\n.ff-pinpoint-star {\r\n  position: absolute;\r\n  pointer-events: none;\r\n  transform: translate(-50%, -50%) scale(0);\r\n  opacity: 0;\r\n  mix-blend-mode: screen;\r\n  z-index: 6;\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 4px #ffd700);\r\n}\r\n\r\n\/* Gunpowder spark rapid burst keyframes *\/\r\n@keyframes ffGunpowderSpark {\r\n  0% {\r\n    transform: translate(-50%, -50%) scale(0) rotate(0deg);\r\n    opacity: 0;\r\n  }\r\n  12% {\r\n    transform: translate(-50%, -50%) scale(1.15) rotate(15deg);\r\n    opacity: 1;\r\n    filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 3px #ffd700) brightness(1.6);\r\n  }\r\n  28% {\r\n    transform: translate(-50%, -50%) scale(0.8) rotate(35deg);\r\n    opacity: 0.95;\r\n    filter: drop-shadow(0 0 1.5px #ffffff) drop-shadow(0 0 3px #ffd700) brightness(1.3);\r\n  }\r\n  50% {\r\n    transform: translate(-50%, -50%) scale(0.95) rotate(60deg);\r\n    opacity: 0.8;\r\n  }\r\n  75% {\r\n    transform: translate(-50%, -50%) scale(0.15) rotate(90deg);\r\n    opacity: 0;\r\n  }\r\n  100% {\r\n    transform: translate(-50%, -50%) scale(0) rotate(90deg);\r\n    opacity: 0;\r\n  }\r\n}\r\n\r\n\/* 50 Precise Gunpowder Micro-Star Positions (Encompassing entire badge perimeter & wings) *\/\r\n.star-pos-1 { top: 3%; left: 50%; width: 10px; height: 10px; animation: ffGunpowderSpark 1.4s infinite 0.05s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-2 { top: 10%; left: 50%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.6s infinite 0.45s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-3 { top: 7%; left: 42%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 0.85s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-4 { top: 7%; left: 58%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 1.25s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-5 { top: 14%; left: 34%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.7s infinite 0.35s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-6 { top: 14%; left: 66%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.7s infinite 0.95s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-7 { top: 22%; left: 14%; width: 9px; height: 9px; animation: ffGunpowderSpark 1.5s infinite 0.15s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-8 { top: 22%; left: 86%; width: 9px; height: 9px; animation: ffGunpowderSpark 1.5s infinite 0.65s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-9 { top: 18%; left: 22%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.8s infinite 1.15s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-10 { top: 18%; left: 78%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.8s infinite 0.55s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-11 { top: 26%; left: 8%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.4s infinite 0.75s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-12 { top: 26%; left: 92%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.4s infinite 1.35s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-13 { top: 32%; left: 18%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.6s infinite 0.25s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-14 { top: 32%; left: 82%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.6s infinite 0.85s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-15 { top: 28%; left: 28%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.5s infinite 0.4s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-16 { top: 28%; left: 72%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.5s infinite 1.05s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-17 { top: 45%; left: 6%; width: 9px; height: 9px; animation: ffGunpowderSpark 1.8s infinite 0.1s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-18 { top: 45%; left: 94%; width: 9px; height: 9px; animation: ffGunpowderSpark 1.8s infinite 0.7s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-19 { top: 40%; left: 15%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 1.3s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-20 { top: 40%; left: 85%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 0.5s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-21 { top: 52%; left: 10%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.7s infinite 0.9s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-22 { top: 52%; left: 90%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.7s infinite 0.3s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-23 { top: 50%; left: 50%; width: 11px; height: 11px; animation: ffGunpowderSpark 2.1s infinite 0.2s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-24 { top: 44%; left: 44%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.4s infinite 0.6s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-25 { top: 44%; left: 56%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.4s infinite 1.1s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-26 { top: 56%; left: 50%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.6s infinite 1.5s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-27 { top: 60%; left: 20%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.6s infinite 0.35s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-28 { top: 60%; left: 80%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.6s infinite 0.95s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-29 { top: 68%; left: 16%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 1.45s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-30 { top: 68%; left: 84%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 0.65s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-31 { top: 65%; left: 30%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.7s infinite 0.15s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-32 { top: 65%; left: 70%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.7s infinite 0.75s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-33 { top: 72%; left: 24%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.4s infinite 1.05s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-34 { top: 72%; left: 76%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.4s infinite 0.45s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-35 { top: 75%; left: 35%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.6s infinite 0.85s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-36 { top: 75%; left: 65%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.6s infinite 1.35s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-37 { top: 82%; left: 30%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 0.2s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-38 { top: 82%; left: 70%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 0.8s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-39 { top: 86%; left: 42%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.7s infinite 1.2s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-40 { top: 86%; left: 58%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.7s infinite 0.4s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-41 { top: 92%; left: 50%; width: 9px; height: 9px; animation: ffGunpowderSpark 1.8s infinite 1.0s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-42 { top: 95%; left: 45%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.4s infinite 0.6s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-43 { top: 95%; left: 55%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.4s infinite 1.4s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-44 { top: 80%; left: 50%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.5s infinite 0.7s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-45 { top: 0%; left: 50%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.5s infinite 0.3s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-46 { top: 12%; left: 10%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.8s infinite 1.1s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-47 { top: 12%; left: 90%; width: 6px; height: 6px; animation: ffGunpowderSpark 1.8s infinite 0.5s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-48 { top: 58%; left: 2%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.6s infinite 1.25s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-49 { top: 58%; left: 98%; width: 7px; height: 7px; animation: ffGunpowderSpark 1.6s infinite 0.75s cubic-bezier(0.16, 1, 0.3, 1); }\r\n.star-pos-50 { top: 98%; left: 50%; width: 8px; height: 8px; animation: ffGunpowderSpark 1.7s infinite 0.15s cubic-bezier(0.16, 1, 0.3, 1); }\r\n\r\n\r\n\/* Star Color Themes *\/\r\n.star-theme-gold .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #ffd700);\r\n}\r\n.star-theme-cyan .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #00b4d8);\r\n}\r\n.star-theme-white .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #ffffff);\r\n}\r\n.star-theme-ruby .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #ef4444);\r\n}\r\n.star-theme-purple .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #c084fc);\r\n}\r\n.star-theme-emerald .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #10b981);\r\n}\r\n.star-theme-amber .ff-pinpoint-star {\r\n  filter: drop-shadow(0 0 2px #ffffff) drop-shadow(0 0 5px #f59e0b);\r\n}\r\n.star-theme-rainbow .ff-pinpoint-star {\r\n  animation: ffGunpowderSpark 1.6s infinite cubic-bezier(0.16, 1, 0.3, 1), ffRainbowHue 4s linear infinite;\r\n}\r\n@keyframes ffRainbowHue {\r\n  0% { filter: hue-rotate(0deg) drop-shadow(0 0 4px #ff007f); }\r\n  50% { filter: hue-rotate(180deg) drop-shadow(0 0 4px #00f0ff); }\r\n  100% { filter: hue-rotate(360deg) drop-shadow(0 0 4px #ff007f); }\r\n}\r\n\r\n\/* ========================================================\r\n   \u2605 2. VERIFY METALLIC SHEEN MASK SWEEP (Z-Index 7) \u2605\r\n   Clipped directly to badge silhouette with direction support\r\n   ======================================================== *\/\r\n.ff-prime-badge-wrap .ff-badge-shine {\r\n  position: absolute;\r\n  inset: 0;\r\n  pointer-events: none;\r\n  overflow: hidden;\r\n  z-index: 7;\r\n  -webkit-mask-size: contain;\r\n  mask-size: contain;\r\n  -webkit-mask-repeat: no-repeat;\r\n  mask-repeat: no-repeat;\r\n  -webkit-mask-position: center;\r\n  mask-position: center;\r\n}\r\n\r\n.ff-prime-badge-wrap .ff-badge-shine-beam {\r\n  position: absolute;\r\n  top: -100%;\r\n  left: -100%;\r\n  width: 300%;\r\n  height: 300%;\r\n  background: linear-gradient(\r\n    45deg,\r\n    transparent 35%,\r\n    rgba(255, 255, 255, 0.2) 44%,\r\n    rgba(255, 255, 255, 0.98) 50%,\r\n    rgba(255, 255, 255, 0.2) 56%,\r\n    transparent 65%\r\n  );\r\n  animation: ff_shape_sweep 2.6s infinite linear;\r\n  mix-blend-mode: screen;\r\n  display: block;\r\n}\r\n\r\n\r\n\/* 8 Authentic Directional Sheen Sweep Styles *\/\r\n\/* 1. Diagonal: Bottom-Left to Top-Right (45deg) - Classic FF in-game *\/\r\n.sweep-dir-diag .ff-badge-shine-beam,\r\n.sweep-dir-bl-tr .ff-badge-shine-beam {\r\n  background: linear-gradient(45deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_bl_tr;\r\n}\r\n@keyframes ff_sweep_bl_tr {\r\n  0% { transform: translate(-100%, 100%) rotate(45deg); }\r\n  100% { transform: translate(100%, -100%) rotate(45deg); }\r\n}\r\n\r\n\/* 2. Diagonal: Bottom-Right to Top-Left (-45deg) *\/\r\n.sweep-dir-diag-rev .ff-badge-shine-beam,\r\n.sweep-dir-br-tl .ff-badge-shine-beam {\r\n  background: linear-gradient(-45deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_br_tl;\r\n}\r\n@keyframes ff_sweep_br_tl {\r\n  0% { transform: translate(100%, 100%) rotate(-45deg); }\r\n  100% { transform: translate(-100%, -100%) rotate(-45deg); }\r\n}\r\n\r\n\/* 3. Diagonal: Top-Left to Bottom-Right (135deg) *\/\r\n.sweep-dir-tl-br .ff-badge-shine-beam {\r\n  background: linear-gradient(135deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_tl_br;\r\n}\r\n@keyframes ff_sweep_tl_br {\r\n  0% { transform: translate(-100%, -100%) rotate(135deg); }\r\n  100% { transform: translate(100%, 100%) rotate(135deg); }\r\n}\r\n\r\n\/* 4. Diagonal: Top-Right to Bottom-Left (-135deg) *\/\r\n.sweep-dir-tr-bl .ff-badge-shine-beam {\r\n  background: linear-gradient(-135deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_tr_bl;\r\n}\r\n@keyframes ff_sweep_tr_bl {\r\n  0% { transform: translate(100%, -100%) rotate(-135deg); }\r\n  100% { transform: translate(-100%, 100%) rotate(-135deg); }\r\n}\r\n\r\n\/* 5. Vertical: Bottom to Top (0deg) *\/\r\n.sweep-dir-up .ff-badge-shine-beam {\r\n  background: linear-gradient(0deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_up;\r\n}\r\n@keyframes ff_sweep_up {\r\n  0% { transform: translateY(100%); }\r\n  100% { transform: translateY(-100%); }\r\n}\r\n\r\n\/* 6. Vertical: Top to Bottom (180deg) *\/\r\n.sweep-dir-down .ff-badge-shine-beam {\r\n  background: linear-gradient(180deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_down;\r\n}\r\n@keyframes ff_sweep_down {\r\n  0% { transform: translateY(-100%); }\r\n  100% { transform: translateY(100%); }\r\n}\r\n\r\n\/* 7. Horizontal: Left to Right (90deg) *\/\r\n.sweep-dir-right .ff-badge-shine-beam {\r\n  background: linear-gradient(90deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_right;\r\n}\r\n@keyframes ff_sweep_right {\r\n  0% { transform: translateX(-100%); }\r\n  100% { transform: translateX(100%); }\r\n}\r\n\r\n\/* 8. Horizontal: Right to Left (270deg) *\/\r\n.sweep-dir-left .ff-badge-shine-beam {\r\n  background: linear-gradient(270deg, transparent 35%, rgba(255,255,255,0.2) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.2) 56%, transparent 65%);\r\n  animation-name: ff_sweep_left;\r\n}\r\n@keyframes ff_sweep_left {\r\n  0% { transform: translateX(100%); }\r\n  100% { transform: translateX(-100%); }\r\n}\r\n\r\n\/* ========================================================\r\n   \u2605 3. CENTER GEM STAR GLINT (Z-Index 8) \u2605\r\n   ======================================================== *\/\r\n.ff-prime-badge-wrap .ff-gem-glint {\r\n  position: absolute;\r\n  top: 48%;\r\n  left: 50%;\r\n  width: 16px;\r\n  height: 16px;\r\n  transform: translate(-50%, -50%) scale(0);\r\n  background: radial-gradient(circle, rgba(255, 255, 255, 1) 0%, rgba(255, 235, 120, 0.85) 35%, rgba(255, 183, 3, 0.3) 65%, transparent 75%);\r\n  border-radius: 50%;\r\n  pointer-events: none;\r\n  z-index: 8;\r\n  opacity: 0;\r\n  mix-blend-mode: screen;\r\n}\r\n\r\n.ff-prime-badge-wrap .ff-gem-glint::before,\r\n.ff-prime-badge-wrap .ff-gem-glint::after {\r\n  content: '';\r\n  position: absolute;\r\n  top: 50%;\r\n  left: 50%;\r\n  transform: translate(-50%, -50%);\r\n  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.98) 50%, transparent);\r\n  border-radius: 50%;\r\n}\r\n.ff-prime-badge-wrap .ff-gem-glint::before {\r\n  width: 180%;\r\n  height: 18%;\r\n}\r\n.ff-prime-badge-wrap .ff-gem-glint::after {\r\n  width: 18%;\r\n  height: 180%;\r\n}\r\n\r\n@keyframes ffGemSparkleStar {\r\n  0%, 70% {\r\n    opacity: 0;\r\n    transform: translate(-50%, -50%) scale(0.1) rotate(0deg);\r\n  }\r\n  78% {\r\n    opacity: 1;\r\n    transform: translate(-50%, -50%) scale(1.15) rotate(45deg);\r\n  }\r\n  86% {\r\n    opacity: 0.65;\r\n    transform: translate(-50%, -50%) scale(1.3) rotate(90deg);\r\n  }\r\n  94%, 100% {\r\n    opacity: 0;\r\n    transform: translate(-50%, -50%) scale(1.4) rotate(135deg);\r\n  }\r\n}\r\n\r\n\/* ========================================================\r\n   LEVEL-SPECIFIC CONFIGURATIONS (LEVEL 1 TO LEVEL 8)\r\n   Clean Silhouette Drop-Shadow Contour (Zero Background Blobs)\r\n   ======================================================== *\/\r\n\r\n\/* Level 1, 2, 3: Clean Metallic Badges *\/\r\n.ff-prime-lvl-1 .ff-badge-img,\r\n.ff-prime-lvl-2 .ff-badge-img,\r\n.ff-prime-lvl-3 .ff-badge-img {\r\n  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));\r\n  animation: none !important;\r\n}\r\n\r\n\/* Level 4: Cyan Crystal (Crisp contour) *\/\r\n.ff-prime-lvl-4 .ff-badge-img {\r\n  filter: \r\n    drop-shadow(0 0 4px rgba(0, 180, 216, 0.75)) \r\n    drop-shadow(0 0 8px rgba(0, 119, 182, 0.4)) \r\n    drop-shadow(0 2px 4px rgba(0, 0, 0, 0.6));\r\n}\r\n\r\n\/* Level 5: Radiant Gold Contour & Wing Pulse *\/\r\n.ff-prime-lvl-5 .ff-badge-img {\r\n  animation: ffWingsPulseSoft 3s ease-in-out infinite alternate;\r\n  filter: \r\n    drop-shadow(0 0 3px rgba(255, 183, 3, 0.75)) \r\n    drop-shadow(0 2px 4px rgba(0, 0, 0, 0.7));\r\n}\r\n\r\n@keyframes ffWingsPulseSoft {\r\n  0% { transform: scale(1); }\r\n  100% { transform: scale(1.025) translateY(-1px); }\r\n}\r\n\r\n\/* Level 6: Amber Flame Wings *\/\r\n.ff-prime-lvl-6 .ff-badge-img {\r\n  animation: ffWingsPulseSoft 2.8s ease-in-out infinite alternate;\r\n  filter: \r\n    drop-shadow(0 0 3px rgba(251, 133, 0, 0.8)) \r\n    drop-shadow(0 2px 4px rgba(0, 0, 0, 0.75));\r\n}\r\n\r\n\/* Level 7: Grandmaster Mythic (Star Twinkle & Crisp Gold Aura) *\/\r\n.ff-prime-lvl-7 .ff-gem-glint {\r\n  animation: ffGemSparkleStar 3.8s ease-in-out infinite;\r\n}\r\n.ff-prime-lvl-7 .ff-badge-img {\r\n  animation: ffWingsMythicSoft 2.6s ease-in-out infinite alternate;\r\n  filter: \r\n    drop-shadow(0 0 3px rgba(255, 215, 0, 0.85)) \r\n    drop-shadow(0 2px 4px rgba(0, 0, 0, 0.8));\r\n}\r\n\r\n@keyframes ffWingsMythicSoft {\r\n  0% { transform: scale(1) translateY(0); }\r\n  100% { transform: scale(1.03) translateY(-1.5px); }\r\n}\r\n\r\n\/* Level 8: SUPREME GOD TIER (Verify Sheen + 16 Gunpowder Stars + Center Gem Flare - 100% Transparent Background) *\/\r\n.ff-prime-lvl-8 .ff-gem-glint {\r\n  animation: ffGemSparkleStar 2.8s ease-in-out infinite;\r\n}\r\n\r\n.ff-prime-lvl-8 .ff-badge-img {\r\n  animation: ffWingsLegendarySoft 2.4s ease-in-out infinite alternate;\r\n  filter: \r\n    drop-shadow(0 0 3px rgba(255, 235, 120, 0.85)) \r\n    drop-shadow(0 2px 4px rgba(0, 0, 0, 0.85));\r\n}\r\n\r\n@keyframes ffWingsLegendarySoft {\r\n  0% { \r\n    transform: scale(1) translateY(0); \r\n  }\r\n  100% { \r\n    transform: scale(1.035) translateY(-2px); \r\n  }\r\n}\r\n\r\n\/* Profile Tag Shimmer *\/\r\n.ff-prime-profile-tag {\r\n  display: inline-block;\r\n  background: transparent !important;\r\n  vertical-align: middle;\r\n  filter: drop-shadow(0 0 5px rgba(255, 215, 0, 0.8));\r\n  animation: ffTagPulseSoft 2.2s infinite alternate;\r\n}\r\n@keyframes ffTagPulseSoft {\r\n  from { filter: drop-shadow(0 0 3px rgba(255, 196, 0, 0.5)); transform: scale(1); }\r\n  to { filter: drop-shadow(0 0 8px rgba(255, 235, 100, 0.95)); transform: scale(1.02); }\r\n}\r\n";
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

      // 1. Pinpoint Diamond Stars Layer (Levels 4 to 8)
      if (this.level >= 4) {
        const starsLayer = document.createElement('div');
        starsLayer.className = 'ff-prime-stars-layer';
        
        let starList = [];
        if (this.level === 8) starList = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-5', 'star-pos-6', 'star-pos-7', 'star-pos-8', 'star-pos-9', 'star-pos-10', 'star-pos-11', 'star-pos-12'];
        else if (this.level === 7) starList = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-6', 'star-pos-7', 'star-pos-8', 'star-pos-9'];
        else if (this.level === 6) starList = ['star-pos-1', 'star-pos-2', 'star-pos-4', 'star-pos-5', 'star-pos-6', 'star-pos-8'];
        else if (this.level === 5) starList = ['star-pos-1', 'star-pos-2', 'star-pos-6', 'star-pos-7'];
        else if (this.level === 4) starList = ['star-pos-1', 'star-pos-2'];

        starList.forEach(pos => {
          const starSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
          starSvg.setAttribute('class', `ff-pinpoint-star ${pos}`);
          starSvg.setAttribute('viewBox', '0 0 24 24');
          starSvg.innerHTML = '<path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="#FFFCE0"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/>';
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