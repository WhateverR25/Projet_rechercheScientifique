<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()

const logout = async () => {
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
        <div class="logo-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 12H16.01" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 12H12.01" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 12H8.01" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h2>BudgetWise</h2>
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
        <a href="#" @click.prevent="logout" class="logout-link">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          {{ t('logout') }}
        </a>
      </nav>
      <div class="sidebar-footer">
        <p>© 2026 BudgetWise</p>
      </div>
    </aside>

    <section class="content">
      <router-view />
    </section>

    <nav class="mobile-nav">
      <router-link to="/dashboard" :class="{ active: route.name === 'dashboard' }">{{ t('dashboard') }}</router-link>
      <router-link to="/transactions" :class="{ active: route.name === 'transactions' }">{{ t('transactions') }}</router-link>
      <router-link to="/budgets" :class="{ active: route.name === 'budgets' }">{{ t('budgets') }}</router-link>
      <router-link to="/reports" :class="{ active: route.name === 'reports' }">{{ t('reports') }}</router-link>
      <router-link to="/settings" :class="{ active: route.name === 'settings' }">{{ t('settings') }}</router-link>
    </nav>
  </main>
</template>

<style scoped>
.logout-link {
  margin-top: auto;
  color: #ef4444 !important;
}
.logout-link:hover {
  background: #fef2f2 !important;
  color: #dc2626 !important;
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
    color: #6f4ef2;
  }
}
</style>