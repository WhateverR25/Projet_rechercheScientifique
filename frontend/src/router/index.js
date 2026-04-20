import { createRouter, createWebHistory } from 'vue-router'
import api from '../services/api'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import TransactionsView from '../views/TransactionsView.vue'
import BudgetsView from '../views/BudgetsView.vue'
import ReportsView from '../views/ReportsView.vue'
import SettingsView from '../views/SettingsView.vue'
import AppLayout from '../components/AppLayout.vue'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
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
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

let authChecked = false

router.beforeEach(async (to) => {
  const token = localStorage.getItem('budgetwise_token')

  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  if (to.meta.requiresAuth && token && !authChecked) {
    try {
      const { data } = await api.get('/auth/me')
      localStorage.setItem('budgetwise_user', JSON.stringify(data))
      authChecked = true
    } catch {
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
