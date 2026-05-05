<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const logs = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get('/admin/logs')
    logs.value = data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="admin-audit">
    <div class="page-header">
      <h1 class="page-title">Journal d'audit</h1>
    </div>

    <div class="table-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type d'action</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="3" class="text-center">Chargement...</td></tr>
          <tr v-else-if="logs.length === 0"><td colspan="3" class="text-center">Aucune entrée trouvée.</td></tr>
          <tr v-for="log in logs" :key="log.id">
            <td class="date-cell">{{ new Date(log.created_at).toLocaleString() }}</td>
            <td><span class="action-type">{{ log.action_type }}</span></td>
            <td>{{ log.description }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.page-header { margin-bottom: 32px; }
.page-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #111827; }

.table-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; overflow: hidden; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 16px 24px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.admin-table th { background: #f9fafb; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; font-weight: 600; }
.text-center { text-align: center; }

.date-cell { color: #6b7280; font-size: 0.9rem; }
.action-type { background: #f3f4f6; color: #111827; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
</style>
