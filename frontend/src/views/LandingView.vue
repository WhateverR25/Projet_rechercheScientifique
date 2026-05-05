<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Placeholder video URL — easy to swap
const VIDEO_URL = 'https://www.w3schools.com/html/mov_bbb.mp4'

// Navbar scroll state
const navScrolled = ref(false)

// Video state
const videoRef = ref(null)
const videoPaused = ref(true)
const videoVisible = ref(false)

// Scroll handler for navbar
const onScroll = () => {
  navScrolled.value = window.scrollY > 80
}

// IntersectionObserver for scroll animations
let observer = null

const initObserver = () => {
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible')
          observer.unobserve(entry.target)
        }
      })
    },
    { threshold: 0.15 }
  )

  document.querySelectorAll('.fade-up').forEach((el) => {
    observer.observe(el)
  })
}

// IntersectionObserver for video autoplay
let videoObserver = null

const initVideoObserver = () => {
  if (!videoRef.value) return
  videoObserver = new IntersectionObserver(
    ([entry]) => {
      videoVisible.value = entry.isIntersecting
      if (entry.isIntersecting) {
        videoRef.value.play()
        videoPaused.value = false
      } else {
        videoRef.value.pause()
        videoPaused.value = true
      }
    },
    { threshold: 0.4 }
  )
  videoObserver.observe(videoRef.value)
}

const toggleVideo = () => {
  if (!videoRef.value) return
  if (videoRef.value.paused) {
    videoRef.value.play()
    videoPaused.value = false
  } else {
    videoRef.value.pause()
    videoPaused.value = true
  }
}

const scrollToVideo = () => {
  const el = document.getElementById('video-section')
  if (el) el.scrollIntoView({ behavior: 'smooth' })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  // Delay observer init to ensure DOM is ready
  requestAnimationFrame(() => {
    initObserver()
    initVideoObserver()
  })
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  if (observer) observer.disconnect()
  if (videoObserver) videoObserver.disconnect()
})

const features = [
  {
    icon: '📊',
    title: 'Tableau de bord intelligent',
    desc: 'Visualisez vos finances en temps réel avec des graphiques clairs et intuitifs.',
    color: '#c9a84c'
  },
  {
    icon: '💸',
    title: 'Suivi des transactions',
    desc: 'Catégorisez chaque dépense et revenu en quelques secondes.',
    color: '#3b82f6'
  },
  {
    icon: '🎯',
    title: 'Budgets personnalisés',
    desc: 'Définissez vos limites mensuelles et recevez des alertes avant de les dépasser.',
    color: '#10b981'
  }
]

const stats = [
  { value: '100% Privé', label: 'Vos données restent chez vous' },
  { value: '0€', label: 'Gratuit pour commencer' },
  { value: '5 min', label: 'Pour configurer votre compte' }
]
</script>

<template>
  <div class="landing-page">
    <!-- ═══════════ NAVBAR ═══════════ -->
    <nav class="landing-nav" :class="{ scrolled: navScrolled }">
      <div class="nav-inner">
        <router-link to="/" class="nav-logo">
          <img src="/aurem-logo.png" alt="Aurem" class="nav-logo-img" />
        </router-link>
        <div class="nav-actions">
          <router-link to="/login" class="nav-btn nav-btn-ghost" id="landing-login-btn">
            Se connecter
          </router-link>
          <router-link to="/register" class="nav-btn nav-btn-filled" id="landing-register-btn">
            S'inscrire
          </router-link>
        </div>
      </div>
    </nav>

    <!-- ═══════════ HERO ═══════════ -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="hero-content fade-up">
        <h1 class="hero-title">Dépensez moins.<br />Épargnez plus.</h1>
        <p class="hero-subtitle">
          Aurem vous donne une vision claire et élégante de vos finances personnelles.
        </p>
        <div class="hero-ctas">
          <router-link to="/register" class="hero-btn hero-btn-filled" id="hero-cta-register">
            Commencer gratuitement
          </router-link>
          <button class="hero-btn hero-btn-ghost" @click="scrollToVideo" id="hero-cta-video">
            Voir comment ça marche
          </button>
        </div>
        <p class="hero-proof">✦ Gérez vos revenus, dépenses et budgets en un seul endroit</p>
      </div>
    </section>

    <!-- ═══════════ FEATURES ═══════════ -->
    <section class="features">
      <div class="features-grid">
        <div
          v-for="(f, i) in features"
          :key="i"
          class="feature-card fade-up"
          :style="{ '--delay': i * 120 + 'ms' }"
        >
          <div class="feature-icon" :style="{ background: f.color + '18', color: f.color }">
            <span>{{ f.icon }}</span>
          </div>
          <h3 class="feature-title">{{ f.title }}</h3>
          <p class="feature-desc">{{ f.desc }}</p>
        </div>
      </div>
    </section>

    <!-- ═══════════ VIDEO DEMO ═══════════ -->
    <section class="video-section fade-up" id="video-section">
      <h2 class="section-heading">Voyez Aurem en action</h2>
      <div class="video-wrapper" @click="toggleVideo">
        <video
          ref="videoRef"
          :src="VIDEO_URL"
          muted
          loop
          playsinline
          preload="metadata"
          class="video-player"
        ></video>
        <div class="video-overlay" :class="{ hidden: !videoPaused }">
          <div class="play-btn">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="rgba(26,35,50,0.7)" />
              <polygon points="20,15 35,24 20,33" fill="white" />
            </svg>
          </div>
        </div>
      </div>
      <p class="video-footer-text">
        Aucune carte bancaire requise · Données 100% privées · Accès immédiat
      </p>
    </section>

    <!-- ═══════════ STATS BAR ═══════════ -->
    <section class="stats-bar fade-up">
      <div class="stats-grid">
        <div v-for="(s, i) in stats" :key="i" class="stat-item">
          <span class="stat-value">{{ s.value }}</span>
          <span class="stat-label">{{ s.label }}</span>
        </div>
      </div>
    </section>

    <!-- ═══════════ FINAL CTA ═══════════ -->
    <section class="final-cta fade-up">
      <h2 class="final-cta-heading">Prêt à reprendre le contrôle ?</h2>
      <router-link to="/register" class="final-cta-btn" id="final-cta-btn">
        Créer mon compte gratuitement
      </router-link>
    </section>

    <!-- ═══════════ FOOTER ═══════════ -->
    <footer class="landing-footer">
      <div class="footer-inner">
        <img src="/aurem-logo.png" alt="Aurem" class="footer-logo" />
        <span class="footer-copy">© 2026 Aurem. Tous droits réservés.</span>
        <router-link to="/login" class="footer-link" id="footer-login-link">Connexion</router-link>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ─────────────────────────────────────────
   DESIGN TOKENS
   ───────────────────────────────────────── */
