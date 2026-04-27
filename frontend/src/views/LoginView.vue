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
      <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
        <rect width="32" height="32" rx="8" fill="#6f4ef2"/>
        <path d="M8 16h4l3-6 5 12 3-6h4" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span>{{ t('appName') }}</span>
    </div>

    <!-- Card -->
    <section class="login-card">
      <div class="login-card-header">
        <h2>{{ t('login') }}</h2>
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
  background: #faf8f5;
  padding: 24px;
}

/* ─── Logo ─── */
.login-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 32px;
}

.login-logo span {
  font-size: 1.5rem;
  font-weight: 700;
  color: #6f4ef2;
  letter-spacing: -0.3px;
}

/* ─── Card ─── */
.login-card {
  width: min(440px, 100%);
  background: white;
  border-radius: 16px;
  padding: 36px 32px 32px;
  box-shadow:
    0 1px 3px rgba(0,0,0,0.04),
    0 8px 24px rgba(0,0,0,0.06);
}

.login-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.login-card-header h2 {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1a1a2e;
}

/* ─── Divider ─── */
.login-divider {
  display: none;
}

/* ─── Form ─── */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1a1a2e;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-group input {
  width: 100%;
  border: 1.5px solid #e2e0dc;
  border-radius: 10px;
  padding: 13px 14px;
  font-size: 0.95rem;
  font-family: inherit;
  color: #1a1a2e;
  background: #faf8f5;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input::placeholder {
  color: #b0aca6;
}

.form-group input:focus {
  outline: none;
  border-color: #6f4ef2;
  box-shadow: 0 0 0 3px rgba(111, 78, 242, 0.1);
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
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 4px;
  color: #9b978f;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: color 0.2s;
}

.toggle-password:hover {
  color: #6f4ef2;
  background: none;
}

/* ─── Error ─── */
.login-error {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 14px;
  background: #fff5f5;
  border: 1px solid #fecaca;
  border-radius: 10px;
  font-size: 0.85rem;
  color: #dc2626;
  font-weight: 500;
}

/* ─── Submit ─── */
.login-submit {
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #f5a574, #ef8c5e);
  color: white;
  font-size: 1rem;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.2s, opacity 0.2s;
  margin-top: 4px;
}

.login-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(239, 140, 94, 0.35);
}

.login-submit:active:not(:disabled) {
  transform: translateY(0);
}

.login-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* ─── Spinner ─── */
.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2.5px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ─── Footer ─── */
.login-footer-text {
  text-align: center;
  margin-top: 24px;
  font-size: 0.9rem;
  color: #6b7280;
}

.login-link {
  color: #6f4ef2;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.login-link:hover {
  color: #5a3dd6;
  text-decoration: underline;
}

/* ─── Responsive ─── */
@media (max-width: 480px) {
  .login-card {
    padding: 28px 20px 24px;
    border-radius: 14px;
  }

  .login-logo span {
    font-size: 1.3rem;
  }
}
</style>
