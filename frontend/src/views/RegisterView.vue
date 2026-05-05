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
          <router-link to="/">
            <img src="/aurem-logo.png" alt="Aurem" class="register-logo-img" />
          </router-link>
        </div>

        <h2 class="register-title">{{ t('register') }}</h2>
        <p class="register-subtitle">Create your account to start tracking.</p>

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
            <span v-else>Create account</span>
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
          <svg v-for="n in 5" :key="n" width="22" height="22" viewBox="0 0 24 24" fill="#bfa167">
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
            <img :src="dashboardPreview" alt="Aurem Dashboard Preview" class="preview-image" />
          </div>
        </div>

        <!-- Features -->
        <div class="feature-pills">
          <span class="pill"><img src="../assets/icon2.png" alt="" class="pill-icon" /> Smart Reports</span>
          <span class="pill"><img src="../assets/icon3.png" alt="" class="pill-icon" /> Budget Goals</span>
          <span class="pill"><img src="../assets/icon1.png" alt="" class="pill-icon" /> Multi-language</span>
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
  background: linear-gradient(165deg, #faf9f7 0%, #f3f1ed 50%, #eae7e0 100%);
}

/* ─── LEFT: Form ─── */
.register-left {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 40px;
}

.register-form-container {
  width: min(420px, 100%);
}

.register-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 36px;
}

.register-logo-img {
  height: 130px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
}

.register-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: -0.4px;
  margin-bottom: 6px;
}

.register-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 400;
  margin-bottom: 32px;
  letter-spacing: -0.1px;
}

/* ─── Form ─── */
.register-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
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

.form-group input {
  width: 100%;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 13px 16px;
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

/* ─── Password ─── */
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
.register-error {
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
.register-submit {
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

.register-submit::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(191, 161, 103, 0.15), rgba(191, 161, 103, 0));
  opacity: 0;
  transition: opacity 0.3s;
}

.register-submit:hover:not(:disabled) {
  background: #1e293b;
  transform: translateY(-1px);
  box-shadow:
    0 4px 12px rgba(15, 23, 42, 0.2),
    0 8px 24px rgba(15, 23, 42, 0.12);
}

.register-submit:hover:not(:disabled)::after {
  opacity: 1;
}

.register-submit:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
}

.register-submit:disabled {
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

/* ─── Footer ─── */
.register-footer-text {
  text-align: center;
  margin-top: 28px;
  font-size: 0.9rem;
  color: #64748b;
}

.register-link {
  color: #0f172a;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
  border-bottom: 1px solid #cbd5e1;
  padding-bottom: 1px;
}

.register-link:hover {
  color: #bfa167;
  border-bottom-color: #bfa167;
}

/* ─── RIGHT: Showcase ─── */
.register-right {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 40px;
  background: #0f172a;
  position: relative;
  overflow: hidden;
}

.register-right::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -30%;
  width: 80%;
  height: 200%;
  background: radial-gradient(ellipse, rgba(191, 161, 103, 0.08), transparent 70%);
  pointer-events: none;
}

.showcase-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  max-width: 480px;
  position: relative;
  z-index: 1;
}

/* ─── Stars ─── */
.stars {
  display: flex;
  gap: 4px;
}

/* ─── Testimonial ─── */
.testimonial-quote {
  font-size: 1.15rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  text-align: center;
  line-height: 1.7;
  border: none;
  padding: 0;
  margin: 0;
  letter-spacing: -0.1px;
}

.testimonial-author {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.45);
  font-style: italic;
  font-weight: 500;
}

/* ─── Preview ─── */
.preview-wrapper {
  width: 100%;
  margin-top: 8px;
}

.preview-browser {
  border-radius: 14px;
  overflow: hidden;
  box-shadow:
    0 4px 16px rgba(0, 0, 0, 0.2),
    0 24px 48px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.browser-dots {
  display: flex;
  gap: 6px;
  padding: 10px 14px;
  background: rgba(255, 255, 255, 0.06);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
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
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.06);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(8px);
}

.pill-icon {
  width: 18px;
  height: 18px;
  object-fit: contain;
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

  .register-logo-img {
    height: 100px;
  }
}
</style>
