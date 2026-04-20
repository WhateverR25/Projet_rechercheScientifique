<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'

const router = useRouter()
const { t } = useI18n()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')

const submit = async () => {
  error.value = ''
  try {
    const { data } = await api.post('/auth/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    localStorage.setItem('budgetwise_token', data.token)
    localStorage.setItem('budgetwise_user', JSON.stringify(data.user))
    router.push('/dashboard')
  } catch (err) {
    const apiErrors = err?.response?.data?.errors
    if (apiErrors && typeof apiErrors === 'object') {
      const firstKey = Object.keys(apiErrors)[0]
      error.value = apiErrors[firstKey]?.[0] || t('registerError')
      return
    }

    error.value = err?.response?.data?.message || t('registerError')
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card">
      <header class="auth-header">
        <h1>{{ t('appName') }}</h1>
      </header>

      <h2>{{ t('register') }}</h2>
      <form @submit.prevent="submit" class="auth-form">
        <label>{{ t('name') }}</label>
        <input v-model="name" type="text" required />
        <label>{{ t('email') }}</label>
        <input v-model="email" type="email" required />
        <label>{{ t('password') }}</label>
        <input v-model="password" type="password" required />
        <label>{{ t('confirmPassword') }}</label>
        <input v-model="passwordConfirmation" type="password" required />
        <p v-if="error" class="error">{{ error }}</p>
        <button type="submit">{{ t('register') }}</button>
      </form>
      <router-link to="/login">{{ t('login') }}</router-link>
    </section>
  </main>
</template>
