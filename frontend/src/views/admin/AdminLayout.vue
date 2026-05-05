<script setup>
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()

const logout = async () => {
  try {
    await api.post('/admin/logout')
  } catch {}
  localStorage.removeItem('admin_token')
  router.push('/admin/login')
}
</script>

<template>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="sidebar-header">
        <img src="/aurem-logo.png" alt="Aurem" class="logo-img" />
        <span class="badge">ADMIN</span>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/admin/dashboard" :class="{ active: route.name === 'admin-dashboard' }">
          {{ t('dashboard') }}
        </router-link>
        <router-link to="/admin/utilisateurs" :class="{ active: route.name === 'admin-users' }">
          {{ t('adminUsers') }}
        </router-link>
        <router-link to="/admin/transactions" :class="{ active: route.name === 'admin-transactions' }">
          {{ t('adminTransactions') }}
        </router-link>
        <router-link to="/admin/categories" :class="{ active: route.name === 'admin-categories' }">
          {{ t('adminCategories') }}
        </router-link>
        <router-link to="/admin/parametres" :class="{ active: route.name === 'admin-settings' }">
          {{ t('adminSettings') }}
        </router-link>
        <router-link to="/admin/journal" :class="{ active: route.name === 'admin-audit' }">
          {{ t('adminAudit') }}
        </router-link>
      </nav>

      <button class="logout-btn" @click="logout">{{ t('adminLogout') }}</button>
    </aside>

    <main class="admin-content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f3ef;
  font-family: 'Inter', sans-serif;
}

.admin-sidebar {
  width: 250px;
  background: #111827;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 24px 0;
}

.sidebar-header {
  padding: 0 24px;
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 40px;
}

.logo-img {
  height: 90px;
  filter: brightness(0) invert(1);
}

.badge {
  background: #c9a84c;
  color: #111827;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 12px;
}

.sidebar-nav a {
  padding: 12px 16px;
  color: #9ca3af;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.95rem;
  border-radius: 8px;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}

.sidebar-nav a:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.sidebar-nav a.active {
  background: rgba(201, 168, 76, 0.1);
  color: #c9a84c;
  border-left-color: #c9a84c;
}

.logout-btn {
  margin-top: auto;
  background: transparent;
  border: none;
  color: #ef4444;
  padding: 16px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.2s;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.1);
}

.admin-content {
  flex: 1;
  padding: 40px;
  overflow-y: auto;
  max-height: 100vh;
}
</style>
