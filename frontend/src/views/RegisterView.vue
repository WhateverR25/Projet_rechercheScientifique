<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../services/api'
import dashboardPreview from '../assets/dashboard-preview.png'

const router = useRouter()
const { t } = useI18n()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)

const submit = async () => {
  error.value = ''
  loading.value = true
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
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="register-page">
    <!-- Left: Form -->
    <section class="register-left">
      <div class="register-form-container">
        <!-- Logo -->
        <div class="register-logo">
          <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
            <rect width="32" height="32" rx="8" fill="#6f4ef2"/>
            <path d="M8 16h4l3-6 5 12 3-6h4" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>{{ t('appName') }}</span>
        </div>

        <h2 class="register-title">{{ t('register') }}</h2>

        <form @submit.prevent="submit" class="register-form" id="register-form">
          <div class="form-group">
            <label for="register-name">{{ t('name') }}</label>
            <input
              id="register-name"
              v-model="name"
              type="text"
              :placeholder="'John Doe'"
              required
              autocomplete="name"
            />
          </div>

          <div class="form-group">
            <label for="register-email">{{ t('email') }}</label>
            <input
              id="register-email"
              v-model="email"
              type="email"
              :placeholder="'email@address.com'"
              required
              autocomplete="email"
            />
          </div>

          <div class="form-group">
            <label for="register-password">{{ t('password') }}</label>
            <div class="password-field">
              <input
                id="register-password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showPassword = !showPassword"
              >
                <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="register-confirm-password">{{ t('confirmPassword') }}</label>
            <div class="password-field">
              <input
                id="register-confirm-password"
                v-model="passwordConfirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <svg v-if="!showConfirmPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <p v-if="error" class="register-error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            {{ error }}
          </p>

          <button type="submit" class="register-submit" :disabled="loading" id="register-submit-btn">
            <span v-if="loading" class="spinner"></span>
            <span v-else>Sign up with email</span>
          </button>
        </form>

        <p class="register-footer-text">
          Already have an account?
          <router-link to="/login" class="register-link" id="goto-login">{{ t('login') }}</router-link>
        </p>
      </div>
    </section>

    <!-- Right: Showcase -->
    <section class="register-right">
      <div class="showcase-content">
        <!-- Stars -->
        <div class="stars">
          <svg v-for="n in 5" :key="n" width="24" height="24" viewBox="0 0 24 24" fill="#f5a574">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        </div>

        <!-- Quote -->
        <blockquote class="testimonial-quote">
          "Finally, a budget tracker that's both<br>
          powerful and beautiful to use every day."
        </blockquote>
        <p class="testimonial-author">Sarah M.</p>

        <!-- Dashboard preview -->
        <div class="preview-wrapper">
          <div class="preview-browser">
            <div class="browser-dots">
              <span></span><span></span><span></span>
            </div>
            <img :src="dashboardPreview" alt="BudgetWise Dashboard Preview" class="preview-image" />
          </div>
        </div>

        <!-- Features -->
        <div class="feature-pills">
          <span class="pill">📊 Smart Reports</span>
          <span class="pill">🎯 Budget Goals</span>
          <span class="pill">🌍 Multi-language</span>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.register-page {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1fr 1fr;
  background: #faf8f5;
}

/* ─── LEFT: Form ─── */
.register-left {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.register-form-container {
  width: min(400px, 100%);
}

.register-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 32px;
}

.register-logo span {
  font-size: 1.35rem;
  font-weight: 700;
  color: #6f4ef2;
  letter-spacing: -0.3px;
}

.register-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 28px;
}

/* ─── Form ─── */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a1a2e;
}

.form-group input {
  width: 100%;
  border: 1.5px solid #e2e0dc;
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 0.95rem;
  font-family: inherit;
  color: #1a1a2e;
  background: white;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input::placeholder {
  color: #b0aca6;
}

.form-group input:focus {
  outline: none;
  border-color: #6f4ef2;
  box-shadow: 0 0 0 3px rgba(111, 78, 242, 0.1);
}

/* ─── Password ─── */
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
.register-error {
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
.register-submit {
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

.register-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 16px rgba(239, 140, 94, 0.35);
}

.register-submit:active:not(:disabled) {
  transform: translateY(0);
}

.register-submit:disabled {
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
.register-footer-text {
  text-align: center;
  margin-top: 24px;
  font-size: 0.9rem;
  color: #6b7280;
}

.register-link {
  color: #6f4ef2;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.register-link:hover {
  color: #5a3dd6;
  text-decoration: underline;
}

/* ─── RIGHT: Showcase ─── */
.register-right {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background: white;
  border-left: 1px solid #f0ece6;
}

.showcase-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  max-width: 480px;
}

/* ─── Stars ─── */
.stars {
  display: flex;
  gap: 4px;
}

/* ─── Testimonial ─── */
.testimonial-quote {
  font-size: 1.1rem;
  font-weight: 500;
  color: #1a1a2e;
  text-align: center;
  line-height: 1.6;
  border: none;
  padding: 0;
  margin: 0;
}

.testimonial-author {
  font-size: 0.95rem;
  color: #9b978f;
  font-style: italic;
  font-weight: 500;
}

/* ─── Preview ─── */
.preview-wrapper {
  width: 100%;
  margin-top: 8px;
}

.preview-browser {
  border-radius: 12px;
  overflow: hidden;
  box-shadow:
    0 4px 12px rgba(0,0,0,0.06),
    0 20px 40px rgba(0,0,0,0.1);
  border: 1px solid #e8e5e0;
}

.browser-dots {
  display: flex;
  gap: 6px;
  padding: 10px 14px;
  background: #f6f4f0;
  border-bottom: 1px solid #e8e5e0;
}

.browser-dots span {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.browser-dots span:nth-child(1) { background: #ff5f57; }
.browser-dots span:nth-child(2) { background: #ffbd2e; }
.browser-dots span:nth-child(3) { background: #27c93f; }

.preview-image {
  width: 100%;
  display: block;
}

/* ─── Feature pills ─── */
.feature-pills {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 4px;
}

.pill {
  background: #f6f4f0;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #4b5563;
  border: 1px solid #e8e5e0;
}

/* ─── Responsive ─── */
@media (max-width: 900px) {
  .register-page {
    grid-template-columns: 1fr;
  }

  .register-right {
    display: none;
  }

  .register-left {
    padding: 24px;
  }
}

@media (max-width: 480px) {
  .register-form-container {
    width: 100%;
  }
}
</style>
