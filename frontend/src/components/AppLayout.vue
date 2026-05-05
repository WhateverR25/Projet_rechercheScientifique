<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()

const showLogoutModal = ref(false)

const confirmLogout = async () => {
  try { await api.post('/auth/logout') } catch {}
  localStorage.removeItem('budgetwise_token')
  localStorage.removeItem('budgetwise_user')
  router.push('/login')
}
</script>

<template>
  <main class="dashboard-page">
    <aside class="sidebar">
      <div class="logo">
        <img src="/aurem-logo.png" alt="Aurem" class="sidebar-logo-img" />
      </div>
      <nav class="nav-menu">
        <router-link to="/dashboard" :class="{ active: route.name === 'dashboard' }">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
          {{ t('dashboard') }}
        </router-link>
        <router-link to="/transactions" :class="{ active: route.name === 'transactions' }">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
          {{ t('transactions') }}
        </router-link>
        <router-link to="/budgets" :class="{ active: route.name === 'budgets' }">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
          {{ t('budgets') }}
        </router-link>
        <router-link to="/reports" :class="{ active: route.name === 'reports' }">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"></path></svg>
          {{ t('reports') }}
        </router-link>
        <router-link to="/settings" :class="{ active: route.name === 'settings' }">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
          {{ t('settings') }}
        </router-link>
        <a href="#" @click.prevent="showLogoutModal = true" class="logout-link">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          {{ t('logout') }}
        </a>
      </nav>
      <div class="sidebar-footer">
        <p>© 2026 Aurem</p>
      </div>
    </aside>

    <section class="content">
      <router-view v-slot="{ Component }">
        <transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </section>

    <nav class="mobile-nav">
      <router-link to="/dashboard" :class="{ active: route.name === 'dashboard' }">{{ t('dashboard') }}</router-link>
      <router-link to="/transactions" :class="{ active: route.name === 'transactions' }">{{ t('transactions') }}</router-link>
      <router-link to="/budgets" :class="{ active: route.name === 'budgets' }">{{ t('budgets') }}</router-link>
      <router-link to="/reports" :class="{ active: route.name === 'reports' }">{{ t('reports') }}</router-link>
      <router-link to="/settings" :class="{ active: route.name === 'settings' }">{{ t('settings') }}</router-link>
    </nav>

    <div class="modal-overlay" v-if="showLogoutModal" @click.self="showLogoutModal = false">
      <div class="modal-content">
        <div class="modal-icon-wrapper">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <h3 class="modal-title">Êtes-vous sûr de vouloir vous déconnecter ?</h3>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showLogoutModal = false">Annuler</button>
          <button class="btn-confirm" @click="confirmLogout">Se déconnecter</button>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
/* ─── Sidebar Logo ─── */
.sidebar-logo-img {
  height: 90px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.06));
}

/* ─── Inner-page transition (Linear/Stripe-inspired) ─── */
.page-fade-enter-active {
  transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1),
              transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.page-fade-leave-active {
  transition: opacity 0.2s cubic-bezier(0.4, 0, 1, 1),
              transform 0.2s cubic-bezier(0.4, 0, 1, 1);
}
.page-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.logout-link {
  margin-top: auto;
  color: #64748b;
  transition: all 0.2s ease;
}
.logout-link:hover {
  background: #fef2f2;
  color: #dc2626;
}

.mobile-nav {
  display: none;
}

@media (max-width: 768px) {
  .mobile-nav {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    z-index: 30;
  }

  .mobile-nav a {
    text-align: center;
    padding: 12px 6px;
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 600;
  }

  .mobile-nav a.active {
    color: #0f172a;
  }
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 200ms ease;
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 32px;
  width: min(400px, 90%);
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  animation: scaleIn 200ms ease;
  display: flex;
  flex-direction: column;
  align-items: center;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.modal-icon-wrapper {
  width: 48px;
  height: 48px;
  background: #fef2f2;
  color: #dc2626;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.modal-title {
  font-family: 'Inter', sans-serif;
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 24px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  width: 100%;
}

.modal-actions button {
  flex: 1;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.btn-cancel {
  background: #f3f4f6;
  color: #4b5563;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-confirm {
  background: #dc2626;
  color: white;
}

.btn-confirm:hover {
  background: #b91c1c;
}
</style>
