import { createRouter, createWebHistory } from 'vue-router'
import api from '../services/api'
import LandingView from '../views/LandingView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import TransactionsView from '../views/TransactionsView.vue'
import BudgetsView from '../views/BudgetsView.vue'
import ReportsView from '../views/ReportsView.vue'
import SettingsView from '../views/SettingsView.vue'
import AppLayout from '../components/AppLayout.vue'
import MaintenanceView from '../views/MaintenanceView.vue'

import AdminLoginView from '../views/admin/AdminLoginView.vue'
import AdminLayout from '../views/admin/AdminLayout.vue'
import AdminDashboardView from '../views/admin/AdminDashboardView.vue'
import AdminUsersView from '../views/admin/AdminUsersView.vue'
import AdminTransactionsView from '../views/admin/AdminTransactionsView.vue'
import AdminCategoriesView from '../views/admin/AdminCategoriesView.vue'
import AdminAuditView from '../views/admin/AdminAuditView.vue'
import AdminSettingsView from '../views/admin/AdminSettingsView.vue'

const routes = [
  { path: '/', name: 'landing', component: LandingView },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/maintenance', name: 'maintenance', component: MaintenanceView },
  { 
    path: '/', 
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'dashboard', component: DashboardView },
      { path: 'transactions', name: 'transactions', component: TransactionsView },
      { path: 'budgets', name: 'budgets', component: BudgetsView },
      { path: 'reports', name: 'reports', component: ReportsView },
      { path: 'settings', name: 'settings', component: SettingsView },
    ]
  },
  { path: '/admin/login', name: 'admin-login', component: AdminLoginView },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAdmin: true },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'admin-dashboard', component: AdminDashboardView },
      { path: 'utilisateurs', name: 'admin-users', component: AdminUsersView },
      { path: 'transactions', name: 'admin-transactions', component: AdminTransactionsView },
      { path: 'categories', name: 'admin-categories', component: AdminCategoriesView },
      { path: 'journal', name: 'admin-audit', component: AdminAuditView },
      { path: 'parametres', name: 'admin-settings', component: AdminSettingsView },
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

let authChecked = false
let adminChecked = false

router.beforeEach(async (to) => {
  const token = localStorage.getItem('budgetwise_token')
  const adminToken = localStorage.getItem('admin_token')

  if (to.meta.requiresAdmin) {
    if (!adminToken) return { name: 'admin-login' }
    
    if (!adminChecked) {
      try {
        await api.get('/admin/me', { headers: { Authorization: `Bearer ${adminToken}` } })
        adminChecked = true
      } catch {
        localStorage.removeItem('admin_token')
        return { name: 'admin-login' }
      }
    }
    return
  }

  if (to.name === 'admin-login' && adminToken) {
    return { name: 'admin-dashboard' }
  }

  // If user is logged in and visits landing page, redirect to dashboard
  if (to.name === 'landing' && token) {
    return { name: 'dashboard' }
  }

  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  if (to.meta.requiresAuth && token && !authChecked) {
    try {
      const { data } = await api.get('/auth/me')
      localStorage.setItem('budgetwise_user', JSON.stringify(data))
      authChecked = true
    } catch (err) {
      if (err?.response?.status === 503) {
        return { name: 'maintenance' }
      }
      localStorage.removeItem('budgetwise_token')
      localStorage.removeItem('budgetwise_user')
      return { name: 'login' }
    }
  }

  if ((to.name === 'login' || to.name === 'register') && token) {
    return { name: 'dashboard' }
  }
})

export default router
