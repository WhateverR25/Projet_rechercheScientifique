<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const router = useRouter()
const { t } = useI18n()
const email = ref('')
const password = ref('')
const error = ref('')

const submit = async () => {
  error.value = ''
  try {
    const { data } = await api.post('/auth/login', { email: email.value, password: password.value })
    localStorage.setItem('budgetwise_token', data.token)
    localStorage.setItem('budgetwise_user', JSON.stringify(data.user))
    router.push('/dashboard')
  } catch (err) {
    error.value = err?.response?.data?.message || t('loginError')
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card">
      <header class="auth-header">
        <h1>{{ t('appName') }}</h1>
      </header>

      <h2>{{ t('login') }}</h2>
      <form @submit.prevent="submit" class="auth-form">
        <label>{{ t('email') }}</label>
        <input v-model="email" type="email" required />
        <label>{{ t('password') }}</label>
        <input v-model="password" type="password" required />
        <p v-if="error" class="error">{{ error }}</p>
        <button type="submit">{{ t('login') }}</button>
      </form>
      <router-link to="/register">{{ t('register') }}</router-link>
    </section>
  </main>
</template>
