<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="card-head">
        <div>
          <h1>{{ $t('auth.forgotTitle') }}</h1>
          <p class="subtitle">{{ $t('auth.forgotSubtitle') }}</p>
        </div>

        <select :value="locale" class="locale-select" @change="changeLocale($event.target.value)">
          <option value="zh_TW">繁體中文</option>
          <option value="en">English</option>
        </select>
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label>{{ $t('auth.email') }}</label>
          <input v-model="email" type="email" :placeholder="$t('auth.emailPlaceholder')" />
        </div>

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? $t('auth.sendingResetLink') : $t('auth.sendResetLink') }}
        </button>
      </form>

      <div class="bottom-link">
        <router-link to="/login">{{ $t('auth.backToLogin') }}</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'

const { t, locale } = useI18n()
const email = ref('')
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

function changeLocale(value) {
  locale.value = value
  localStorage.setItem('locale', value)
  successMessage.value = ''
  errorMessage.value = ''
}

async function submit() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const validationMessage = validateEmail()

    if (validationMessage) {
      errorMessage.value = validationMessage
      return
    }

    const requestLocale = ['zh_TW', 'en'].includes(locale.value) ? locale.value : 'zh_TW'
    const res = await api.post('/forgot-password', {
      email: email.value,
      locale: requestLocale,
    })
    successMessage.value = res.data.message || t('auth.resetLinkSent')
  } catch (error) {
    const data = error.response?.data

    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      errorMessage.value = data.errors[firstKey][0]
    } else {
      errorMessage.value = data?.message || t('auth.forgotPasswordFailed')
    }
  } finally {
    loading.value = false
  }
}

function validateEmail() {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!email.value.trim()) return t('auth.validation.emailRequired')
  if (!emailPattern.test(email.value.trim())) return t('auth.validation.emailInvalid')

  return ''
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fb;
  padding: 24px;
}
.auth-card {
  width: 100%;
  max-width: 440px;
  background: #fff;
  border-radius: 16px;
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
h1 { margin: 0 0 8px; }
.subtitle { margin: 0; color: #666; }
.locale-select {
  border: 1px solid #dcdfe6;
  border-radius: 8px;
  padding: 8px 10px;
  background: #fff;
}
.form-group { margin-bottom: 16px; }
label { display: block; margin-bottom: 8px; font-size: 14px; }
input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #dcdfe6;
  border-radius: 10px;
  padding: 12px 14px;
}
button {
  width: 100%;
  border: 0;
  border-radius: 10px;
  padding: 12px 14px;
  background: #111827;
  color: #fff;
  cursor: pointer;
}
.success-message { color: #15803d; margin-bottom: 16px; }
.error-message { color: #dc2626; margin-bottom: 16px; }
.bottom-link { margin-top: 18px; text-align: center; }
@media (max-width: 520px) {
  .card-head {
    flex-direction: column;
  }
}
</style>
