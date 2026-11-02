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

      // 1. Core Aura (Levels 4 to 8)
      if (this.level >= 4) {
        const aura = document.createElement('div');
        aura.className = 'ff-core-aura';
        this.wrapper.appendChild(aura);
      }

      // 2. Micro-Embers Canvas (Levels 6 to 8)
      if (this.level >= 6) {
        this.canvas = document.createElement('canvas');
        this.canvas.className = 'ff-particle-canvas';
        this.wrapper.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d');
      }

      // 3. Badge Image (Level 1 to 8)
      const fileName = `ff_ui_prime_icon_0${this.level}.png`;
      this.img = document.createElement('img');
      this.img.src = `${this.assetPath}${fileName}`;
      this.img.alt = `Free Fire Prime Level ${this.level}`;
      this.img.className = 'ff-badge-img';
      this.wrapper.appendChild(this.img);

      // 4. Natural Gem Specular Glint (Levels 7 & 8)
      if (this.level >= 7) {
        const glint = document.createElement('div');
        glint.className = 'ff-gem-glint';
        this.wrapper.appendChild(glint);
      }

      // 5. Ascending Golden Energy Stream (Level 8: Right-to-Upward flowing beam)
      if (this.level >= 8) {
        const stream = document.createElement('div');
        stream.className = 'ff-energy-stream';
        const beam = document.createElement('div');
        beam.className = 'ff-energy-beam';
        stream.appendChild(beam);
        this.wrapper.appendChild(stream);
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
