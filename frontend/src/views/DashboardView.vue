<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Chart as ChartJS, ArcElement, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler } from 'chart.js'
import { Doughnut, Line } from 'vue-chartjs'
import api from '../services/api'

ChartJS.register(ArcElement, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Legend, Filler)

const { t, locale } = useI18n()
const dashboard = ref(null)

const currency = (value) => new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
  style: 'currency',
  currency: 'EUR',
}).format(Number(value ?? 0))

const loadAll = async () => {
  const { data } = await api.get('/dashboard')
  dashboard.value = data
}

const formatMonth = (monthValue) => {
  const date = new Date(`${monthValue}-01T00:00:00`)
  return date.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', { month: 'short' })
}

const lineData = computed(() => {
  const income = dashboard.value?.monthly_income ?? []
  const expenses = dashboard.value?.monthly_expenses ?? []
  const monthSet = new Set([...income.map((item) => item.month), ...expenses.map((item) => item.month)])
  const months = Array.from(monthSet).sort()
  const labels = months.length ? months.map(formatMonth) : [t('noData')]

  return {
    labels,
    datasets: [
      {
        label: t('income'),
        data: months.length ? months.map((month) => Number(income.find((item) => item.month === month)?.total ?? 0)) : [0],
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        fill: true,
        tension: 0.4,
      },
      {
        label: t('expense'),
        data: months.length ? months.map((month) => Number(expenses.find((item) => item.month === month)?.total ?? 0)) : [0],
        borderColor: '#f43f5e',
        backgroundColor: 'rgba(244, 63, 94, 0.1)',
        fill: true,
        tension: 0.4,
      },
    ],
  }
})

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { color: '#9ca3af' }, border: { display: false }, grid: { color: '#f3f4f6' } },
    x: { ticks: { color: '#9ca3af' }, border: { display: false }, grid: { display: false } },
  },
}

const pieData = computed(() => {
  const categories = dashboard.value?.expense_by_category ?? []

  return {
    labels: categories.length ? categories.map((item) => item.name) : [t('noData')],
    datasets: [{
      data: categories.length ? categories.map((item) => Number(item.total ?? 0)) : [1],
      backgroundColor: categories.length ? categories.map((item) => item.color) : ['#e5e7eb'],
      borderWidth: 4,
      hoverOffset: 4,
    }],
  }
})

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '70%',
  plugins: {
    legend: {
      position: 'bottom',
      labels: { usePointStyle: true, padding: 20, font: { size: 12, family: 'Inter' } },
    },
  },
}

const topCategories = computed(() => (dashboard.value?.expense_by_category ?? []).slice(0, 4))

onMounted(loadAll)
</script>

<template>
  <div class="dashboard-page" v-if="dashboard">
    <header class="top-bar">
      <h1>{{ t('dashboard') }}</h1>
    </header>

    <section class="kpis">
      <article class="kpi-card">
        <div class="kpi-icon balance-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
        </div>
        <div class="kpi-info">
          <p>{{ t('totalBalance') }}</p>
          <h3>{{ currency(dashboard.balance ?? 0) }}</h3>
        </div>
      </article>
      <article class="kpi-card">
        <div class="kpi-icon income-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7V17"></path></svg>
        </div>
        <div class="kpi-info">
          <p>{{ t('monthlyIncome') }}</p>
          <h3>{{ currency(dashboard.total_income ?? 0) }}</h3>
        </div>
      </article>
      <article class="kpi-card">
        <div class="kpi-icon expense-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 7L7 17M7 17H17M7 17V7"></path></svg>
        </div>
        <div class="kpi-info">
          <p>{{ t('monthlyExpenses') }}</p>
          <h3>{{ currency(dashboard.total_expense ?? 0) }}</h3>
        </div>
      </article>
      <article class="kpi-card">
        <div class="kpi-icon savings-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
        <div class="kpi-info">
          <p>{{ t('savingsRate') }}</p>
          <h3>{{ Number(dashboard.savings_rate ?? 0).toFixed(1) }}%</h3>
        </div>
      </article>
    </section>

    <section class="charts-row">
      <article class="chart-card line-chart">
        <h2>{{ t('incomeVsExpenses') }}</h2>
        <div class="chart-wrapper">
          <Line :data="lineData" :options="lineOptions" />
        </div>
      </article>
      <article class="chart-card pie-chart">
        <h2>{{ t('spendingByCategory') }}</h2>
        <div class="chart-wrapper">
          <Doughnut :data="pieData" :options="pieOptions" />
        </div>
      </article>
    </section>

    <section class="bottom-row">
      <div class="list-card">
        <div class="card-header">
          <h2>{{ t('recentTransactions') }}</h2>
          <router-link to="/transactions">{{ t('viewAll') }}</router-link>
        </div>

        <ul v-if="dashboard.recent_transactions?.length">
          <li v-for="item in dashboard.recent_transactions" :key="`${item.type}-${item.id}`">
            <div class="tx-left">
              <div class="tx-icon" :class="item.type === 'income' ? 'tx-income' : 'tx-expense'">
                <svg v-if="item.type === 'income'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M17 7H7M17 7V17"></path></svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 7L7 17M7 17H17M7 17V7"></path></svg>
              </div>
              <div class="tx-details">
                <strong>{{ item.description }}</strong>
                <span>{{ item.date }}</span>
              </div>
            </div>
            <span :class="item.type === 'income' ? 'income-text' : 'expense-text'">
              {{ item.type === 'income' ? '+' : '-' }}{{ currency(item.amount) }}
            </span>
          </li>
        </ul>
        <p v-else class="empty-copy">{{ t('addFirstTransaction') }}</p>
      </div>

      <div class="budget-card">
        <div class="card-header">
          <h2>{{ t('topSpending') }}</h2>
          <router-link to="/reports">{{ t('viewAll') }}</router-link>
        </div>
        <div class="budget-list" v-if="topCategories.length">
          <div class="budget-item" v-for="item in topCategories" :key="item.name">
            <div class="budget-info">
              <span>{{ item.name }}</span>
              <span>{{ currency(item.total) }}</span>
            </div>
            <div class="progress-bar">
              <div class="progress" :style="{ width: `${Math.min((Number(item.total) / Number(topCategories[0].total || 1)) * 100, 100)}%`, background: item.color }"></div>
            </div>
          </div>
        </div>
        <p v-else class="empty-copy">{{ t('noData') }}</p>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.empty-copy {
  color: #6b7280;
  font-size: 0.95rem;
}
</style>
