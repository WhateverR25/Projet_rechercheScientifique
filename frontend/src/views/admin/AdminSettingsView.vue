<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const maintenanceMode = ref(false)
const version = ref('1.0.0')
const saving = ref(false)
const clearing = ref(false)
const message = ref('')

onMounted(async () => {
  try {
    const { data } = await api.get('/admin/settings')
    maintenanceMode.value = data.maintenance_mode
    version.value = data.version
  } catch (err) {
    console.error(err)
  }
})

const saveSettings = async () => {
  saving.value = true
  message.value = ''
  try {
    await api.patch('/admin/settings', { maintenance_mode: maintenanceMode.value })
    message.value = 'Paramètres enregistrés avec succès.'
    setTimeout(() => { message.value = '' }, 3000)
  } catch (err) {
    console.error(err)
  } finally {
    saving.value = false
  }
}

const clearCache = async () => {
  clearing.value = true
  message.value = ''
  try {
    const { data } = await api.post('/admin/settings/clear-cache')
    message.value = data.message || 'Cache vidé avec succès.'
    setTimeout(() => { message.value = '' }, 3000)
  } catch (err) {
    console.error(err)
  } finally {
    clearing.value = false
  }
}
</script>

<template>
  <div class="admin-settings">
    <div class="page-header">
      <h1 class="page-title">Paramètres système</h1>
    </div>

    <div class="settings-card">
      <div class="setting-item">
        <div class="setting-info">
          <h3>Mode Maintenance</h3>
          <p>Active un écran de maintenance pour tous les utilisateurs normaux. L'accès administrateur reste possible.</p>
        </div>
        <div class="toggle-switch">
          <input type="checkbox" id="maintenance" v-model="maintenanceMode" class="toggle-input" />
          <label for="maintenance" class="toggle-label"></label>
        </div>
      </div>

      <div class="setting-item">
        <div class="setting-info">
          <h3>Version de l'application</h3>
          <p>Version actuelle d'Aurem.</p>
        </div>
        <div class="version-badge">v{{ version }}</div>
      </div>

      <div class="setting-item">
        <div class="setting-info">
          <h3>Vider le cache</h3>
          <p>Efface le cache de configuration et de vues du système. Utile après des modifications.</p>
        </div>
        <button @click="clearCache" class="btn-clear" :disabled="clearing">
          {{ clearing ? 'Vidage...' : 'Vider le cache' }}
        </button>
      </div>

      <div class="settings-footer">
        <span class="success-msg" v-if="message">{{ message }}</span>
        <button @click="saveSettings" class="btn-save" :disabled="saving">
          {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-header { margin-bottom: 32px; }
.page-title { font-family: 'Playfair Display', serif; font-size: 2rem; color: #111827; }

.settings-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; padding: 32px; }

.setting-item { display: flex; justify-content: space-between; align-items: center; padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; }

.setting-info h3 { font-size: 1.1rem; color: #111827; margin-bottom: 8px; }
.setting-info p { color: #6b7280; font-size: 0.9rem; max-width: 500px; }

/* Toggle Switch */
.toggle-switch { position: relative; width: 50px; height: 26px; }
.toggle-input { opacity: 0; width: 0; height: 0; }
.toggle-label { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 34px; }
.toggle-label:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
.toggle-input:checked + .toggle-label { background-color: #10b981; }
.toggle-input:checked + .toggle-label:before { transform: translateX(24px); }

.version-badge { background: #f3f4f6; color: #374151; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-family: monospace; }

.btn-clear { background: white; border: 1px solid #d1d5db; color: #374151; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-clear:hover:not(:disabled) { background: #f3f4f6; }
.btn-clear:disabled { opacity: 0.5; }

.settings-footer { display: flex; justify-content: flex-end; align-items: center; gap: 16px; margin-top: 16px; }
.btn-save { background: #111827; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-save:hover:not(:disabled) { background: #1f2937; }
.btn-save:disabled { opacity: 0.7; cursor: not-allowed; }
.success-msg { color: #10b981; font-weight: 500; font-size: 0.9rem; }
</style>
