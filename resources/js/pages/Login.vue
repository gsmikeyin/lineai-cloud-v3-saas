<template>
  <div class="login-page">
    <div class="login-card">
      <div class="card-head">
        <div>
          <h1>ServiceAI Cloud</h1>
          <p class="subtitle">{{ $t('auth.loginSubtitle') }}</p>
        </div>

        <select :value="locale" class="locale-select" @change="changeLocale($event.target.value)">
          <option value="zh_TW">繁中</option>
          <option value="en">English</option>
        </select>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>{{ $t('auth.email') }}</label>
          <input v-model="form.email" type="email" :placeholder="$t('auth.emailPlaceholder')" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.password') }}</label>
          <input v-model="form.password" type="password" :placeholder="$t('auth.passwordPlaceholder')" />
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? $t('auth.loggingIn') : $t('auth.login') }}
        </button>
      </form>

      <div class="login-actions">
        <button class="line-btn" type="button" @click="loginWithLine">
          {{ $t('auth.loginWithLine') }}
        </button>
      </div>

      <div class="bottom-link">
        <router-link to="/forgot-password">{{ $t('auth.forgotPassword') }}</router-link>
      </div>

      <div class="bottom-link">
        {{ $t('auth.noAccount') }}
        <router-link to="/register">{{ $t('auth.createAccount') }}</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const { t, locale } = useI18n()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: '',
})

function changeLocale(value) {
  locale.value = value
  localStorage.setItem('locale', value)
}

function loginWithLine() {
  window.location.href = '/auth/line/redirect'
}

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/login', {
      email: form.email,
      password: form.password,
    })

    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user', JSON.stringify(res.data.user))

    router.push('/app')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('auth.loginFailed')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fb;
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  border-radius: 8px;
  padding: 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.card-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 24px;
}

h1 {
  margin: 0 0 8px;
  font-size: 28px;
}

.subtitle {
  margin: 0;
  color: #666;
}

.locale-select {
  border: 1px solid #dcdfe6;
  border-radius: 8px;
  padding: 8px 10px;
  background: #fff;
}

.form-group {
  margin-bottom: 16px;
}

label {
  display: block;
  font-size: 14px;
  margin-bottom: 8px;
  color: #333;
}

input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #dcdfe6;
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 14px;
}

button {
  width: 100%;
  border: 0;
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 15px;
  cursor: pointer;
  background: #111827;
  color: #fff;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  margin-bottom: 16px;
  color: #d93025;
  font-size: 14px;
}

.bottom-link {
  margin-top: 18px;
  font-size: 14px;
  color: #666;
  text-align: center;
}

.bottom-link a {
  color: #2563eb;
  text-decoration: none;
}

.line-btn {
  background: #06c755;
  margin-top: 12px;
}

@media (max-width: 520px) {
  .card-head {
    flex-direction: column;
  }
}
</style>
