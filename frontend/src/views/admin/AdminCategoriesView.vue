<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const { t } = useI18n()

const categories = ref([])
const userCategories = ref([])
const loading = ref(true)
const loadingUserCats = ref(true)

const showModal = ref(false)
const showEmojiPicker = ref(false)
const formData = ref({ id: null, name: '', type: 'expense', icon: '📝', color: '#10b981' })

const catToDelete = ref(null)
const userCatToDelete = ref(null)

const toasts = ref([])

const emojiOptions = ['🛒','💰','🏠','🚗','🍔','💊','🎮','📚','✈️','👕','🎁','💡','📱','🎬','⚽','🏥','💳','🎵','🐾','👶','💼','🔧','🎨','🌿','☕','🍕','🎂','📦','🚌','⛽','🏦','💸','📊','🎓','💎','🧾','🏋️','💇','🎪','🌍']

const presetColors = [
  '#10b981','#3b82f6','#f59e0b','#ef4444','#8b5cf6',
  '#ec4899','#14b8a6','#f97316','#6366f1','#84cc16',
  '#06b6d4','#e11d48','#a855f7','#f43f5e','#0ea5e9',
  '#22c55e','#fbbf24','#fb923c','#c084fc','#f472b6',
  '#2dd4bf','#facc15','#f87171','#a78bfa','#60a5fa',
  '#34d399','#fde047','#fca5a5','#e879f9','#93c5fd'
]

const showToast = (message, type = 'success') => {
  const id = Date.now()
  toasts.value.push({ id, message, type })
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }, 4000)
}

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

const fetchUserCategories = async () => {
  loadingUserCats.value = true
  try {
    const { data } = await api.get('/admin/user-categories')
    userCategories.value = data
  } catch (err) {
    console.error(err)
  } finally {
    loadingUserCats.value = false
  }
}

onMounted(() => {
  fetchCategories()
  fetchUserCategories()
})

const isDuplicateName = computed(() => {
  if (!formData.value.name.trim()) return false
  return categories.value.some(c =>
    c.name.toLowerCase() === formData.value.name.trim().toLowerCase() &&
    c.id !== formData.value.id
  )
})

const isDuplicateColor = computed(() => {
  if (!formData.value.color) return false
  return categories.value.some(c =>
    c.color && c.color.toLowerCase() === formData.value.color.toLowerCase() &&
    c.id !== formData.value.id
  )
})

const canSubmit = computed(() => !isDuplicateName.value && !isDuplicateColor.value && formData.value.name.trim() && formData.value.color)

const openCreateModal = () => {
  formData.value = { id: null, name: '', type: 'expense', icon: '📝', color: null }
  showEmojiPicker.value = false
  showModal.value = true
}

const startEdit = (cat) => {
  formData.value = { ...cat }
  showEmojiPicker.value = false
  showModal.value = true
}

const confirmDelete = (cat) => {
  catToDelete.value = cat
}

const deleteCategory = async () => {
  if (!catToDelete.value) return
  const name = catToDelete.value.name
  try {
    await api.delete(`/admin/categories/${catToDelete.value.id}`)
    catToDelete.value = null
    await fetchCategories()
    showToast(`Catégorie « ${name} » supprimée.`, 'success')
  } catch (err) {
    console.error(err)
    showToast('Échec de la suppression de la catégorie.', 'error')
  }
}

const submitForm = async () => {
  if (!canSubmit.value) return
  try {
    if (formData.value.id) {
      await api.patch(`/admin/categories/${formData.value.id}`, formData.value)
      showToast(`Catégorie « ${formData.value.name} » modifiée.`, 'success')
    } else {
      await api.post('/admin/categories', formData.value)
      showToast(`Catégorie « ${formData.value.name} » créée.`, 'success')
    }
    showModal.value = false
    showEmojiPicker.value = false
    await fetchCategories()
  } catch (err) {
    console.error(err)
    showToast(err?.response?.data?.message || 'Échec de création de catégorie.', 'error')
  }
}

const groupedUserCategories = computed(() => {
  const groups = {}
  userCategories.value.forEach(cat => {
    const key = cat.user_id
    if (!groups[key]) {
      groups[key] = { user: cat.user, cats: [] }
    }
    groups[key].cats.push(cat)
  })
  return Object.values(groups)
})

const confirmDeleteUserCat = (cat) => {
  userCatToDelete.value = cat
}

const deleteUserCategory = async () => {
  if (!userCatToDelete.value) return
  const name = userCatToDelete.value.name
  try {
    await api.delete(`/admin/user-categories/${userCatToDelete.value.id}`)
    userCatToDelete.value = null
    await fetchUserCategories()
    showToast(`Catégorie utilisateur « ${name} » supprimée.`, 'success')
  } catch (err) {
    console.error(err)
    showToast('Échec de la suppression.', 'error')
  }
}
</script>

