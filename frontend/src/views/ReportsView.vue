<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Chart as ChartJS, ArcElement, CategoryScale, LinearScale, LineElement, PointElement, Tooltip, Legend } from 'chart.js'
import { Doughnut, Line } from 'vue-chartjs'
import api from '../services/api'

ChartJS.register(ArcElement, CategoryScale, LinearScale, LineElement, PointElement, Tooltip, Legend)

const { t, locale } = useI18n()
const incomes = ref([])
const expenses = ref([])

const currency = (value) => new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
  style: 'currency',
  currency: 'EUR',
}).format(Number(value ?? 0))

const monthLabel = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', { month: 'short' })
}

const monthlyTrendData = computed(() => {
  const months = {}
  const now = new Date()
  for (let i = 5; i >= 0; i -= 1) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    months[key] = { income: 0, expense: 0 }
  }

  incomes.value.forEach((item) => {
    const key = String(item.received_at).slice(0, 7)
    if (months[key]) months[key].income += Number(item.amount || 0)
  })
  expenses.value.forEach((item) => {
    const key = String(item.spent_at).slice(0, 7)
    if (months[key]) months[key].expense += Number(item.amount || 0)
  })

  const keys = Object.keys(months)
  const labels = keys.map((key) => monthLabel(`${key}-01`))
  return {
    labels,
    datasets: [
      { label: t('income'), data: keys.map((key) => months[key].income), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', tension: 0.35, fill: true },
      { label: t('expense'), data: keys.map((key) => months[key].expense), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.15)', tension: 0.35, fill: true },
    ],
  }
})

const categoryPieData = computed(() => {
  const grouped = {}
  expenses.value.forEach((item) => {
    const name = item.category?.name || t('uncategorized')
    const color = item.category?.color || '#9ca3af'
    if (!grouped[name]) grouped[name] = { total: 0, color }
    grouped[name].total += Number(item.amount || 0)
  })

  const labels = Object.keys(grouped)
  return {
    labels: labels.length ? labels : [t('noData')],
    datasets: [{
      data: labels.length ? labels.map((label) => grouped[label].total) : [1],
      backgroundColor: labels.length ? labels.map((label) => grouped[label].color) : ['#e5e7eb'],
      borderWidth: 2,
    }],
  }
})

const totals = computed(() => {
  const totalIncome = incomes.value.reduce((sum, item) => sum + Number(item.amount || 0), 0)
  const totalExpenses = expenses.value.reduce((sum, item) => sum + Number(item.amount || 0), 0)
  return {
    totalIncome,
    totalExpenses,
    balance: totalIncome - totalExpenses,
  }
})

const loadData = async () => {
  const [incomeRes, expenseRes] = await Promise.all([api.get('/incomes'), api.get('/expenses')])
  incomes.value = incomeRes.data
  expenses.value = expenseRes.data
}

onMounted(loadData)
</script>

<template>
  <div class="reports-view">
    <header class="top-bar">
      <h1>{{ t('reports') }}</h1>
      <p class="subtitle">{{ t('reportsSubtitle') }}</p>
    </header>

    <section class="kpis">
      <article class="kpi">
        <p>{{ t('totalIncomeValue') }}</p>
        <h3>{{ currency(totals.totalIncome) }}</h3>
      </article>
      <article class="kpi">
        <p>{{ t('totalExpensesValue') }}</p>
        <h3>{{ currency(totals.totalExpenses) }}</h3>
      </article>
      <article class="kpi">
        <p>{{ t('netBalanceValue') }}</p>
        <h3>{{ currency(totals.balance) }}</h3>
      </article>
    </section>

    <section class="grid">
      <article class="card">
        <h2>{{ t('monthlyTrends') }} - {{ t('lastSixMonths') }}</h2>
        <div class="chart-wrap">
          <Line :data="monthlyTrendData" :options="{ responsive: true, maintainAspectRatio: false }" />
        </div>
      </article>
      <article class="card">
        <h2>{{ t('expensesByCategory') }}</h2>
        <div class="chart-wrap">
          <Doughnut :data="categoryPieData" :options="{ responsive: true, maintainAspectRatio: false }" />
        </div>
      </article>
    </section>
  </div>
</template>

<style scoped>
.reports-view { display: flex; flex-direction: column; gap: 24px; }
.subtitle { color: #6b7280; margin-top: 4px; }
.kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.kpi { background: white; border: 1px solid #f3f4f6; border-radius: 14px; padding: 16px; }
.kpi p { color: #6b7280; margin-bottom: 6px; }
.kpi h3 { color: #111827; font-size: 1.2rem; }
.grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
.card { background: white; border: 1px solid #f3f4f6; border-radius: 16px; padding: 18px; }
.card h2 { font-size: 1rem; margin-bottom: 12px; }
.chart-wrap { height: 300px; }
@media (max-width: 900px) {
  .kpis { grid-template-columns: 1fr; }
  .grid { grid-template-columns: 1fr; }
}
</style>