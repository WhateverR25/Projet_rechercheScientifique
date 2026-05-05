<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'

const router = useRouter()
const { t } = useI18n()
const email = ref('admin@gmail.com')
const password = ref('admin123456')
const error = ref('')
const loading = ref(false)

const submit = async () => {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/admin/login', { email: email.value, password: password.value })
    localStorage.setItem('admin_token', data.token)
    router.push('/admin/dashboard')
  } catch (err) {
    error.value = err?.response?.data?.message || 'Login failed'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <!-- Back Button -->
    <router-link to="/login" class="back-button">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg>
      <span>{{ t('adminBackUser') }}</span>
    </router-link>

    <div class="login-logo">
      <router-link to="/">
        <img src="/aurem-logo.png" alt="Aurem Admin" class="login-logo-img" />
      </router-link>
    </div>

    <section class="login-card">
      <div class="login-card-header">
        <h2>{{ t('adminPortal') }}</h2>
        <p class="login-subtitle">{{ t('connectToManage') }}</p>
      </div>

      <form @submit.prevent="submit" class="login-form">
        <div class="form-group">
          <label for="login-email">{{ t('email') }}</label>
          <input id="login-email" v-model="email" type="email" required />
        </div>

        <div class="form-group">
          <label for="login-password">{{ t('password') }}</label>
          <input id="login-password" v-model="password" type="password" required />
        </div>

        <p v-if="error" class="login-error">{{ error }}</p>

        <button type="submit" class="login-submit" :disabled="loading">
          <span v-if="loading" class="spinner"></span>
          <span v-else>{{ t('adminLoginButton') }}</span>
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #111827;
  padding: 24px;
  position: relative;
}

.back-button {
  position: absolute;
  top: 32px;
  left: 32px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #9ca3af;
  text-decoration: none;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  font-size: 0.95rem;
  padding: 10px 16px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(8px);
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  overflow: hidden;
}

.back-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(201, 168, 76, 0.15) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: -1;
  border-radius: 999px;
}

.back-button:hover {
  color: white;
  transform: translateX(-4px);
  border-color: rgba(201, 168, 76, 0.4);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.back-button:hover::before {
  opacity: 1;
}

.back-button svg {
  transition: transform 0.3s ease;
}

.back-button:hover svg {
  transform: translateX(-2px);
}

.login-logo {
  margin-bottom: 40px;
}

.login-logo-img {
  height: 160px;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5)) brightness(0) invert(1);
}

.login-card {
  width: min(400px, 100%);
  background: white;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

.login-card-header {
  margin-bottom: 24px;
}

.login-card-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  color: #111827;
  margin-bottom: 8px;
}

.login-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-weight: 600;
  font-size: 0.85rem;
  color: #374151;
}

.form-group input {
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
}

.form-group input:focus {
  border-color: #c9a84c;
  box-shadow: 0 0 0 2px rgba(201, 168, 76, 0.2);
}

.login-error {
  color: #dc2626;
  font-size: 0.85rem;
  background: #fef2f2;
  padding: 10px;
  border-radius: 8px;
}

.login-submit {
  background: #111827;
  color: white;
  padding: 14px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 10px;
}

.login-submit:hover {
  background: #1f2937;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
