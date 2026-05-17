<template>
  <div class="register-page">
    <div class="register-card">
      <div class="card-head">
        <div>
          <h1>ServiceAI Cloud</h1>
          <p class="subtitle">{{ $t('auth.registerSubtitle') }}</p>
        </div>

        <select :value="locale" class="locale-select" @change="changeLocale($event.target.value)">
          <option value="zh_TW">繁體中文</option>
          <option value="en">English</option>
        </select>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>{{ $t('auth.companyName') }}</label>
          <input v-model="form.company_name" type="text" :placeholder="$t('auth.companyNamePlaceholder')" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.name') }}</label>
          <input v-model="form.name" type="text" :placeholder="$t('auth.namePlaceholder')" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.email') }}</label>
          <input v-model="form.email" type="email" :placeholder="$t('auth.emailPlaceholder')" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.password') }}</label>
          <input v-model="form.password" type="password" :placeholder="$t('auth.passwordPlaceholder')" />
        </div>

        <div class="form-group">
          <label>{{ $t('auth.confirmPassword') }}</label>
          <input v-model="form.password_confirmation" type="password" :placeholder="$t('auth.confirmPasswordPlaceholder')" />
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? $t('auth.registering') : $t('auth.register') }}
        </button>
      </form>

      <div class="bottom-link">
        {{ $t('auth.hasAccount') }}
        <router-link to="/login">{{ $t('auth.goLogin') }}</router-link>
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
  company_name: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function changeLocale(value) {
  locale.value = value
  localStorage.setItem('locale', value)
}

async function handleRegister() {
  loading.value = true
  errorMessage.value = ''

  try {
    const registrationLocale = ['zh_TW', 'en'].includes(locale.value) ? locale.value : 'zh_TW'
    const validationMessage = validateForm()

    if (validationMessage) {
      errorMessage.value = validationMessage
      return
    }

    const res = await api.post('/register', {
      ...form,
      locale: registrationLocale,
    })

    localStorage.removeItem('token')
    localStorage.removeItem('user')
    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user', JSON.stringify(res.data.user))

    api.defaults.headers.common.Authorization = `Bearer ${res.data.token}`
    router.push('/app')
  } catch (error) {
    const data = error.response?.data

    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      errorMessage.value = data.errors[firstKey][0]
    } else {
      errorMessage.value = data?.message || t('auth.registerFailed')
    }
  } finally {
    loading.value = false
  }
}

function validateForm() {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!form.company_name.trim()) return t('auth.validation.companyNameRequired')
  if (!form.name.trim()) return t('auth.validation.nameRequired')
  if (!form.email.trim()) return t('auth.validation.emailRequired')
  if (!emailPattern.test(form.email.trim())) return t('auth.validation.emailInvalid')
  if (!form.password) return t('auth.validation.passwordRequired')
  if (form.password.length < 8) return t('auth.validation.passwordMin')
  if (!form.password_confirmation) return t('auth.validation.confirmPasswordRequired')
  if (form.password !== form.password_confirmation) return t('auth.validation.passwordConfirmed')

  return ''
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fb;
  padding: 24px;
}

.register-card {
  width: 100%;
  max-width: 460px;
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

@media (max-width: 520px) {
  .card-head {
    flex-direction: column;
  }
}
</style>