:root {
  --navy: #1a2332;
  --offwhite: #f5f3ef;
  --gold: #c9a84c;
}

.landing-page {
  --navy: #1a2332;
  --offwhite: #f5f3ef;
  --gold: #c9a84c;
  background: var(--offwhite);
  color: var(--navy);
  overflow-x: hidden;
  min-height: 100vh;
  position: relative;
}

/* ─────────────────────────────────────────
   SCROLL ANIMATION
   ───────────────────────────────────────── */
.fade-up {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.5s ease var(--delay, 0ms),
              transform 0.5s ease var(--delay, 0ms);
}
.fade-up.is-visible {
  opacity: 1;
  transform: translateY(0);
}

/* ─────────────────────────────────────────
   NAVBAR
   ───────────────────────────────────────── */
.landing-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  padding: 0 40px;
  height: 100px;
  display: flex;
  align-items: center;
  transition: background 0.35s ease, box-shadow 0.35s ease;
}
.landing-nav.scrolled {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(12px);
  box-shadow: 0 1px 12px rgba(0, 0, 0, 0.06);
}
.nav-inner {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.nav-logo-img {
  height: 90px;
  width: auto;
  object-fit: contain;
}
.nav-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}
.nav-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 42px;
  padding: 0 22px;
  border-radius: 999px;
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.01em;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  text-decoration: none;
}
.nav-btn-ghost {
  background: transparent;
  color: var(--navy);
  border: 1.5px solid var(--navy);
}
.nav-btn-ghost:hover {
  background: var(--navy);
  color: white;
}
.nav-btn-filled {
  background: var(--navy);
  color: white;
  border: 1.5px solid var(--navy);
}
.nav-btn-filled:hover {
  background: #243044;
  border-color: #243044;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(26, 35, 50, 0.25);
}

/* ─────────────────────────────────────────
   HERO
   ───────────────────────────────────────── */
.hero {
  position: relative;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 120px 24px 80px;
  overflow: hidden;
}
.hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 60% 50% at 50% 40%, rgba(201, 168, 76, 0.10) 0%, transparent 70%),
    radial-gradient(ellipse 80% 60% at 30% 70%, rgba(201, 168, 76, 0.06) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 80% 20%, rgba(26, 35, 50, 0.04) 0%, transparent 60%);
  pointer-events: none;
}
.hero-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(201, 168, 76, 0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(201, 168, 76, 0.04) 1px, transparent 1px);
  background-size: 60px 60px;
  mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 20%, transparent 70%);
  -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 20%, transparent 70%);
}
.hero-content {
  position: relative;
  max-width: 720px;
}
.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: 56px;
  font-weight: 700;
  line-height: 1.15;
  color: var(--navy);
  margin-bottom: 20px;
  letter-spacing: -0.5px;
}
.hero-subtitle {
  font-family: 'Inter', sans-serif;
  font-size: 18px;
  font-weight: 400;
  color: #6b7280;
  line-height: 1.6;
  margin-bottom: 36px;
}
.hero-ctas {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 28px;
}
.hero-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 48px;
  padding: 0 32px;
  border-radius: 999px;
  font-family: 'Inter', sans-serif;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  text-decoration: none;
  border: none;
}
.hero-btn-filled {
  background: var(--navy);
  color: white;
}
.hero-btn-filled:hover {
  background: #243044;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26, 35, 50, 0.25);
}
.hero-btn-ghost {
  background: transparent;
  color: var(--navy);
  border: 1.5px solid rgba(26, 35, 50, 0.25);
}
.hero-btn-ghost:hover {
  border-color: var(--navy);
  background: rgba(26, 35, 50, 0.04);
}
.hero-proof {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: var(--gold);
  font-weight: 500;
  letter-spacing: 0.02em;
}

