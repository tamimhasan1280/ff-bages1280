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
      const defaultCdnAssets = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/';
      const isLocalHost = (typeof window !== 'undefined' && /^(localhost|127\.0\.0\.1)$/i.test(window.location.hostname || ''));
      this.assetPath = options.assetPath ? (options.assetPath.replace(/\/+$/, '') + '/') : (isLocalHost ? 'badges/' : defaultCdnAssets);
      this.cdnFallbackPath = defaultCdnAssets;
      this.enableAnim = (options.anim !== false && options.anim !== 'off' && options.anim !== 'false');
      this.enablePulse = this.enableAnim && (options.pulse !== false && options.pulse !== 'off' && options.pulse !== 'false');
      this.enableGlow = (options.glow !== false && options.glow !== 'off' && options.glow !== 'false');
      this.enableTilt = options.tilt !== undefined ? options.tilt : (this.enableAnim && (this.size === 'lg' || this.size === 'xl'));
      
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
      const animCls = this.enableAnim ? (this.enablePulse ? '' : ' ff-pulse-off') : ' ff-anim-off';
      this.wrapper.className = `ff-prime-badge-wrap ff-prime-lvl-${this.level} ${sizeClass}${animCls}`;
      
      if (!sizeClass && this.size) {
        this.wrapper.style.width = this.size;
        this.wrapper.style.height = this.size;
      }

      // 1. Pinpoint Diamond Stars Layer (Levels 4 to 8)
      if (this.enableAnim && this.level >= 4) {
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
      this.img.onerror = () => {
        this.img.onerror = null;
        this.img.src = `${this.cdnFallbackPath}${fileName}`;
      };
      this.img.src = fileFullPath;
      this.img.alt = `Free Fire Prime Level ${this.level}`;
      this.img.className = 'ff-badge-img';
      if (!this.enableGlow) {
        this.img.style.filter = 'none';
      }
      if (!this.enablePulse) {
        this.img.style.animation = 'none';
      }
      this.wrapper.appendChild(this.img);

      // 3. In-Game Verify Badge Metallic Sheen Sweep (Levels 4 to 8)
      if (this.enableAnim && this.level >= 4) {
        const shine = document.createElement('span');
        shine.className = 'ff-badge-shine';
        shine.style.cssText = `position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${fileFullPath}');mask-image:url('${fileFullPath}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;`;
        const beam = document.createElement('span');
        beam.className = 'ff-badge-shine-beam';
        shine.appendChild(beam);
        this.wrapper.appendChild(shine);
      }

      // 4. Center Gem Specular Glint (Levels 7 & 8)
      if (this.enableAnim && this.level >= 7) {
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
      const anim = el.getAttribute('data-anim');
      const pulse = el.getAttribute('data-pulse');
      const glow = el.getAttribute('data-glow');
      new PrimeBadge(el, {
        level,
        size,
        anim: anim !== null ? (anim !== 'off' && anim !== 'false' && anim !== '0') : undefined,
        pulse: pulse !== null ? (pulse !== 'off' && pulse !== 'false' && pulse !== '0') : undefined,
        glow: glow !== null ? (glow !== 'off' && glow !== 'false' && glow !== '0') : undefined
      });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', global.initPrimeBadges);
  } else {
    global.initPrimeBadges();
  }
})(typeof window !== 'undefined' ? window : this);
