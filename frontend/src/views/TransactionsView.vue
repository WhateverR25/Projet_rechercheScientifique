<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
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
const isDropdownOpen = ref(false)
const isCatPanelOpen = ref(false)
const showEmojiPicker = ref(false)
const catEditingId = ref(null)
const catToDelete = ref(null)

// Form states
const form = ref({ amount: '', description: '', date: new Date().toISOString().split('T')[0], category_id: '' })
const catForm = ref({ name: '', type: 'expense', color: null, icon: '❓' })
const catError = ref('')
const formError = ref('')
const showSuccess = ref(false)
const txToDelete = ref(null)

// Emoji options for category icons
const emojiOptions = ['🛒','💰','🏠','🚗','🍔','💊','🎮','📚','✈️','👕','🎁','💡','📱','🎬','⚽','🏥','💳','🎵','🐾','👶','💼','🔧','🎨','🌿','☕','🍕','🎂','📦','🚌','⛽','🏦','💸','📊','🎓','💎','🧾','🏋️','💇','🎪','🌍']

const emojiColors = [
  '#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6',
  '#ec4899','#14b8a6','#f97316','#6366f1','#84cc16',
  '#06b6d4','#e11d48','#a855f7','#f43f5e','#0ea5e9',
  '#22c55e','#fbbf24','#fb923c','#c084fc','#f472b6',
  '#2dd4bf','#facc15','#f87171','#a78bfa','#60a5fa',
  '#34d399','#fde047','#fca5a5','#e879f9','#93c5fd'
]

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

const categorySearch = ref('')
const catPanelRef = ref(null)
const catTriggerRef = ref(null)

// Filter categories to only show those matching the current transaction type
const filteredCategories = computed(() => {
  const query = categorySearch.value.toLowerCase()
  const currentType = type.value // 'expense' or 'income'
  return categories.value.filter(c => {
    const matchesSearch = c.name.toLowerCase().includes(query)
    const matchesType = c.type === currentType || c.type === 'both'
    return matchesSearch && matchesType
  })
})

// Click-outside handler to close the panel
const handleClickOutside = (e) => {
  if (!isCatPanelOpen.value) return
  const panel = catPanelRef.value
  const trigger = catTriggerRef.value
  if (panel && !panel.contains(e.target) && trigger && !trigger.contains(e.target)) {
    isCatPanelOpen.value = false
  }
}

// Event listener registration is done in the main onMounted below
onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})

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
    isDropdownOpen.value = false
    await loadAll()
    
    showSuccess.value = true
    setTimeout(() => { showSuccess.value = false }, 2000)
  } catch (err) {
    formError.value = err?.response?.data?.message || t('sessionExpired')
  }
}

const selectCategory = (id) => {
  form.value.category_id = id
  isCatPanelOpen.value = false
}

// Reset category when switching transaction type if the selected category doesn't match
const onTypeChange = (newType) => {
  type.value = newType
  if (form.value.category_id) {
    const selectedCat = categories.value.find(c => c.id === form.value.category_id)
    if (selectedCat && selectedCat.type !== 'both' && selectedCat.type !== newType) {
      form.value.category_id = ''
    }
  }
}

const deleteTransaction = (tx) => {
  txToDelete.value = tx
}

const confirmDeleteTransaction = async () => {
  if (!txToDelete.value) return
  const tx = txToDelete.value
  txToDelete.value = null
  const endpoint = tx._type === 'income' ? `/incomes/${tx.id}` : `/expenses/${tx.id}`
  await api.delete(endpoint)
  await loadAll()
}

const isDuplicateName = computed(() => {
  if (!catForm.value.name.trim()) return false
  return categories.value.some(c => 
    c.name.toLowerCase() === catForm.value.name.trim().toLowerCase() && 
    (c.type === catForm.value.type || c.type === 'both') &&
    c.id !== catEditingId.value
  )
})

const isDuplicateColor = computed(() => {
  if (!catForm.value.color) return false
  return categories.value.some(c =>
    c.color && c.color.toLowerCase() === catForm.value.color.toLowerCase() &&
    c.id !== catEditingId.value
  )
})

