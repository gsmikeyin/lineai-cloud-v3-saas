<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>重設密碼</h1>
      <p class="subtitle">請輸入新密碼。</p>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" />
        </div>

        <div class="form-group">
          <label>新密碼</label>
          <input v-model="form.password" type="password" />
        </div>

        <div class="form-group">
          <label>確認新密碼</label>
          <input v-model="form.password_confirmation" type="password" />
        </div>

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? '送出中...' : '重設密碼' }}
        </button>
      </form>

      <div class="bottom-link">
        <router-link to="/login">返回登入</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const form = reactive({
  token: route.query.token || '',
  email: route.query.email || '',
  password: '',
  password_confirmation: '',
})

async function submit() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.post('/reset-password', form)
    successMessage.value = res.data.message || '密碼已重設'

    setTimeout(() => {
      router.push('/login')
    }, 1200)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '重設失敗'
  } finally {
    loading.value = false
  }
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
h1 { margin: 0 0 8px; }
.subtitle { margin: 0 0 24px; color: #666; }
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
</style>