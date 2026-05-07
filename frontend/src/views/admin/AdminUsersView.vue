<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'

const users = ref([])
const search = ref('')
const loading = ref(true)
const pagination = ref({ current: 1, last: 1 })

const showModal = ref(false)
const selectedUser = ref(null)
const showProfileModal = ref(false)
const profileData = ref(null)

const fetchUsers = async (page = 1) => {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/users?page=${page}&search=${search.value}`)
    users.value = data.data
    pagination.value = { current: data.current_page, last: data.last_page }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

let timeoutId = null
watch(search, () => {
  clearTimeout(timeoutId)
  timeoutId = setTimeout(() => {
    fetchUsers(1)
  }, 300)
})

onMounted(() => fetchUsers())

const toggleStatus = async (user) => {
  try {
    if (user.status === 'inactive') {
      await api.patch(`/admin/users/${user.id}/reactivate`)
      user.status = 'active'
    } else {
      await api.patch(`/admin/users/${user.id}/deactivate`)
      user.status = 'inactive'
    }
  } catch (err) {
    console.error(err)
  }
}

const confirmDelete = (user) => {
  selectedUser.value = user
  showModal.value = true
}

const executeDelete = async () => {
  if (!selectedUser.value) return
  try {
    await api.delete(`/admin/users/${selectedUser.value.id}`)
    showModal.value = false
    fetchUsers(pagination.value.current)
  } catch (err) {
    console.error('Failed to delete user:', err)
    alert('Failed to delete user. Please try again.')
  }
}

const viewProfile = async (user) => {
  try {
    const { data } = await api.get(`/admin/users/${user.id}`)
    profileData.value = data
    showProfileModal.value = true
  } catch (err) {
    console.error(err)
  }
}

const getInitials = (name) => {
  return name.substring(0, 2).toUpperCase()
}
</script>

<template>
  <div class="admin-users">
    <div class="page-header">
      <h1 class="page-title">Utilisateurs</h1>
      <input 
        v-model="search" 
        type="text" 
        placeholder="Rechercher par nom ou email..." 
        class="search-input"
      />
    </div>

    <div class="table-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Utilisateur</th>
            <th>Email</th>
            <th>Inscrit le</th>
            <th>Transactions</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center">Chargement...</td></tr>
          <tr v-else-if="users.length === 0"><td colspan="6" class="text-center">Aucun utilisateur trouvé.</td></tr>
          <tr v-for="user in users" :key="user.id">
            <td>
              <div class="user-cell">
                <div class="avatar">{{ getInitials(user.name) }}</div>
                <span class="user-name">{{ user.name }}</span>
              </div>
            </td>
            <td>{{ user.email }}</td>
            <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
            <td>{{ user.transactions_count }}</td>
            <td>
              <span class="badge" :class="user.status === 'active' ? 'badge-active' : 'badge-inactive'">
                {{ user.status === 'active' ? 'Actif' : 'Inactif' }}
              </span>
            </td>
            <td class="actions-cell">
              <button @click="viewProfile(user)" class="btn-action" title="Voir profil">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
              <button @click="confirmDelete(user)" class="btn-action btn-danger" title="Supprimer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination" v-if="pagination.last > 1">
        <button 
          :disabled="pagination.current === 1" 
          @click="fetchUsers(pagination.current - 1)"
        >Précédent</button>
        <span>Page {{ pagination.current }} / {{ pagination.last }}</span>
        <button 
          :disabled="pagination.current === pagination.last" 
          @click="fetchUsers(pagination.current + 1)"
        >Suivant</button>
      </div>
    </div>

    <!-- Modal Delete -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content">
        <h3>Confirmer la suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer <strong>{{ selectedUser?.name }}</strong> ? Cette action est irréversible et supprimera toutes ses données (budgets, transactions).</p>
        <div class="modal-actions">
          <button @click="showModal = false" class="btn-cancel">Annuler</button>
          <button @click="executeDelete" class="btn-confirm">Supprimer</button>
        </div>
      </div>
    </div>

    <!-- Profile Modal -->
    <div v-if="showProfileModal" class="modal-overlay" @click.self="showProfileModal = false">
      <div class="modal-content profile-modal">
        <h3>Profil Utilisateur</h3>
        <div v-if="profileData" class="profile-details">
          <p><strong>Nom:</strong> {{ profileData.name }}</p>
          <p><strong>Email:</strong> {{ profileData.email }}</p>
          <p><strong>Statut:</strong> {{ profileData.status }}</p>
          <p><strong>Inscrit le:</strong> {{ new Date(profileData.created_at).toLocaleString() }}</p>
          <p><strong>Dernière connexion:</strong> {{ profileData.last_login_at ? new Date(profileData.last_login_at).toLocaleString() : 'Jamais' }}</p>
          <p><strong>Total Revenus:</strong> {{ profileData.incomes_count }}</p>
          <p><strong>Total Dépenses:</strong> {{ profileData.expenses_count }}</p>
        </div>
        <div class="modal-actions">
          <button @click="showProfileModal = false" class="btn-cancel">Fermer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
.page-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #111827; }

.search-input { padding: 10px 16px; border: 1px solid #d1d5db; border-radius: 8px; width: 300px; outline: none; }
.search-input:focus { border-color: #c9a84c; }

.table-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; overflow: hidden; }

.admin-table { width: 100%; border-collapse: collapse; }
.admin-table th, .admin-table td { padding: 16px 24px; text-align: left; border-bottom: 1px solid #e5e7eb; }
.admin-table th { background: #f9fafb; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
.admin-table td { font-size: 0.95rem; color: #374151; }

.user-cell { display: flex; align-items: center; gap: 12px; }
.avatar { width: 32px; height: 32px; background: #111827; color: #c9a84c; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; }
.user-name { font-weight: 600; }

.badge { padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
.badge-active { background: #dcfce7; color: #166534; }
.badge-inactive { background: #fee2e2; color: #991b1b; }

.actions-cell { display: flex; gap: 8px; }
.btn-action { background: transparent; border: none; color: #6b7280; cursor: pointer; padding: 6px; border-radius: 6px; transition: all 0.2s; font-size: 1.1rem;}
.btn-action:hover { background: #f3f4f6; }
.btn-danger:hover { background: #fef2f2; }

.pagination { padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e5e7eb; background: #f9fafb; }
.pagination button { padding: 8px 16px; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer; }
.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }

.text-center { text-align: center; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(17, 24, 39, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 32px; border-radius: 16px; width: 400px; }
.modal-content h3 { font-size: 1.25rem; margin-bottom: 16px; }

.profile-details p { margin-bottom: 8px; font-size: 0.95rem; }

.modal-actions { display: flex; gap: 12px; margin-top: 24px; justify-content: flex-end; }
.modal-actions button { padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; }
.btn-cancel { background: #f3f4f6; color: #374151; }
.btn-confirm { background: #dc2626; color: white; }
</style>
