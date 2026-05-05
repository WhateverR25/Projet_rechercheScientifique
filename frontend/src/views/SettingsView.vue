<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const { t, locale } = useI18n()
const router = useRouter()
const user = ref(JSON.parse(localStorage.getItem('budgetwise_user') || 'null'))

const errorMsg = ref('')

const editing = ref(false)
const editForm = ref({ name: '', email: '' })
const isChangingLang = ref(false)

const setLocale = (nextLocale) => {
  if (locale.value === nextLocale) return
  isChangingLang.value = true
  setTimeout(() => {
    locale.value = nextLocale
    localStorage.setItem('budgetwise_locale', nextLocale)
    isChangingLang.value = false
  }, 150)
}

const startEdit = () => {
  editForm.value = { name: user.value?.name || '', email: user.value?.email || '' }
  editing.value = true
}

const saveEdit = async () => {
  try {
    user.value = { ...user.value, ...editForm.value }
    localStorage.setItem('budgetwise_user', JSON.stringify(user.value))
    editing.value = false
    await api.put('/auth/profile', editForm.value).catch(() => {})
  } catch (err) {
    errorMsg.value = err?.response?.data?.message || t('errorSaving', 'Erreur de sauvegarde')
  }
}



const initials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name.substring(0, 2).toUpperCase()
})

onMounted(async () => {
  try {
    const { data } = await api.get('/auth/me')
    user.value = data
    localStorage.setItem('budgetwise_user', JSON.stringify(data))
  } catch (err) {
    errorMsg.value = t('sessionExpired')
  }
})
</script>

<template>
  <div class="settings-view">
    <transition name="fade">
      <div v-if="errorMsg" class="toast-banner error">
        <span>{{ errorMsg }}</span>
        <button class="close-toast" @click="errorMsg = ''">×</button>
      </div>
    </transition>

    <div class="page-content" :class="{ 'fading-text': isChangingLang }">
      <header class="top-bar">
        <h1>{{ t('settings') }}</h1>
        <p class="subtitle">{{ t('settingsSubtitle') }}</p>
      </header>

      <div class="settings-grid">
        <section class="card account-card">
          <div class="card-header">
            <h2>{{ t('account') }}</h2>
            <button v-if="!editing" class="btn-text" @click="startEdit">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
              Modifier
            </button>
          </div>

          <div class="account-content">
            <div class="avatar-circle">
              {{ initials }}
            </div>
            
            <div class="account-details" v-if="!editing">
              <div class="field">
                <label>{{ t('name') }}</label>
                <p>{{ user?.name || '-' }}</p>
              </div>
              <div class="field">
                <label>{{ t('email') }}</label>
                <p>{{ user?.email || '-' }}</p>
              </div>
            </div>

            <form v-else class="account-edit-form" @submit.prevent="saveEdit">
              <div class="field">
                <label>{{ t('name') }}</label>
                <input v-model="editForm.name" type="text" required class="input-field" />
              </div>
              <div class="field">
                <label>{{ t('email') }}</label>
                <input v-model="editForm.email" type="email" required class="input-field" />
              </div>
              <div class="edit-actions">
                <button type="button" class="btn-cancel" @click="editing = false">Annuler</button>
                <button type="submit" class="btn-save">Enregistrer</button>
              </div>
            </form>
          </div>
        </section>

        <section class="card language-card">
          <h2>{{ t('language') }}</h2>
          <div class="lang-switcher" :class="locale">
            <div class="lang-pill"></div>
            <button type="button" :class="{ active: locale === 'en' }" @click="setLocale('en')">English</button>
            <button type="button" :class="{ active: locale === 'fr' }" @click="setLocale('fr')">Français</button>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.settings-view { display: flex; flex-direction: column; gap: 24px; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.toast-banner { background: #fee2e2; color: #dc2626; padding: 12px 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; font-weight: 500; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.1); }
.toast-banner .close-toast { background: transparent; border: none; color: #dc2626; font-size: 1.25rem; padding: 0 4px; cursor: pointer; }

.subtitle { color: #6b7280; margin-top: 4px; }
.settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: stretch; }

.card { background: white; border: 1px solid #f3f4f6; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.card h2 { margin-bottom: 0; font-size: 1.125rem; color: #111827; }

.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.btn-text { background: transparent; color: #0f172a; font-weight: 600; font-size: 0.9rem; padding: 6px 12px; border-radius: 8px; display: flex; align-items: center; gap: 6px; transition: background 0.2s; border: none; cursor: pointer; }
.btn-text:hover { background: #f1f5f9; }

.account-content { display: flex; gap: 24px; align-items: flex-start; }
.avatar-circle { width: 64px; height: 64px; border-radius: 50%; background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; flex-shrink: 0; }
.account-details, .account-edit-form { flex: 1; display: flex; flex-direction: column; gap: 16px; }

.field label { display: block; color: #64748b; margin-bottom: 4px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.field p { color: #111827; font-weight: 500; font-size: 1rem; margin: 0; }

.input-field { width: 100%; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 0.95rem; background: #f8fafc; transition: all 0.2s; }
.input-field:focus { outline: none; border-color: #0f172a; background: white; box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1); }

.edit-actions { display: flex; gap: 12px; margin-top: 8px; }
.btn-cancel, .btn-save, .btn-danger-confirm { padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 0.95rem; transition: all 0.2s; cursor: pointer; border: none; }
.btn-cancel { background: white; color: #4b5563; border: 1px solid #d1d5db; }
.btn-cancel:hover { background: #f9fafb; color: #111827; }
.btn-save { background: #0f172a; color: white; border: none; }
.btn-save:hover { background: #1e293b; }

.language-card h2 { margin-bottom: 20px; }
.lang-switcher { display: flex; position: relative; background: #f3f4f6; border-radius: 12px; padding: 4px; width: 100%; }
.lang-pill { position: absolute; top: 4px; left: 4px; width: calc(50% - 4px); height: calc(100% - 8px); background: #0f172a; border-radius: 10px; transition: transform 300ms cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 6px rgba(15, 23, 42, 0.2); }
.lang-switcher.fr .lang-pill { transform: translateX(100%); }
.lang-switcher button { position: relative; z-index: 2; flex: 1; background: transparent; color: #4b5563; font-weight: 600; border-radius: 10px; padding: 12px; border: none; cursor: pointer; transition: color 300ms ease; text-align: center; }
.lang-switcher button.active { color: white; }

.page-content { transition: opacity 150ms ease; display: flex; flex-direction: column; gap: 24px; }
.page-content.fading-text { opacity: 0; }

@media (max-width: 900px) {
  .settings-grid { grid-template-columns: 1fr; }
  .account-content { flex-direction: column; align-items: center; text-align: center; }
  .account-edit-form { width: 100%; text-align: left; }
  .edit-actions { justify-content: center; }
}
</style>