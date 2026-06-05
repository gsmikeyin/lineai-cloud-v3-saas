<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="card-head">
        <div>
          <h1>{{ $t('auth.resetTitle') }}</h1>
          <p class="subtitle">{{ $t('auth.resetSubtitle') }}</p>
        </div>

        <select :value="locale" class="locale-select" @change="changeLocale($event.target.value)">
          <option value="zh_TW">繁體中文</option>
          <option value="en">English</option>
        </select>
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label>{{ $t('auth.email') }}</label>
          <input v-model="form.email" type="email" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.newPassword') }}</label>
          <input v-model="form.password" type="password" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.confirmNewPassword') }}</label>
          <input v-model="form.password_confirmation" type="password" />
        </div>

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? $t('auth.resettingPassword') : $t('auth.resetPassword') }}
        </button>
      </form>

      <div class="bottom-link">
        <router-link to="/login">{{ $t('auth.backToLogin') }}</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const form = reactive({
  token: route.query.token || '',
  email: route.query.email || '',
  password: '',
  password_confirmation: '',
})

onMounted(() => {
  if (['zh_TW', 'en'].includes(route.query.locale)) {
    locale.value = route.query.locale
    localStorage.setItem('locale', route.query.locale)
  }
})

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
    const validationMessage = validateForm()

    if (validationMessage) {
      errorMessage.value = validationMessage
      return
    }

    const requestLocale = ['zh_TW', 'en'].includes(locale.value) ? locale.value : 'zh_TW'
    const res = await api.post('/reset-password', {
      ...form,
      locale: requestLocale,
    })
    successMessage.value = res.data.message || t('auth.passwordReset')

    setTimeout(() => {
      router.push('/login')
    }, 1200)
  } catch (error) {
    const data = error.response?.data

    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      errorMessage.value = data.errors[firstKey][0]
    } else {
      errorMessage.value = data?.message || t('auth.resetPasswordFailed')
    }
  } finally {
    loading.value = false
  }
}

function validateForm() {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!String(form.token || '').trim()) return t('auth.validation.resetTokenRequired')
  if (!String(form.email || '').trim()) return t('auth.validation.emailRequired')
  if (!emailPattern.test(String(form.email || '').trim())) return t('auth.validation.emailInvalid')
  if (!form.password) return t('auth.validation.passwordRequired')
  if (form.password.length < 8) return t('auth.validation.passwordMin')
  if (!form.password_confirmation) return t('auth.validation.confirmPasswordRequired')
  if (form.password !== form.password_confirmation) return t('auth.validation.passwordConfirmed')

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
