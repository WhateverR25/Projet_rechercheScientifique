<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const { t } = useI18n()

const categories = ref([])
const loading = ref(true)

const showModal = ref(false)
const formData = ref({ id: null, name: '', type: 'expense', icon: '📝', color: '#10b981' })

const catToDelete = ref(null)

const fetchCategories = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/categories')
    categories.value = data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchCategories)

const isDuplicateName = computed(() => {
  if (!formData.value.name.trim()) return false
  return categories.value.some(c => 
    c.name.toLowerCase() === formData.value.name.trim().toLowerCase() && 
    (c.type === formData.value.type || c.type === 'both') &&
    c.id !== formData.value.id
  )
})

const openCreateModal = () => {
  formData.value = { id: null, name: '', type: 'expense', icon: '📝', color: '#10b981' }
  showModal.value = true
}

const startEdit = (cat) => {
  formData.value = { ...cat }
  showModal.value = true
}

const confirmDelete = (cat) => {
  catToDelete.value = cat
}

const deleteCategory = async () => {
  if (!catToDelete.value) return
  try {
    await api.delete(`/admin/categories/${catToDelete.value.id}`)
    catToDelete.value = null
    fetchCategories()
  } catch (err) {
    console.error(err)
  }
}

const submitForm = async () => {
  if (isDuplicateName.value) return
  try {
    if (formData.value.id) {
      await api.patch(`/admin/categories/${formData.value.id}`, formData.value)
    } else {
      await api.post('/admin/categories', formData.value)
    }
    showModal.value = false
    fetchCategories()
  } catch (err) {
    console.error(err)
    alert(t('failedCreateCategory') || 'Failed to save category')
  }
}
</script>

<template>
  <div class="admin-categories">
    <div class="page-header">
      <h1 class="page-title">{{ t('systemDefaultCategories') }}</h1>
      <button @click="openCreateModal" class="btn-primary">+ {{ t('newCategory') }}</button>
    </div>

    <div class="table-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>{{ t('icon') }}</th>
            <th>{{ t('name') }}</th>
            <th>{{ t('type') }}</th>
            <th>{{ t('color') }}</th>
            <th class="text-right">{{ t('actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center">Chargement...</td></tr>
          <tr v-for="cat in categories" :key="cat.id">
            <td>
              <div class="icon-circle" :style="{ background: cat.color + '22', color: cat.color }">
                {{ cat.icon }}
              </div>
            </td>
            <td>
              <span>{{ cat.name }}</span>
            </td>
            <td>
              <span class="badge" :class="'badge-' + cat.type">{{ cat.type === 'expense' ? t('expense') : (cat.type === 'income' ? t('income') : t('both')) }}</span>
            </td>
            <td>
              <div class="color-swatch" :style="{ background: cat.color }"></div>
            </td>
            <td class="text-right">
              <div class="admin-actions">
                <button @click="startEdit(cat)" class="admin-icon-btn" title="Modifier">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </button>
                <button @click="confirmDelete(cat)" class="admin-icon-btn admin-icon-delete" title="Supprimer">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Edit/Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content">
        <h3>{{ formData.id ? 'Modifier la catégorie' : t('newCategory') }}</h3>
        <form @submit.prevent="submitForm">
          <div class="form-group">
            <label>{{ t('name') }}</label>
            <input v-model="formData.name" required :class="{ 'input-error': isDuplicateName }" />
            <p v-if="isDuplicateName" class="inline-error">Une catégorie portant ce nom existe déjà.</p>
          </div>
          <div class="form-group">
            <label>{{ t('type') }}</label>
            <select v-model="formData.type">
              <option value="expense">{{ t('expense') }}</option>
              <option value="income">{{ t('income') }}</option>
              <option value="both">{{ t('both') }}</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>{{ t('icon') }}</label>
              <input v-model="formData.icon" required />
            </div>
            <div class="form-group">
              <label>{{ t('color') }}</label>
              <input type="color" v-model="formData.color" class="color-picker" />
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" @click="showModal = false" class="btn-cancel">{{ t('cancel') }}</button>
            <button type="submit" class="btn-confirm" :disabled="isDuplicateName">{{ t('save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Category Modal -->
    <div v-if="catToDelete" class="modal-overlay" @click.self="catToDelete = null">
      <div class="modal-content text-center" style="width: 340px;">
        <div style="width: 48px; height: 48px; background: #fef2f2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <h3 style="font-size: 1.15rem; color: #111827; margin-bottom: 8px;">Supprimer « {{ catToDelete.name }} » ?</h3>
        <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 24px;">Les transactions liées passeront en 'Sans catégorie'.</p>
        <div style="display: flex; gap: 12px; width: 100%;">
          <button type="button" class="btn-cancel" style="flex: 1;" @click="catToDelete = null">Annuler</button>
          <button type="button" class="btn-danger" style="flex: 1;" @click="deleteCategory">Supprimer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Common admin styles */
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.page-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #111827; }
.btn-primary { background: #111827; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-primary:hover { background: #1f2937; }

.table-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; overflow: hidden; }
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 16px 24px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.admin-table th { background: #f9fafb; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; font-weight: 600; }
.text-center { text-align: center; }
.text-right { text-align: right; }

.icon-circle { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.color-swatch { width: 24px; height: 24px; border-radius: 6px; }
.color-picker { width: 40px; height: 40px; border: none; padding: 0; background: none; cursor: pointer; }

.badge { padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
.badge-income { background: #dcfce7; color: #166534; }
.badge-expense { background: #fee2e2; color: #991b1b; }
.badge-both { background: #e0e7ff; color: #3730a3; }

.admin-actions { display: flex; gap: 8px; justify-content: flex-end; }
.admin-icon-btn { background: white; border: 1px solid #d1d5db; color: #4b5563; padding: 6px; border-radius: 6px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.admin-icon-btn:hover { background: #f3f4f6; color: #111827; border-color: #9ca3af; }
.admin-icon-delete:hover { background: #fee2e2; color: #ef4444; border-color: #fca5a5; }
.btn-danger { background: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-danger:hover { background: #dc2626; }

.modal-overlay { position: fixed; inset: 0; background: rgba(17, 24, 39, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; animation: fadeIn 0.2s; }
.modal-content { background: white; padding: 32px; border-radius: 16px; width: 400px; animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-content h3 { font-size: 1.25rem; margin-bottom: 16px; color: #111827; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #374151; }
.form-group input, .form-group select { padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; font-size: 0.95rem; transition: border-color 0.2s; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #111827; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px; }
.btn-cancel { padding: 8px 16px; border-radius: 8px; background: #f3f4f6; color: #4b5563; border: none; cursor: pointer; font-weight: 500; transition: background 0.2s; }
.btn-cancel:hover { background: #e5e7eb; }
.btn-confirm { padding: 8px 16px; border-radius: 8px; background: #111827; color: white; border: none; cursor: pointer; font-weight: 600; transition: background 0.2s; }
.btn-confirm:hover { background: #1f2937; }
.btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

.input-error { border-color: #ef4444 !important; animation: shake 0.4s; }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.inline-error { color: #ef4444; font-size: 0.8rem; margin-top: 4px; font-weight: 500; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
