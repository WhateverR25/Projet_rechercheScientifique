<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { Line, Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import api from '@/services/api'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const { t } = useI18n()
const stats = ref(null)
const chartDataRegistrations = ref(null)
const chartDataTransactions = ref(null)
const loading = ref(true)

const fetchDashboard = async () => {
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value = data.stats
    
    chartDataRegistrations.value = {
      labels: data.charts.registrations.map(i => i.label).reverse(),
      datasets: [{
        label: 'Nouveaux utilisateurs',
        data: data.charts.registrations.map(i => i.count).reverse(),
        borderColor: '#c9a84c',
        backgroundColor: 'rgba(201, 168, 76, 0.2)',
        tension: 0.4,
        fill: true
      }]
    }

    chartDataTransactions.value = {
      labels: data.charts.transactions.map(i => i.date).reverse(),
      datasets: [{
        label: 'Transactions',
        data: data.charts.transactions.map(i => i.count).reverse(),
        backgroundColor: '#111827',
        borderRadius: 4
      }]
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
    x: { grid: { display: false } }
  }
}
</script>

<template>
  <div class="admin-dashboard">
    <h1 class="page-title">{{ t('dashboard') }}</h1>
    
    <div v-if="loading" class="loading">{{ t('loading') }}</div>
    <div v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <h3>{{ t('totalUsers') }}</h3>
          <p class="stat-val">{{ stats.totalUsers }}</p>
        </div>
        <div class="stat-card">
          <h3>{{ t('newThisMonth') }}</h3>
          <p class="stat-val">{{ stats.newUsersThisMonth }}</p>
        </div>
        <div class="stat-card">
          <h3>{{ t('totalTransactionsAdmin') }}</h3>
          <p class="stat-val">{{ stats.totalTransactions }}</p>
        </div>
        <div class="stat-card">
          <h3>{{ t('activeCategories') }}</h3>
          <p class="stat-val">{{ stats.activeCategories }}</p>
        </div>
      </div>

      <div class="charts-grid">
        <div class="chart-card">
          <h3>{{ t('registrationsLast6Months') }}</h3>
          <div class="chart-container">
            <Line v-if="chartDataRegistrations" :data="chartDataRegistrations" :options="chartOptions" />
          </div>
        </div>
        
        <div class="chart-card">
          <h3>{{ t('transactionsLast30Days') }}</h3>
          <div class="chart-container">
            <Bar v-if="chartDataTransactions" :data="chartDataTransactions" :options="chartOptions" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #111827;
  margin-bottom: 32px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.stat-card h3 {
  font-size: 0.85rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 8px;
}

.stat-val {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
}

.charts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.chart-card {
  background: white;
  padding: 24px;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.chart-card h3 {
  font-size: 1rem;
  color: #111827;
  margin-bottom: 20px;
  font-weight: 600;
}

.chart-container {
  height: 300px;
  width: 100%;
}
</style>
