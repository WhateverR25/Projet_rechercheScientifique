<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const { t, locale } = useI18n()
const router = useRouter()
const user = ref(JSON.parse(localStorage.getItem('budgetwise_user') || 'null'))

const setLocale = (nextLocale) => {
  locale.value = nextLocale
  localStorage.setItem('budgetwise_locale', nextLocale)
}

const logout = async () => {
  try { await api.post('/auth/logout') } catch {}
  localStorage.removeItem('budgetwise_token')
  localStorage.removeItem('budgetwise_user')
  router.push('/login')
}

onMounted(async () => {
  try {
    const { data } = await api.get('/auth/me')
    user.value = data
    localStorage.setItem('budgetwise_user', JSON.stringify(data))
  } catch {}
})
</script>

<template>
  <div class="settings-view">
    <header class="top-bar">
      <h1>{{ t('settings') }}</h1>
      <p class="subtitle">{{ t('settingsSubtitle') }}</p>
    </header>

    <div class="settings-grid">
      <section class="card">
        <h2>{{ t('account') }}</h2>
        <div class="field">
          <label>{{ t('name') }}</label>
          <p>{{ user?.name || '-' }}</p>
        </div>
        <div class="field">
          <label>{{ t('email') }}</label>
          <p>{{ user?.email || '-' }}</p>
        </div>
      </section>

      <section class="card">
        <h2>{{ t('language') }}</h2>
        <div class="lang-actions">
          <button type="button" :class="{ active: locale === 'en' }" @click="setLocale('en')">English</button>
          <button type="button" :class="{ active: locale === 'fr' }" @click="setLocale('fr')">Francais</button>
        </div>
      </section>
    </div>

    <section class="card">
      <h2>{{ t('profile') }}</h2>
      <p class="subtitle">{{ t('sessionExpired') }}</p>
      <button class="danger" @click="logout">{{ t('signOut') }}</button>
    </section>
  </div>
</template>

<style scoped>
.settings-view { display: flex; flex-direction: column; gap: 24px; }
.subtitle { color: #6b7280; margin-top: 4px; }
.settings-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.card { background: white; border: 1px solid #f3f4f6; border-radius: 16px; padding: 20px; }
.card h2 { margin-bottom: 14px; }
.field { margin-bottom: 12px; }
.field label { display: block; color: #6b7280; margin-bottom: 4px; font-size: 0.85rem; }
.field p { color: #111827; font-weight: 500; }
.lang-actions { display: flex; gap: 10px; }
.lang-actions button { background: #f3f4f6; color: #111827; }
.lang-actions button.active { background: #6f4ef2; color: #fff; }
.danger { background: #dc2626; margin-top: 10px; }
.danger:hover { background: #b91c1c; }
@media (max-width: 900px) {
  .settings-grid { grid-template-columns: 1fr; }
}
</style>