/* ─────────────────────────────────────────
   FEATURES
   ───────────────────────────────────────── */
.features {
  padding: 80px 24px 100px;
  max-width: 1100px;
  margin: 0 auto;
}
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 28px;
}
.feature-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 16px;
  padding: 32px 28px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
}
.feature-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  margin-bottom: 20px;
}
.feature-title {
  font-family: 'Inter', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 10px;
}
.feature-desc {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: #6b7280;
  line-height: 1.6;
}

/* ─────────────────────────────────────────
   VIDEO SECTION
   ───────────────────────────────────────── */
.video-section {
  padding: 60px 24px 80px;
  max-width: 900px;
  margin: 0 auto;
  text-align: center;
}
.section-heading {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 40px;
}
.video-wrapper {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.10);
  cursor: pointer;
  background: #000;
  aspect-ratio: 16 / 9;
}
.video-player {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.video-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.15);
  transition: opacity 0.3s ease;
}
.video-overlay.hidden {
  opacity: 0;
  pointer-events: none;
}
.play-btn {
  transition: transform 0.2s ease;
}
.video-wrapper:hover .play-btn {
  transform: scale(1.1);
}
.video-footer-text {
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  color: #9ca3af;
  margin-top: 20px;
  letter-spacing: 0.01em;
}

/* ─────────────────────────────────────────
   STATS BAR
   ───────────────────────────────────────── */
.stats-bar {
  background: var(--navy);
  padding: 64px 24px;
  margin-top: 40px;
}
.stats-grid {
  max-width: 900px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  text-align: center;
}
.stat-item {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.stat-value {
  font-family: 'Playfair Display', serif;
  font-size: 32px;
  font-weight: 700;
  color: var(--gold);
}
.stat-label {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  color: rgba(255, 255, 255, 0.65);
}

/* ─────────────────────────────────────────
   FINAL CTA
   ───────────────────────────────────────── */
.final-cta {
  padding: 100px 24px;
  text-align: center;
}
.final-cta-heading {
  font-family: 'Playfair Display', serif;
  font-size: 40px;
  font-weight: 700;
  color: var(--navy);
  margin-bottom: 32px;
}
.final-cta-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 52px;
  padding: 0 40px;
  background: var(--navy);
  color: white;
  border-radius: 999px;
  font-family: 'Inter', sans-serif;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.final-cta-btn:hover {
  background: #243044;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26, 35, 50, 0.25);
}

/* ─────────────────────────────────────────
   FOOTER
   ───────────────────────────────────────── */
.landing-footer {
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  padding: 28px 40px;
}
.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.footer-logo {
  height: 36px;
  width: auto;
  object-fit: contain;
}
.footer-copy {
  font-family: 'Inter', sans-serif;
  font-size: 13px;
  color: #9ca3af;
}
.footer-link {
  font-family: 'Inter', sans-serif;
  font-size: 14px;
  font-weight: 500;
  color: var(--navy);
  text-decoration: none;
  transition: color 0.2s;
}
.footer-link:hover {
  color: var(--gold);
}

/* ─────────────────────────────────────────
   RESPONSIVE
   ───────────────────────────────────────── */
@media (max-width: 900px) {
  .features-grid {
    grid-template-columns: 1fr;
    max-width: 420px;
    margin: 0 auto;
  }
  .hero-title {
    font-size: 40px;
  }
  .section-heading {
    font-size: 28px;
  }
  .final-cta-heading {
    font-size: 30px;
  }
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }
}

@media (max-width: 600px) {
  .landing-nav {
    padding: 0 16px;
    height: 80px;
  }
  .nav-logo-img {
    height: 60px;
  }
  .nav-btn {
    height: 36px;
    padding: 0 14px;
    font-size: 13px;
  }
  .hero {
    padding: 100px 20px 60px;
  }
  .hero-title {
    font-size: 32px;
  }
  .hero-subtitle {
    font-size: 16px;
  }
  .hero-btn {
    height: 44px;
    padding: 0 24px;
    font-size: 14px;
  }
  .hero-ctas {
    flex-direction: column;
    align-items: center;
  }
  .footer-inner {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
  .video-section {
    padding: 40px 16px 60px;
  }
  .final-cta {
    padding: 60px 20px;
  }
  .stat-value {
    font-size: 26px;
  }
}
</style>
