<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const { t, locale } = useI18n()
const categories = ref([])
const expenses = ref([])
const limits = ref({})
const saved = ref(false)

const monthKey = computed(() => {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
})

const storageKey = computed(() => `budgetwise_limits_${monthKey.value}`)

const currency = (value) => new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
  style: 'currency',
  currency: 'EUR',
}).format(Number(value ?? 0))

const spentByCategory = computed(() => {
  const grouped = {}
  for (const expense of expenses.value) {
    const categoryId = expense.category_id || 0
    grouped[categoryId] = (grouped[categoryId] || 0) + Number(expense.amount || 0)
  }
  return grouped
})

const budgetRows = computed(() => {
  return categories.value
    .filter((category) => category.type === 'expense' || category.type === 'both')
    .map((category) => {
      const spent = spentByCategory.value[category.id] || 0
      const limit = Number(limits.value[category.id] || 0)
      const remaining = limit - spent
      const progress = limit > 0 ? Math.min((spent / limit) * 100, 100) : 0

      return { ...category, spent, limit, remaining, progress }
    })
})

const loadData = async () => {
  const [categoryRes, expenseRes] = await Promise.all([api.get('/categories'), api.get('/expenses')])
  categories.value = categoryRes.data
  expenses.value = expenseRes.data.filter((item) => String(item.spent_at).startsWith(monthKey.value))

  const stored = localStorage.getItem(storageKey.value)
  limits.value = stored ? JSON.parse(stored) : {}
}

const saveBudgets = () => {
  localStorage.setItem(storageKey.value, JSON.stringify(limits.value))
  saved.value = true
  window.setTimeout(() => { saved.value = false }, 1800)
}

onMounted(loadData)
</script>

<template>
  <div class="budgets-view">
    <header class="top-bar">
      <h1>{{ t('budgets') }}</h1>
      <p class="subtitle">{{ t('budgetsSubtitle') }}</p>
    </header>

    <div class="card">
      <div class="card-header">
        <h2>{{ t('monthlyBudgetPlanner') }}</h2>
        <button @click="saveBudgets">{{ t('saveBudgets') }}</button>
      </div>

      <p class="hint">{{ t('budgetAdvice') }}</p>
      <p v-if="saved" class="saved">{{ t('budgetsSaved') }}</p>

      <div v-if="budgetRows.length" class="budget-list">
        <article v-for="item in budgetRows" :key="item.id" class="budget-item">
          <div class="row-top">
            <div>
              <h3>{{ item.name }}</h3>
              <p>{{ t('spent') }}: {{ currency(item.spent) }}</p>
            </div>
            <div class="limit-input">
              <label :for="`budget-${item.id}`">{{ t('budgetLimit') }}</label>
              <input :id="`budget-${item.id}`" v-model.number="limits[item.id]" type="number" min="0" step="0.01" />
            </div>
          </div>

          <div class="progress-bar">
            <div class="progress" :style="{ width: `${item.progress}%`, background: item.remaining < 0 ? '#ef4444' : item.color }"></div>
          </div>

          <p class="remaining" :class="{ danger: item.remaining < 0 }">
            {{ item.remaining < 0 ? `${t('overBudget')}: ${currency(Math.abs(item.remaining))}` : `${t('remaining')}: ${currency(item.remaining)}` }}
          </p>
        </article>
      </div>

      <p v-else class="empty">{{ t('noData') }}</p>
    </div>
  </div>
</template>

<style scoped>
.budgets-view { display: flex; flex-direction: column; gap: 24px; }
.subtitle { color: #6b7280; margin-top: 4px; }
.card { background: white; border-radius: 16px; border: 1px solid #f3f4f6; padding: 24px; }
.card-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
.hint { color: #6b7280; margin: 12px 0; font-size: 0.9rem; }
.saved { color: #16a34a; font-weight: 600; margin-bottom: 12px; }
.budget-list { display: flex; flex-direction: column; gap: 16px; }
.budget-item { border: 1px solid #f3f4f6; border-radius: 12px; padding: 16px; }
.row-top { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.row-top h3 { font-size: 1rem; color: #111827; margin-bottom: 4px; }
.row-top p { color: #6b7280; font-size: 0.9rem; }
.limit-input { display: flex; flex-direction: column; gap: 6px; min-width: 180px; }
.limit-input label { font-size: 0.8rem; color: #6b7280; }
.limit-input input { border: 1px solid #d1d5db; border-radius: 10px; padding: 10px; }
.progress-bar { width: 100%; height: 9px; border-radius: 999px; background: #f3f4f6; overflow: hidden; }
.progress { height: 100%; }
.remaining { margin-top: 8px; font-size: 0.9rem; color: #374151; }
.remaining.danger { color: #dc2626; font-weight: 600; }
.empty { color: #6b7280; }
</style>