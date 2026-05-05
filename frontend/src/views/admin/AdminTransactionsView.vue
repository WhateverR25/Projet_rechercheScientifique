<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'

const transactions = ref([])
const loading = ref(true)
const pagination = ref({ current: 1, last: 1 })

const filterType = ref('')
const filterCategory = ref('')
const filterDate = ref('')

const fetchTransactions = async (page = 1) => {
  loading.value = true
  try {
    let url = `/admin/transactions?page=${page}`
    if (filterType.value) url += `&type=${filterType.value}`
    if (filterCategory.value) url += `&category=${filterCategory.value}`
    if (filterDate.value) url += `&date=${filterDate.value}`
    
    const { data } = await api.get(url)
    transactions.value = data.data
    pagination.value = { current: data.current_page, last: data.last_page }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

watch([filterType, filterCategory, filterDate], () => {
  fetchTransactions(1)
})

onMounted(() => fetchTransactions())
</script>

<template>
  <div class="admin-transactions">
    <div class="page-header">
      <h1 class="page-title">Transactions (Global Feed)</h1>
      <div class="filters">
        <select v-model="filterType" class="filter-input">
          <option value="">Tous les types</option>
          <option value="income">Revenus</option>
          <option value="expense">Dépenses</option>
        </select>
        <input v-model="filterCategory" type="text" placeholder="Catégorie..." class="filter-input" />
        <input v-model="filterDate" type="date" class="filter-input" />
      </div>
    </div>

    <div class="table-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Type</th>
            <th>Catégorie</th>
            <th>Description</th>
            <th class="text-right">Montant</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center">Chargement...</td></tr>
          <tr v-else-if="transactions.length === 0"><td colspan="6" class="text-center">Aucune transaction trouvée.</td></tr>
          <tr v-for="tx in transactions" :key="tx.id + tx.type">
            <td>{{ new Date(tx.date).toLocaleDateString() }}</td>
            <td>
              <div class="user-info">
                <strong>{{ tx.user_name }}</strong>
                <span class="text-muted">{{ tx.user_email }}</span>
              </div>
            </td>
            <td>
              <span class="badge" :class="tx.type === 'income' ? 'badge-income' : 'badge-expense'">
                {{ tx.type === 'income' ? 'Revenu' : 'Dépense' }}
              </span>
            </td>
            <td>{{ tx.category_name }}</td>
            <td>{{ tx.description }}</td>
            <td class="text-right font-bold" :class="tx.type === 'income' ? 'text-green' : 'text-red'">
              {{ tx.type === 'income' ? '+' : '-' }}{{ tx.amount }} €
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="pagination.last > 1">
        <button 
          :disabled="pagination.current === 1" 
          @click="fetchTransactions(pagination.current - 1)"
        >Précédent</button>
        <span>Page {{ pagination.current }} / {{ pagination.last }}</span>
        <button 
          :disabled="pagination.current === pagination.last" 
          @click="fetchTransactions(pagination.current + 1)"
        >Suivant</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.page-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #111827; }

.filters { display: flex; gap: 12px; }
.filter-input { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; }
.filter-input:focus { border-color: #c9a84c; }

.table-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; overflow: hidden; }

.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 16px 24px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.admin-table th { background: #f9fafb; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; font-weight: 600; }
.admin-table td { font-size: 0.95rem; color: #374151; }

.user-info { display: flex; flex-direction: column; }
.text-muted { font-size: 0.8rem; color: #6b7280; }

.badge { padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-income { background: #dcfce7; color: #166534; }
.badge-expense { background: #fee2e2; color: #991b1b; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.font-bold { font-weight: 600; }
.text-green { color: #10b981; }
.text-red { color: #dc2626; }

.pagination { padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e5e7eb; background: #f9fafb; }
.pagination button { padding: 8px 16px; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer; }
.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