const startEditCat = (cat) => {
  catForm.value = { name: cat.name, type: cat.type, color: cat.color, icon: cat.icon }
  catEditingId.value = cat.id
  catError.value = ''
  isModalOpen.value = true
  isCatPanelOpen.value = false
}

const openCreateCatModal = () => {
  catForm.value = { name: '', type: 'expense', color: null, icon: '❓' }
  catEditingId.value = null
  catError.value = ''
  isModalOpen.value = true
  isCatPanelOpen.value = false
}

const confirmDeleteCat = (cat) => {
  catToDelete.value = cat
  isCatPanelOpen.value = false
}

const deleteCategory = async () => {
  if (!catToDelete.value) return
  try {
    await api.delete(`/categories/${catToDelete.value.id}`)
    catToDelete.value = null
    await loadAll()
  } catch (err) {
    console.error(err)
  }
}

const addCategory = async () => {
  catError.value = ''
  if (!catForm.value.name.trim()) {
    catError.value = t('categoryNameRequired') || 'Le nom de catégorie est obligatoire.'
    return
  }
  
  if (isDuplicateName.value || isDuplicateColor.value) return

  try {
    if (catEditingId.value) {
      await api.patch(`/categories/${catEditingId.value}`, catForm.value)
    } else {
      await api.post(`/categories`, catForm.value)
    }
    catForm.value = { name: '', type: 'expense', color: null, icon: '❓' }
    catEditingId.value = null
    isModalOpen.value = false
    showEmojiPicker.value = false
    await loadAll()
  } catch (err) {
    catError.value = err?.response?.data?.message || t('failedCreateCategory') || 'Échec de création de catégorie.'
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
  document.addEventListener('mousedown', handleClickOutside)
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

    <div class="dashboard-grid">
      <!-- Top Row: Form and Table -->
      <div class="dashboard-top-row">
        
        <!-- Left Column: Add Form -->
        <div class="form-section" :style="{ '--focus-color': type === 'expense' ? '#dc2626' : '#16a34a', '--focus-rgb': type === 'expense' ? '220, 38, 38' : '22, 163, 74' }">
          <div class="card form-card">
            <h2>{{ t('addTransaction') }}</h2>
            <div class="type-toggle" :class="type">
              <div class="toggle-pill"></div>
              <button type="button" @click="onTypeChange('expense')">{{ t('expense') }}</button>
              <button type="button" @click="onTypeChange('income')">{{ t('income') }}</button>
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
              
              <div class="form-group">
                <label>{{ t('date') }}</label>
                <input v-model="form.date" type="date" required />
              </div>
              
              <div class="form-group category-field-wrapper">
                <label>{{ t('categories') }}</label>
                <div ref="catTriggerRef" class="category-trigger" :class="{ 'is-active': isCatPanelOpen }" @click="isCatPanelOpen = !isCatPanelOpen">
                  <span v-if="form.category_id" class="selected-cat">
                    <div class="cat-preview-circle-small" :style="{ backgroundColor: categories.find(c => c.id === form.category_id)?.color || '#9ca3af' }">
                       {{ categories.find(c => c.id === form.category_id)?.icon || '' }}
                    </div>
                    {{ categories.find(c => c.id === form.category_id)?.name }}
                  </span>
                  <span v-else class="selected-cat placeholder-cat">{{ t('uncategorized') }}</span>
                  <svg class="chevron" :class="{ open: isCatPanelOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                </div>

                <!-- Floating Category Popover Panel -->
                <transition name="panel-float">
                  <div ref="catPanelRef" class="category-popover-panel" v-if="isCatPanelOpen" @click.stop>
                    <div class="side-panel-header">
                      <input v-model="categorySearch" type="text" :placeholder="t('searchCategories') || 'Rechercher...'" class="side-panel-search" />
                    </div>

                    <div class="side-panel-scroll">
                      <div class="side-panel-option" :class="{ active: form.category_id === '' }" @click="selectCategory('')">
                        <div class="cat-preview-circle-small" style="background-color: #e5e7eb; color: #9ca3af;">
                          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        {{ t('uncategorized') }}
                      </div>

                      <div v-if="filteredCategories.length > 0">
                        <div class="side-panel-section-title">{{ type === 'expense' ? (t('expense') || 'Dépenses') : (t('income') || 'Revenus') }}</div>
                        <div class="side-panel-option" :class="{ active: form.category_id === cat.id }" v-for="cat in filteredCategories" :key="cat.id" @click="selectCategory(cat.id)">
                          <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                            <div class="cat-preview-circle-small" :style="{ backgroundColor: cat.color || '#9ca3af' }">
                              {{ cat.icon }}
                            </div>
                            {{ cat.name }}
                          </div>
                          <div class="cat-actions" @click.stop>
                            <button class="cat-action-btn" @click.stop="startEditCat(cat)" title="Modifier">
                              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </button>
                            <button class="cat-action-btn cat-action-delete" @click.stop="confirmDeleteCat(cat)" title="Supprimer">
                              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                          </div>
                        </div>
                      </div>

                      <div v-if="filteredCategories.length === 0 && categorySearch" class="side-panel-empty">
                        {{ t('noResults') || 'Aucun résultat' }}
                      </div>
                    </div>

                    <div class="side-panel-footer" @click="openCreateCatModal">
                      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                      Créer une nouvelle catégorie
                    </div>
                  </div>
                </transition>
              </div>
              
              <button type="submit" class="submit-btn" :class="type === 'income' ? 'btn-income-premium' : 'btn-expense-premium'">
                <span v-if="!showSuccess">{{ type === 'income' ? t('addIncome') : t('addExpense') }}</span>
                <span v-else class="success-content">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </span>
              </button>
              <p v-if="formError" class="error-text">{{ formError }}</p>
            </form>
          </div>
        </div>

        <!-- Right Column: Data Table -->
        <div class="table-section">
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
                      <span v-if="tx.category?.icon" style="margin-right: 4px;">{{ tx.category.icon }}</span>
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
      </div> <!-- End dashboard-top-row -->

      <!-- Bottom Row: Chart -->
      <div class="chart-section">
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
    </div>


    <!-- Category Modal (Monarch-style) -->
    <div class="modal-overlay" v-if="isModalOpen" @click.self="isModalOpen = false; showEmojiPicker = false; catEditingId = null;">
      <div class="modal-content monarch-modal">
        <header class="modal-header">
          <h2>{{ catEditingId ? 'Modifier la catégorie' : t('createCategory') }}</h2>
          <button class="close-btn" @click="isModalOpen = false; showEmojiPicker = false; catEditingId = null;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </button>
        </header>
        
        <form @submit.prevent="addCategory" class="modal-body">
          <!-- Icon & Name -->
          <div class="form-group">
            <label>Icône & Nom</label>
            <div class="icon-name-row" :class="{ 'input-error': isDuplicateName }">
              <div class="emoji-trigger" @click.stop="showEmojiPicker = !showEmojiPicker" :style="{ backgroundColor: catForm.color ? catForm.color + '25' : '#f3f4f6', color: catForm.color || '#6b7280' }">
                {{ catForm.icon }}
              </div>
              <input v-model="catForm.name" required placeholder="" class="name-input-inline" />
            </div>
            <p v-if="isDuplicateName" class="inline-error">Une catégorie portant ce nom existe déjà.</p>
            <!-- Emoji Picker -->
            <transition name="dropdown-fade">
              <div class="emoji-picker-grid" v-if="showEmojiPicker" @click.stop>
                <button type="button" v-for="emoji in emojiOptions" :key="emoji" class="emoji-option" :class="{ selected: catForm.icon === emoji }" @click="catForm.icon = emoji; showEmojiPicker = false">
                  {{ emoji }}
                </button>
              </div>
            </transition>
          </div>

          <!-- Type -->
          <div class="form-group">
            <label>{{ t('transactionType') }}</label>
            <select v-model="catForm.type">
              <option value="expense">{{ t('expenseOnly') }}</option>
              <option value="income">{{ t('incomeOnly') }}</option>
              <option value="both">{{ t('bothTypes') }}</option>
            </select>
          </div>

          <!-- Color -->
          <div class="form-group">
            <label>{{ t('color') }}</label>
            <div class="preset-colors-user">
              <button
                type="button"
                v-for="color in emojiColors"
                :key="color"
                class="color-dot-user"
                :class="{ selected: catForm.color === color }"
                :style="{ backgroundColor: color }"
                @click="catForm.color = color"
              ></button>
            </div>
            <p v-if="isDuplicateColor" class="inline-error">Cette couleur est déjà utilisée par une de vos catégories. Veuillez en choisir une différente.</p>
          </div>
          
          <p v-if="catError" class="error-text">{{ catError }}</p>
          
          <div class="modal-actions">
            <button type="submit" class="btn-save-monarch" :disabled="isDuplicateName || isDuplicateColor">{{ t('save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Transaction Modal -->
    <transition name="panel-float">
      <div v-if="txToDelete" class="modal-overlay" @click.self="txToDelete = null">
        <div class="modal-content confirm-modal" style="width: min(320px, 90%); text-align: center;">
          <div class="modal-body" style="align-items: center; padding: 32px 24px;">
            <div style="width: 48px; height: 48px; background: #fef2f2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </div>
            <h3 style="font-size: 1.15rem; color: #111827; margin-bottom: 8px;">Supprimer cette transaction ?</h3>
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 24px;">{{ txToDelete?.description }}</p>
            <div style="display: flex; gap: 12px; width: 100%;">
              <button type="button" style="flex: 1; padding: 10px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" @click="txToDelete = null">Annuler</button>
              <button type="button" style="flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" @click="confirmDeleteTransaction">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Confirm Delete Category Modal -->
    <transition name="panel-float">
      <div v-if="catToDelete" class="modal-overlay" @click.self="catToDelete = null">
        <div class="modal-content confirm-modal" style="width: min(320px, 90%); text-align: center;">
          <div class="modal-body" style="align-items: center; padding: 32px 24px;">
            <div style="width: 48px; height: 48px; background: #fef2f2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <h3 style="font-size: 1.15rem; color: #111827; margin-bottom: 8px;">Supprimer « {{ catToDelete.name }} » ?</h3>
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 24px;">Les transactions liées passeront en 'Sans catégorie'.</p>
            <div style="display: flex; gap: 12px; width: 100%;">
              <button type="button" style="flex: 1; padding: 10px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" @click="catToDelete = null">Annuler</button>
              <button type="button" style="flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;" @click="deleteCategory">Supprimer</button>
            </div>
          </div>
        </div>
      </div>
    </transition>
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

.dashboard-grid {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.dashboard-top-row {
  display: grid;
  grid-template-columns: 35% 1fr;
  gap: 24px;
  align-items: stretch;
}

@media (max-width: 1024px) {
  .dashboard-top-row {
    grid-template-columns: 1fr;
  }
}

.form-section, .table-section {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.form-section {
  position: relative;
}

.chart-section {
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

.card.form-card, .card.table-card {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card h2 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 20px;
}

/* Type Toggle */
.type-toggle {
  position: relative;
  display: flex;
  background: #f3f4f6;
  border-radius: 12px;
  padding: 4px;
  margin-bottom: 24px;
  overflow: hidden;
}

.toggle-pill {
  position: absolute;
  top: 4px;
  bottom: 4px;
  width: calc(50% - 4px);
  border-radius: 8px;
  background: white;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease;
  z-index: 1;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.type-toggle.expense .toggle-pill {
  transform: translateX(0);
  background: #fee2e2;
}

.type-toggle.income .toggle-pill {
  transform: translateX(100%);
  background: #dcfce7;
}

.type-toggle button {
  position: relative;
  z-index: 2;
  flex: 1;
  background: transparent;
  color: #6b7280;
  border: none;
  border-radius: 8px;
  padding: 10px;
  font-weight: 600;
  transition: color 0.3s ease;
  font-size: 0.9rem;
}

.type-toggle.expense button:first-of-type { color: #dc2626; }
.type-toggle.income button:last-of-type { color: #16a34a; }

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
  position: relative;
}

/* Category field needs to be the positioning anchor for the popover */
.category-field-wrapper {
  position: relative;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input, .form-group select {
  border: 1px solid rgba(15, 23, 42, 0.1);
  border-radius: 14px;
  padding: 14px 16px;
  font-size: 0.95rem;
  background: #f8fafc;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  color: #0f172a;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
}

.form-group input:hover, .form-group select:hover {
  background: #f1f5f9;
  border-color: rgba(15, 23, 42, 0.15);
}

.form-group input:focus, .form-group select:focus {
  outline: none;
  background: white;
  border-color: var(--focus-color, #0f172a);
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05), 0 0 0 4px rgba(var(--focus-rgb, 15, 23, 42), 0.15);
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
  padding-left: 38px !important;
  width: 100%;
}

/* Category Trigger (replaces old dropdown) */
.category-trigger {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid rgba(15, 23, 42, 0.1);
  border-radius: 14px;
  background: #f8fafc;
  padding: 14px 16px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
  user-select: none;
  font-size: 0.95rem;
  color: #0f172a;
}

.category-trigger:hover {
  background: #f1f5f9;
  border-color: rgba(15, 23, 42, 0.15);
}

.category-trigger.is-active {
  background: white;
  border-color: var(--focus-color, #0f172a);
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05), 0 0 0 4px rgba(var(--focus-rgb, 15, 23, 42), 0.15);
}

.placeholder-cat {
  color: #9ca3af;
}

.selected-cat {
  display: flex;
  align-items: center;
  gap: 8px;
}

.chevron {
  width: 18px;
  height: 18px;
  color: #64748b;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.chevron.open {
  transform: rotate(180deg);
}

.dropdown-fade-enter-active, .dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.dropdown-fade-enter-from, .dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-5px) scale(0.98);
}

/* Floating Category Popover Panel */
.category-popover-panel {
  position: absolute;
  top: -120px;
  left: calc(100% + 12px);
  width: 260px;
  max-height: 380px;
  background: white;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  z-index: 100;
}

.panel-float-enter-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.panel-float-leave-active {
  transition: all 0.15s ease;
}
.panel-float-enter-from {
  opacity: 0;
  transform: translateX(-8px) scale(0.96);
}
.panel-float-leave-to {
  opacity: 0;
  transform: translateX(-8px) scale(0.96);
}

.side-panel-empty {
  padding: 20px 16px;
  text-align: center;
  color: #9ca3af;
  font-size: 0.85rem;
}

.side-panel-header {
  padding: 12px;
  border-bottom: 1px solid #f3f4f6;
}

.side-panel-search {
  width: 100%;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.88rem;
  color: #111827;
  background: #fafbfc;
  transition: all 0.2s;
  outline: none;
}

.side-panel-search:focus {
  border-color: #93c5fd;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.side-panel-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 6px 0;
}

.side-panel-section-title {
  padding: 10px 16px 4px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.side-panel-option {
  padding: 9px 16px;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  font-weight: 500;
  color: #374151;
}

.side-panel-option:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.side-panel-option.active {
  background: #eff6ff;
  color: #1d4ed8;
  font-weight: 600;
}

.side-panel-footer {
  padding: 12px 16px;
  border-top: 1px solid #f3f4f6;
  background: #fafbfc;
  color: #f97316;
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.side-panel-footer:hover {
  background: #fff7ed;
}

.side-panel-footer svg {
  color: #f97316;
}

.cat-preview-circle-small {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  flex-shrink: 0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  color: white;
}

/* Premium Submit Button */
.submit-btn {
  margin-top: 12px;
  padding: 16px;
  font-size: 1.05rem;
  font-weight: 600;
  border-radius: 14px;
  transition: background-color 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, transform 0.1s ease;
  color: white;
  border: none;
  cursor: pointer;
  letter-spacing: 0.3px;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 56px;
}

.btn-expense-premium {
  background-color: #dc2626;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.btn-expense-premium:hover {
  background-color: #b91c1c;
  box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
  transform: translateY(-1px);
}

.btn-expense-premium:active {
  transform: translateY(1px);
  box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
}

.btn-income-premium {
  background-color: #16a34a;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}

.btn-income-premium:hover {
  background-color: #15803d;
  box-shadow: 0 6px 16px rgba(22, 163, 74, 0.3);
  transform: translateY(-1px);
}

.btn-income-premium:active {
  transform: translateY(1px);
  box-shadow: 0 2px 4px rgba(22, 163, 74, 0.1);
}

.success-content svg {
  animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes scaleIn {
  0% { transform: scale(0.5); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

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
  flex: 1;
  overflow-y: auto;
  min-height: 300px;
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.data-table th {
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
  text-align: left;
  padding: 14px 16px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.data-table th::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
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
  background-color: #f3f4f6;
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
  padding: 30px 0 !important;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
}

.empty-state svg {
  width: 32px;
  height: 32px;
  margin-bottom: 4px;
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

/* Monarch-style modal enhancements */
.monarch-modal {
  width: min(480px, 92%);
}

.monarch-modal .modal-header {
  padding: 24px 28px 20px;
  border-bottom: none;
}

.monarch-modal .modal-header h2 {
  font-size: 1.35rem;
  font-weight: 700;
  color: #111827;
}

.monarch-modal .modal-body {
  padding: 0 28px 28px;
  gap: 24px;
}

.icon-name-row {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1.5px solid #e5e7eb;
  border-radius: 14px;
  padding: 4px 16px 4px 4px;
  background: #fafbfc;
  transition: all 0.2s;
}

.icon-name-row:focus-within {
  border-color: #93c5fd;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.emoji-trigger {
  width: 44px;
  height: 44px;
  min-width: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
}

.emoji-trigger:hover {
  transform: scale(1.08);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.name-input-inline {
  border: none !important;
  background: transparent !important;
  padding: 12px 0 !important;
  font-size: 1rem !important;
  font-weight: 500;
  color: #111827;
  flex: 1;
  outline: none !important;
  box-shadow: none !important;
  border-radius: 0 !important;
}

.emoji-picker-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 4px;
  padding: 12px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
  max-height: 180px;
  overflow-y: auto;
  margin-top: 8px;
}

.emoji-option {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  background: transparent;
  border: 2px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s;
}

.emoji-option:hover {
  background: #f1f5f9;
  transform: scale(1.15);
}

.emoji-option.selected {
  background: #eff6ff;
  border-color: #3b82f6;
}

.btn-save-monarch {
  background: #f97316;
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2);
}

.btn-save-monarch:hover {
  background: #ea580c;
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
  transform: translateY(-1px);
}

.btn-save-monarch:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.input-error {
  border-color: #ef4444 !important;
  animation: shake 0.4s;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.inline-error {
  color: #ef4444;
  font-size: 0.8rem;
  margin-top: 4px;
}

.cat-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s ease;
}
.side-panel-option:hover .cat-actions {
  opacity: 1;
}
.cat-action-btn {
  background: transparent;
  border: none;
  color: #9ca3af;
  padding: 4px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cat-action-btn:hover {
  background: #e5e7eb;
  color: #4b5563;
}
.cat-action-delete:hover {
  background: #fee2e2;
  color: #ef4444;
}

.close-btn {
  background: transparent;
  border: none;
  color: #9ca3af;
  padding: 4px;
  cursor: pointer;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.close-btn:hover {
  color: #111827;
  background: #f3f4f6;
}

.preset-colors-user {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
}

.color-dot-user {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2.5px solid transparent;
  cursor: pointer;
  transition: all 0.15s;
  outline: none;
  padding: 0;
}

.color-dot-user:hover {
  transform: scale(1.15);
}

.color-dot-user.selected {
  border-color: white;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.35);
  transform: scale(1.1);
}

@media (max-width: 1024px) {
  .grid-layout { grid-template-columns: 1fr; }
  .side-column { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

  /* On smaller screens, popover falls below the field instead of to the right */
  .category-popover-panel {
    left: 0;
    top: 100%;
    margin-top: 8px;
    width: 100%;
  }

  .panel-float-enter-from {
    opacity: 0;
    transform: translateY(-6px) scale(0.96);
  }
  .panel-float-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.96);
  }
}

@media (max-width: 768px) {
  .side-column { grid-template-columns: 1fr; }
  .top-bar { flex-direction: column; align-items: flex-start; gap: 16px; }
  .form-grid-2 { grid-template-columns: 1fr; }
  .emoji-picker-grid {
    grid-template-columns: repeat(6, 1fr);
  }
}
</style>