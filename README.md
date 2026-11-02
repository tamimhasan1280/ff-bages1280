# 🏆 Free Fire V-Badges Monster CDN & API

> **The Ultimate High-Definition Free Fire Verified Badges, Influencer V-Badges, and Tournament Crowns CDN & REST API.**
> Engineered for zero-dependency global CDN delivery, ultra-crisp vector-like rendering, and signature light-sweep animations.

---

## ⚡ Global CDN Endpoints

Include the universal stylesheet & JavaScript SDK directly into any HTML or web application:

```html
<!-- 1. Universal Animated Badge Stylesheet -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/cdn/ff-badges.css">

<!-- 2. Zero-Dependency Web Component SDK -->
<script src="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/cdn/ff-badges.js"></script>
```

---

## 🚀 3 Ways to Use

### 1. Web Component (Simplest)
Use the custom `<ff-badge>` tag anywhere in your HTML:
```html
<ff-badge name="official-v" size="34" position="inline"></ff-badge>
<ff-badge name="diamond-partner" size="28" position="top-right"></ff-badge>
<ff-badge name="esports-crown" size="34"></ff-badge>
```

### 2. Pure CSS Animated HTML
If you prefer zero JavaScript, copy the native CSS mask light-sweep snippet:
```html
<span class="ff-shape-badge ff-glow-gold" style="position:relative;width:34px;height:34px;display:inline-flex;">
  <img src="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/FF_UI_Badge_KOL01.png" alt="OFFICIAL V BAGE">
  <span class="ff-badge-shine" style="-webkit-mask-image:url('https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/FF_UI_Badge_KOL01.png');mask-image:url('https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/FF_UI_Badge_KOL01.png');">
    <span class="ff-badge-shine-beam"></span>
  </span>
</span>
```

### 3. JSON REST API
Fetch the full badge catalog, priorities, and CDN URLs directly in your backend (PHP, Node.js, Python, Flutter, Go):
```javascript
fetch('https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/api/badges.json')
  .then(res => res.json())
  .then(data => {
    console.log(data.badges);
  });
```

---

## 🎖️ Complete Badges Catalog

| Badge Name | Category | Priority | Glow Color | CDN Filename |
| :--- | :--- | :--- | :--- | :--- |
| **OFFICIAL V BAGE** | Official Partner (KOL) | `2,000,000` | Golden Glow | `FF_UI_Badge_KOL01.png` |
| **FF Diamond Partner** | Crystal VIP Partner | `1,500,000` | Violet Crystal | `FF_UI_Badge_KOL03.png` |
| **FF Esports PRO Crown** | Esports & Competitor | `95,000` | Molten Amber | `FF_UI_Badge_ESPORTS01.png` |
| **FF Official Staff / GM** | Administration & Staff | `90,000` | Authority Red | `FF_UI_Badge_GM01.png` |
| **FF Craftland Creator** | Map Architect & Builder | `85,000` | Cyan Neon | `craftland.png` |
| **FF Emerging Creator** | Rising Streamer & Tier-2 | `80,000` | Emerald Green | `FF_UI_Badge_KOL02.png` |
| **Classic Gold V-Badge** | Legacy Classics | `75,000` | Warm Gold | `vbadge.png` |
| **Vector SVG V-Badge** | Scalable Vector | `70,000` | Pure Gold | `vbadge.svg` |
| **MODERETOR** | Community Security | `10,000` | Shield Gold | `verified-badge.svg` |
| **PRO-PLYER** | Ranked Competitive | `1,000` | Fire Text | *CSS Molten Typography* |
| **Legendary** | Exclusive Diamond | `95` | Cosmic Purple | *Prismatic SVG Vector* |
| **HUD Alive Badge** | In-Game Battle HUD | `500` | Teal | `badge_alive_teal.png` |
| **HUD Kill Badge** | In-Game Battle HUD | `500` | Maroon | `badge_kill_maroon.png` |

---

## 📦 How to Push to Your GitHub

Follow these simple steps in PowerShell or Command Prompt:

```bash
cd "F:\users\Pictures\Downloads\ff all vages"
git remote add origin https://github.com/tamimhasan1280/ff-bages1280.git
git push -u origin main
```

Once pushed, your badges will instantly be served across the globe via jsDelivr CDN!

---

## 📄 License
MIT License &bull; Free to use across personal and commercial esports projects.
