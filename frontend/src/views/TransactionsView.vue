<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend)

const { locale, t } = useI18n()

// Data states
const categories = ref([])
const transactions = ref([])
const type = ref('expense')
const isModalOpen = ref(false)

// Form states
const form = ref({ amount: '', description: '', date: new Date().toISOString().split('T')[0], category_id: '' })
const catForm = ref({ name: '', type: 'expense', color: '#3b82f6' })
const catError = ref('')
const formError = ref('')

// Sorting & Filtering
const sortKey = ref('date')
const sortOrder = ref('desc')

const currency = (value) => new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
  style: 'currency',
  currency: 'EUR',
}).format(Number(value || 0))

const loadAll = async () => {
  const [catRes, expRes, incRes] = await Promise.all([
    api.get('/categories'),
    api.get('/expenses'),
    api.get('/incomes')
  ])
  
  categories.value = catRes.data
  
  const exps = expRes.data.map(e => ({ ...e, _type: 'expense', date: e.spent_at }))
  const incs = incRes.data.map(i => ({ ...i, _type: 'income', date: i.received_at }))
  
  transactions.value = [...exps, ...incs]
}

const sortedTransactions = computed(() => {
  return [...transactions.value].sort((a, b) => {
    let modifier = sortOrder.value === 'asc' ? 1 : -1
    if (a[sortKey.value] < b[sortKey.value]) return -1 * modifier
    if (a[sortKey.value] > b[sortKey.value]) return 1 * modifier
    return 0
  })
})

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortOrder.value = 'asc'
  }
}

const addTransaction = async () => {
  formError.value = ''
  if (!form.value.amount || !form.value.description || !form.value.date) return

  const endpoint = type.value === 'income' ? '/incomes' : '/expenses'
  const dateField = type.value === 'income' ? 'received_at' : 'spent_at'

  try {
    await api.post(endpoint, {
      amount: Number(form.value.amount),
      description: form.value.description,
      [dateField]: form.value.date,
      category_id: form.value.category_id || null,
    })

    form.value = { amount: '', description: '', date: new Date().toISOString().split('T')[0], category_id: '' }
    await loadAll()
  } catch (err) {
    formError.value = err?.response?.data?.message || t('sessionExpired')
  }
}

const deleteTransaction = async (tx) => {
  if (!confirm('Are you sure you want to delete this transaction?')) return
  const endpoint = tx._type === 'income' ? `/incomes/${tx.id}` : `/expenses/${tx.id}`
  await api.delete(endpoint)
  await loadAll()
}

const addCategory = async () => {
  catError.value = ''
  if (!catForm.value.name.trim()) {
    catError.value = t('categoryNameRequired')
    return
  }
  
  const exists = categories.value.find(c => c.name.toLowerCase() === catForm.value.name.toLowerCase() && (c.type === catForm.value.type || c.type === 'both'))
  if (exists) {
    catError.value = t('categoryExists')
    return
  }

  try {
    await api.post('/categories', catForm.value)
    catForm.value = { name: '', type: 'expense', color: '#3b82f6' }
    isModalOpen.value = false
    await loadAll()
  } catch (err) {
    catError.value = err?.response?.data?.message || t('failedCreateCategory')
  }
}

// Chart Data (Expenses by Category for visual interest)
const pieData = computed(() => {
  const exps = transactions.value.filter(t => t._type === 'expense')
  const grouped = {}
  
  exps.forEach(t => {
    const catName = t.category?.name || 'Uncategorized'
    const color = t.category?.color || '#9ca3af'
    if (!grouped[catName]) grouped[catName] = { total: 0, color }
    grouped[catName].total += Number(t.amount)
  })

  const labels = Object.keys(grouped)
  const data = labels.map(l => grouped[l].total)
  const backgroundColor = labels.map(l => grouped[l].color)

  return {
    labels: labels.length ? labels : ['No Data'],
    datasets: [{ 
      data: data.length ? data : [1], 
      backgroundColor: backgroundColor.length ? backgroundColor : ['#f3f4f6'],
      borderWidth: 0,
      hoverOffset: 4
    }]
  }
})

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '75%',
  plugins: {
    legend: { display: false },
    tooltip: { enabled: !!transactions.value.filter(t => t._type === 'expense').length }
  }
}

