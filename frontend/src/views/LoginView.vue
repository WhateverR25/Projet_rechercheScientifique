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
const showPassword = ref(false)
const loading = ref(false)

const submit = async () => {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/auth/login', { email: email.value, password: password.value })
    localStorage.setItem('budgetwise_token', data.token)
    localStorage.setItem('budgetwise_user', JSON.stringify(data.user))
    router.push('/dashboard')
  } catch (err) {
    error.value = err?.response?.data?.message || t('loginError')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <!-- Logo -->
    <div class="login-logo">
      <router-link to="/">
        <img src="/aurem-logo.png" alt="Aurem" class="login-logo-img" />
      </router-link>
    </div>

    <!-- Card -->
    <section class="login-card">
      <div class="login-card-header">
        <h2>{{ t('login') }}</h2>
        <p class="login-subtitle">Welcome back. Sign in to your account.</p>
      </div>

      <!-- Divider -->
      <div class="login-divider">
        <span>{{ t('email') }}</span>
      </div>

      <form @submit.prevent="submit" class="login-form" id="login-form">
        <div class="form-group">
          <label for="login-email">{{ t('email') }}</label>
          <input
            id="login-email"
            v-model="email"
            type="email"
            :placeholder="'email@address.com'"
            required
            autocomplete="email"
          />
        </div>

        <div class="form-group">
          <div class="label-row">
            <label for="login-password">{{ t('password') }}</label>
          </div>
          <div class="password-field">
            <input
              id="login-password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              class="toggle-password"
              @click="showPassword = !showPassword"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <!-- Eye icon -->
              <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <!-- Eye-off icon -->
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <p v-if="error" class="login-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
          </svg>
          {{ error }}
        </p>

        <button type="submit" class="login-submit" :disabled="loading" id="login-submit-btn">
          <span v-if="loading" class="spinner"></span>
          <span v-else>{{ t('login') }}</span>
        </button>
      </form>

      <p class="login-footer-text">
        Don't have an account?
        <router-link to="/register" class="login-link" id="goto-register">{{ t('register') }}</router-link>
      </p>
      
      <p class="login-footer-text admin-footer">
        <router-link to="/admin/login" class="login-link admin-link" id="goto-admin">Admin Access</router-link>
      </p>
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
  background: linear-gradient(165deg, #faf9f7 0%, #f3f1ed 50%, #eae7e0 100%);
  padding: 24px;
}

/* ─── Logo ─── */
.login-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 40px;
}

.login-logo-img {
  height: 160px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.06));
}

/* ─── Card ─── */
.login-card {
  width: min(460px, 100%);
  background: white;
  border-radius: 20px;
  padding: 40px 36px 36px;
  box-shadow:
    0 1px 2px rgba(0, 0, 0, 0.03),
    0 4px 12px rgba(0, 0, 0, 0.04),
    0 16px 40px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(0, 0, 0, 0.04);
}

.login-card-header {
  margin-bottom: 32px;
}

.login-card-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.4px;
  margin-bottom: 6px;
}

.login-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 400;
  letter-spacing: -0.1px;
}

/* ─── Divider ─── */
.login-divider {
  display: none;
}

/* ─── Form ─── */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.form-group label {
  font-size: 0.825rem;
  font-weight: 600;
  color: #334155;
  letter-spacing: 0.2px;
  text-transform: uppercase;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-group input {
  width: 100%;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 0.95rem;
  font-family: inherit;
  color: #0f172a;
  background: #f8fafc;
  transition: border-color 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.form-group input::placeholder {
  color: #94a3b8;
}

.form-group input:focus {
  outline: none;
  border-color: #1a1a2e;
  box-shadow: 0 0 0 3px rgba(26, 26, 46, 0.08);
  background: white;
}

/* ─── Password field ─── */
.password-field {
  position: relative;
}

.password-field input {
  padding-right: 48px;
}

.toggle-password {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 4px;
  color: #94a3b8;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: color 0.2s;
}

.toggle-password:hover {
  color: #0f172a;
  background: none;
}

/* ─── Error ─── */
.login-error {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  font-size: 0.85rem;
  color: #dc2626;
  font-weight: 500;
}

/* ─── Submit ─── */
.login-submit {
  width: 100%;
  padding: 15px;
  border: none;
  border-radius: 12px;
  background: #0f172a;
  color: white;
  font-size: 0.95rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  letter-spacing: 0.3px;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  margin-top: 6px;
  position: relative;
  overflow: hidden;
}

.login-submit::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(191, 161, 103, 0.15), rgba(191, 161, 103, 0));
  opacity: 0;
  transition: opacity 0.3s;
}

.login-submit:hover:not(:disabled) {
  background: #1e293b;
  transform: translateY(-1px);
  box-shadow:
    0 4px 12px rgba(15, 23, 42, 0.2),
    0 8px 24px rgba(15, 23, 42, 0.12);
}

.login-submit:hover:not(:disabled)::after {
  opacity: 1;
}

.login-submit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
}

.login-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ─── Spinner ─── */
.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2.5px solid rgba(255,255,255,0.25);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.login-footer-text {
  text-align: center;
  margin-top: 28px;
  font-size: 0.9rem;
  color: #64748b;
}

.admin-footer {
  margin-top: 12px;
  font-size: 0.8rem;
}

.admin-link {
  color: #94a3b8 !important;
  border-bottom: none !important;
}

.admin-link:hover {
  color: #0f172a !important;
}

.login-link {
  color: #0f172a;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
  border-bottom: 1px solid #cbd5e1;
  padding-bottom: 1px;
}

.login-link:hover {
  color: #bfa167;
  border-bottom-color: #bfa167;
}

/* ─── Responsive ─── */
@media (max-width: 480px) {
  .login-card {
    padding: 28px 22px 24px;
    border-radius: 16px;
  }

  .login-logo-img {
    height: 110px;
  }
}
</style>