<template>
  <div class="admin-categories">

    <!-- Toast Notifications -->
    <teleport to="body">
      <div class="toast-container">
        <transition-group name="toast">
          <div v-for="toast in toasts" :key="toast.id" class="toast" :class="'toast-' + toast.type">
            <div class="toast-icon">
              <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
              <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
            <span class="toast-msg">{{ toast.message }}</span>
          </div>
        </transition-group>
      </div>
    </teleport>

    <!-- Section 1: System Default Categories -->
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
            <td><span>{{ cat.name }}</span></td>
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

    <!-- Section 2: User Custom Categories -->
    <div class="section-divider">
      <h2 class="section-title">Catégories personnalisées par utilisateur</h2>
    </div>

    <div v-if="loadingUserCats" class="text-center" style="padding: 24px; color: #6b7280;">Chargement...</div>
    <div v-else-if="groupedUserCategories.length === 0" class="empty-user-cats">
      Aucune catégorie personnalisée créée par les utilisateurs.
    </div>
    <div v-else class="user-cats-groups">
      <div v-for="group in groupedUserCategories" :key="group.user?.id" class="user-group-card">
        <div class="user-group-header">
          <div class="user-avatar">{{ (group.user?.name || '?')[0].toUpperCase() }}</div>
          <div>
            <div class="user-group-name">{{ group.user?.name || 'Utilisateur inconnu' }}</div>
            <div class="user-group-email">{{ group.user?.email || '' }}</div>
          </div>
          <span class="user-cat-count">{{ group.cats.length }} catégorie{{ group.cats.length > 1 ? 's' : '' }}</span>
        </div>
        <table class="admin-table">
          <thead>
            <tr>
              <th>Icône</th>
              <th>Nom</th>
              <th>Type</th>
              <th>Couleur</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cat in group.cats" :key="cat.id">
              <td>
                <div class="icon-circle" :style="{ background: (cat.color || '#9ca3af') + '22', color: cat.color || '#9ca3af' }">
                  {{ cat.icon || '📁' }}
                </div>
              </td>
              <td>{{ cat.name }}</td>
              <td>
                <span class="badge" :class="'badge-' + cat.type">{{ cat.type === 'expense' ? t('expense') : (cat.type === 'income' ? t('income') : t('both')) }}</span>
              </td>
              <td>
                <div class="color-swatch" :style="{ background: cat.color || '#9ca3af' }"></div>
              </td>
              <td class="text-right">
                <button @click="confirmDeleteUserCat(cat)" class="admin-icon-btn admin-icon-delete" title="Supprimer">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit/Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false; showEmojiPicker = false">
      <div class="modal-content">
        <h3>{{ formData.id ? 'Modifier la catégorie' : t('newCategory') }}</h3>
        <form @submit.prevent="submitForm">

          <!-- Icon preview + name -->
          <div class="form-group">
            <label>{{ t('name') }}</label>
            <div class="icon-name-row" :class="{ 'input-error': isDuplicateName }">
              <div class="emoji-trigger" @click.stop="showEmojiPicker = !showEmojiPicker" :style="{ backgroundColor: formData.color + '25', color: formData.color }">
                {{ formData.icon }}
              </div>
              <input v-model="formData.name" required placeholder="Nom de la catégorie" class="name-input-inline" />
            </div>
            <p v-if="isDuplicateName" class="inline-error">Ce nom est déjà utilisé.</p>
            <transition name="dropdown-fade">
              <div class="emoji-picker-grid" v-if="showEmojiPicker" @click.stop>
                <button type="button" v-for="emoji in emojiOptions" :key="emoji" class="emoji-option" :class="{ selected: formData.icon === emoji }" @click="formData.icon = emoji; showEmojiPicker = false">
                  {{ emoji }}
                </button>
              </div>
            </transition>
          </div>

          <div class="form-group">
            <label>{{ t('type') }}</label>
            <select v-model="formData.type">
              <option value="expense">{{ t('expense') }}</option>
              <option value="income">{{ t('income') }}</option>
              <option value="both">{{ t('both') }}</option>
            </select>
          </div>

          <!-- Preset color grid -->
          <div class="form-group">
            <label>{{ t('color') }}</label>
            <div class="preset-colors">
              <button
                type="button"
                v-for="color in presetColors"
                :key="color"
                class="color-dot"
                :class="{ selected: formData.color === color }"
                :style="{ backgroundColor: color }"
                @click="formData.color = color"
              ></button>
            </div>
            <p v-if="isDuplicateColor" class="inline-error">Cette couleur est déjà utilisée par une autre catégorie. Veuillez en choisir une différente.</p>
          </div>

          <div class="modal-actions">
            <button type="button" @click="showModal = false; showEmojiPicker = false" class="btn-cancel">{{ t('cancel') }}</button>
            <button type="submit" class="btn-confirm" :disabled="!canSubmit">{{ t('save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete System Category Modal -->
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

    <!-- Confirm Delete User Category Modal -->
    <div v-if="userCatToDelete" class="modal-overlay" @click.self="userCatToDelete = null">
      <div class="modal-content text-center" style="width: 340px;">
        <div style="width: 48px; height: 48px; background: #fef2f2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <h3 style="font-size: 1.15rem; color: #111827; margin-bottom: 8px;">Supprimer « {{ userCatToDelete.name }} » ?</h3>
        <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 24px;">Cette catégorie personnalisée sera définitivement supprimée.</p>
        <div style="display: flex; gap: 12px; width: 100%;">
          <button type="button" class="btn-cancel" style="flex: 1;" @click="userCatToDelete = null">Annuler</button>
          <button type="button" class="btn-danger" style="flex: 1;" @click="deleteUserCategory">Supprimer</button>
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

/* Section divider */
.section-divider { margin: 40px 0 20px; }
.section-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #111827; }

/* User category groups */
.user-cats-groups { display: flex; flex-direction: column; gap: 20px; }
.user-group-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; overflow: hidden; }
.user-group-header { display: flex; align-items: center; gap: 12px; padding: 16px 24px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
.user-avatar { width: 36px; height: 36px; border-radius: 50%; background: #111827; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; flex-shrink: 0; }
.user-group-name { font-weight: 600; color: #111827; font-size: 0.95rem; }
.user-group-email { font-size: 0.8rem; color: #6b7280; }
.user-cat-count { margin-left: auto; font-size: 0.8rem; color: #6b7280; background: #e5e7eb; padding: 3px 10px; border-radius: 999px; }
.empty-user-cats { text-align: center; color: #9ca3af; padding: 32px; background: white; border-radius: 16px; border: 1px dashed #e5e7eb; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(17, 24, 39, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; animation: fadeIn 0.2s; }
.modal-content { background: white; padding: 32px; border-radius: 16px; width: 420px; animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-content h3 { font-size: 1.25rem; margin-bottom: 20px; color: #111827; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; position: relative; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: #374151; }
.form-group select { padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; font-size: 0.95rem; transition: border-color 0.2s; }
.form-group select:focus { outline: none; border-color: #111827; }
.modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px; }
.btn-cancel { padding: 8px 16px; border-radius: 8px; background: #f3f4f6; color: #4b5563; border: none; cursor: pointer; font-weight: 500; transition: background 0.2s; }
.btn-cancel:hover { background: #e5e7eb; }
.btn-confirm { padding: 8px 16px; border-radius: 8px; background: #111827; color: white; border: none; cursor: pointer; font-weight: 600; transition: background 0.2s; }
.btn-confirm:hover { background: #1f2937; }
.btn-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

/* Icon + name row */
.icon-name-row { display: flex; align-items: center; gap: 8px; border: 1px solid #d1d5db; border-radius: 8px; padding: 4px 8px 4px 4px; background: white; transition: border-color 0.2s; }
.icon-name-row:focus-within { border-color: #111827; }
.icon-name-row.input-error { border-color: #ef4444 !important; animation: shake 0.4s; }
.emoji-trigger { width: 36px; height: 36px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; cursor: pointer; flex-shrink: 0; transition: opacity 0.15s; }
.emoji-trigger:hover { opacity: 0.8; }
.name-input-inline { flex: 1; border: none; outline: none; font-size: 0.95rem; font-family: inherit; background: transparent; color: #111827; }

/* Emoji picker grid */
.emoji-picker-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 4px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px; max-height: 160px; overflow-y: auto; box-shadow: 0 4px 16px rgba(0,0,0,0.1); margin-top: 6px; z-index: 10; position: relative; }
.emoji-option { background: transparent; border: 1.5px solid transparent; border-radius: 6px; padding: 4px; font-size: 1.1rem; cursor: pointer; transition: all 0.15s; line-height: 1; }
.emoji-option:hover { background: #f3f4f6; border-color: #d1d5db; }
.emoji-option.selected { background: #eff6ff; border-color: #3b82f6; }

/* Preset color circles */
.preset-colors { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.color-dot { width: 28px; height: 28px; border-radius: 50%; border: 3px solid transparent; cursor: pointer; transition: all 0.15s; outline: none; padding: 0; }
.color-dot:hover { transform: scale(1.15); box-shadow: 0 0 0 2px rgba(0,0,0,0.2); }
.color-dot.selected { border-color: white; box-shadow: 0 0 0 3px rgba(0,0,0,0.35); transform: scale(1.1); }

.input-error { border-color: #ef4444 !important; animation: shake 0.4s; }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}
.inline-error { color: #ef4444; font-size: 0.8rem; margin-top: 4px; font-weight: 500; }

/* Dropdown fade animation */
.dropdown-fade-enter-active, .dropdown-fade-leave-active { transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-fade-enter-from, .dropdown-fade-leave-to { opacity: 0; transform: translateY(-5px) scale(0.98); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

/* Toast notifications */
.toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
.toast { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 10px; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.12); min-width: 280px; max-width: 360px; pointer-events: auto; border-left: 4px solid; }
.toast-success { border-left-color: #10b981; }
.toast-error { border-left-color: #ef4444; }
.toast-icon { flex-shrink: 0; }
.toast-success .toast-icon { color: #10b981; }
.toast-error .toast-icon { color: #ef4444; }
.toast-msg { font-size: 0.9rem; color: #111827; font-weight: 500; }
.toast-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from { opacity: 0; transform: translateX(30px); }
.toast-leave-to { opacity: 0; transform: translateX(30px); }
</style>