onMounted(async () => {
  await loadAll()
})
</script>

<template>
  <div class="transactions-view">
    <header class="top-bar">
      <div>
        <h1>{{ t('transactions') }}</h1>
        <p class="subtitle">{{ t('addFirstTransaction') }}</p>
      </div>
      <button class="btn-outline" @click="isModalOpen = true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path d="M12 5v14M5 12h14"></path></svg>
        {{ t('manageCategories') }}
      </button>
    </header>

    <div class="grid-layout">
      <!-- Left Column: Add Form & Chart -->
      <div class="side-column">
        <div class="card form-card">
          <h2>{{ t('addTransaction') }}</h2>
          <div class="type-toggle">
            <button type="button" :class="{ active: type === 'expense' }" @click="type = 'expense'">{{ t('expense') }}</button>
            <button type="button" :class="{ active: type === 'income' }" @click="type = 'income'">{{ t('income') }}</button>
          </div>
          
          <form @submit.prevent="addTransaction" class="transaction-form">
            <div class="form-group">
              <label>{{ t('amount') }}</label>
              <div class="input-icon-wrapper">
                <span class="input-icon">€</span>
                <input v-model="form.amount" type="number" step="0.01" min="0.01" required :placeholder="t('amountPlaceholder')" class="pl-8" />
              </div>
            </div>
            
            <div class="form-group">
              <label>{{ t('description') }}</label>
              <input v-model="form.description" required :placeholder="t('descriptionPlaceholder')" />
            </div>
            
            <div class="form-grid-2">
              <div class="form-group">
                <label>{{ t('date') }}</label>
                <input v-model="form.date" type="date" required />
              </div>
              
              <div class="form-group">
                <label>{{ t('categories') }}</label>
                <select v-model="form.category_id">
                  <option value="">{{ t('uncategorized') }}</option>
                  <option v-for="cat in categories.filter(c => c.type === type || c.type === 'both')" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
            </div>
            
            <button type="submit" class="submit-btn" :class="type === 'income' ? 'btn-income' : 'btn-expense'">
              {{ type === 'income' ? t('addIncome') : t('addExpense') }}
            </button>
            <p v-if="formError" class="error-text">{{ formError }}</p>
          </form>
        </div>

        <div class="card chart-card">
          <h2>{{ t('expensesByCategory') }}</h2>
          <div class="chart-wrapper">
            <Doughnut :data="pieData" :options="pieOptions" />
            <div class="chart-center">
              <span class="chart-label">{{ t('totalExpensesValue') }}</span>
              <strong class="chart-value">{{ currency(transactions.filter(t => t._type === 'expense').reduce((acc, curr) => acc + Number(curr.amount), 0)) }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Data Table -->
      <div class="main-column">
        <div class="card table-card">
          <div class="table-header">
            <h2>{{ t('transactionHistory') }}</h2>
            <span class="tx-count">{{ transactions.length }} {{ t('entries') }}</span>
          </div>
          
          <div class="table-responsive">
            <table class="data-table">
              <thead>
                <tr>
                  <th @click="sortBy('date')" class="sortable">
                    {{ t('date') }} <span class="sort-icon" v-if="sortKey === 'date'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                  </th>
                  <th @click="sortBy('description')" class="sortable">
                    {{ t('description') }} <span class="sort-icon" v-if="sortKey === 'description'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                  </th>
                  <th>{{ t('categories') }}</th>
                  <th @click="sortBy('amount')" class="sortable text-right">
                    {{ t('amount') }} <span class="sort-icon" v-if="sortKey === 'amount'">{{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                  </th>
                  <th class="text-center">{{ t('actions') }}</th>
                </tr>
              </thead>
              <tbody v-if="sortedTransactions.length">
                <tr v-for="tx in sortedTransactions" :key="`${tx._type}-${tx.id}`" class="tx-row">
                  <td class="text-muted">{{ tx.date }}</td>
                  <td class="font-medium">{{ tx.description }}</td>
                  <td>
                    <span class="category-badge" :style="{ backgroundColor: (tx.category?.color || '#9ca3af') + '20', color: tx.category?.color || '#4b5563' }">
                      <span class="cat-dot" :style="{ backgroundColor: tx.category?.color || '#9ca3af' }"></span>
                      {{ tx.category?.name || t('uncategorized') }}
                    </span>
                  </td>
                  <td class="text-right font-bold" :class="tx._type === 'income' ? 'text-green' : 'text-red'">
                    {{ tx._type === 'income' ? '+' : '-' }}{{ currency(tx.amount) }}
                  </td>
                  <td class="text-center">
                    <button class="btn-icon" @click="deleteTransaction(tx)" :title="t('delete')">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr>
                  <td colspan="5" class="empty-table">
                    <div class="empty-state">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <h3>{{ t('noTransactionsYet') }}</h3>
                    <p>{{ t('addFirstTransaction') }}</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Category Modal -->
    <div class="modal-overlay" v-if="isModalOpen" @click.self="isModalOpen = false">
      <div class="modal-content">
        <header class="modal-header">
          <h2>{{ t('createCategory') }}</h2>
          <button class="close-btn" @click="isModalOpen = false">×</button>
        </header>
        
        <form @submit.prevent="addCategory" class="modal-body">
          <div class="form-group">
            <label>{{ t('categoryName') }}</label>
            <input v-model="catForm.name" required placeholder="e.g. Groceries, Salary, Utilities" />
          </div>
          
          <div class="form-group">
            <label>{{ t('transactionType') }}</label>
            <select v-model="catForm.type">
              <option value="expense">{{ t('expenseOnly') }}</option>
              <option value="income">{{ t('incomeOnly') }}</option>
              <option value="both">{{ t('bothTypes') }}</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>{{ t('color') }}</label>
            <div class="color-picker-wrapper">
              <input v-model="catForm.color" type="color" class="color-input" />
              <span class="color-hex">{{ catForm.color }}</span>
            </div>
          </div>
          
          <p v-if="catError" class="error-text">{{ catError }}</p>
          
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">{{ t('cancel') }}</button>
            <button type="submit" class="btn-primary">{{ t('save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.transactions-view {
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.top-bar h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 4px;
}

.subtitle {
  color: #6b7280;
  font-size: 0.95rem;
}

.icon {
  width: 18px;
  height: 18px;
}

.btn-outline {
  display: flex;
  align-items: center;
  gap: 8px;
  background: white;
  color: #111827;
  border: 1px solid #d1d5db;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

.grid-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 24px;
}

.side-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  border: 1px solid #f3f4f6;
}

.card h2 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 20px;
}

/* Type Toggle */
.type-toggle {
  display: flex;
  background: #f3f4f6;
  border-radius: 10px;
  padding: 4px;
  margin-bottom: 24px;
}

.type-toggle button {
  flex: 1;
  background: transparent;
  color: #6b7280;
  border-radius: 8px;
  padding: 10px;
  font-weight: 600;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.type-toggle button.active {
  background: white;
  color: #111827;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

/* Forms */
.transaction-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4b5563;
}

.form-group input, .form-group select {
  border: 1px solid #d1d5db;
  border-radius: 10px;
  padding: 12px;
  font-size: 0.95rem;
  background: #f9fafb;
  transition: all 0.2s;
  color: #111827;
}

.form-group input:focus, .form-group select:focus {
  outline: none;
  border-color: #6f4ef2;
  background: white;
  box-shadow: 0 0 0 3px rgba(111, 78, 242, 0.1);
}

.input-icon-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: #6b7280;
  font-weight: 500;
}

.pl-8 {
  padding-left: 32px !important;
  width: 100%;
}

.submit-btn {
  margin-top: 8px;
  padding: 14px;
  font-size: 1rem;
  transition: opacity 0.2s, transform 0.1s;
}

.submit-btn:active {
  transform: scale(0.98);
}

.btn-expense {
  background: #f43f5e;
}
.btn-expense:hover { background: #e11d48; }

.btn-income {
  background: #10b981;
}
.btn-income:hover { background: #059669; }

/* Chart */
.chart-wrapper {
  position: relative;
  height: 200px;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.chart-center {
  position: absolute;
  display: flex;
  flex-direction: column;
  align-items: center;
  pointer-events: none;
}

.chart-label {
  font-size: 0.8rem;
  color: #6b7280;
  font-weight: 500;
}

.chart-value {
  font-size: 1.25rem;
  color: #111827;
  font-weight: 700;
}

/* Data Table */
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.tx-count {
  background: #f3f4f6;
  color: #4b5563;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.data-table th {
  text-align: left;
  padding: 14px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: color 0.2s;
}

.data-table th.sortable:hover {
  color: #111827;
}

.sort-icon {
  margin-left: 4px;
  font-size: 1rem;
}

.data-table td {
  padding: 16px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}

.tx-row {
  transition: background-color 0.2s;
}

.tx-row:hover {
  background-color: #f9fafb;
}

.tx-row:last-child td {
  border-bottom: none;
}

.text-muted { color: #6b7280; font-size: 0.95rem; }
.font-medium { font-weight: 500; color: #111827; }
.font-bold { font-weight: 600; }
.text-right { text-align: right; }
.text-center { text-align: center; }

.text-green { color: #10b981; }
.text-red { color: #f43f5e; }

.category-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.cat-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.btn-icon {
  background: transparent;
  color: #9ca3af;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #fee2e2;
  color: #ef4444;
}

.btn-icon svg {
  width: 18px;
  height: 18px;
}

.empty-table {
  padding: 60px 0 !important;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #9ca3af;
}

.empty-state svg {
  width: 48px;
  height: 48px;
  margin-bottom: 8px;
}

.empty-state h3 {
  font-size: 1.125rem;
  color: #374151;
  font-weight: 600;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(17, 24, 39, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  animation: fadeIn 0.2s;
}

.modal-content {
  background: white;
  border-radius: 20px;
  width: min(400px, 90%);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #f3f4f6;
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 700;
}

.close-btn {
  background: transparent;
  color: #9ca3af;
  font-size: 1.5rem;
  padding: 0;
  line-height: 1;
}

.close-btn:hover { color: #111827; }

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.color-picker-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
}

.color-input {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 48px;
  height: 48px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  padding: 0;
  background: transparent;
}

.color-input::-webkit-color-swatch-wrapper { padding: 0; }
.color-input::-webkit-color-swatch { border: none; border-radius: 12px; }

.color-hex {
  font-family: monospace;
  font-size: 1.1rem;
  color: #4b5563;
  background: #f3f4f6;
  padding: 8px 12px;
  border-radius: 8px;
}

.error-text {
  color: #ef4444;
  font-size: 0.875rem;
  font-weight: 500;
  margin-top: -8px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.btn-cancel {
  background: white;
  color: #4b5563;
  border: 1px solid #d1d5db;
}

.btn-cancel:hover { background: #f9fafb; }

.btn-primary {
  background: #6f4ef2;
  color: white;
}

.btn-primary:hover { background: #5b3ed6; }

@media (max-width: 1024px) {
  .grid-layout { grid-template-columns: 1fr; }
  .side-column { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
}

@media (max-width: 768px) {
  .side-column { grid-template-columns: 1fr; }
  .top-bar { flex-direction: column; align-items: flex-start; gap: 16px; }
  .form-grid-2 { grid-template-columns: 1fr; }
}
</style>