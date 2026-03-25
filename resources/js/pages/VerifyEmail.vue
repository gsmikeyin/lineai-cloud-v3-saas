<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Email 驗證</h1>
      <p class="subtitle">
        請先到信箱收取驗證信。若未收到，可重新寄送。
      </p>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <button @click="resend" :disabled="loading">
        {{ loading ? '寄送中...' : '重新寄送驗證信' }}
      </button>

      <div class="bottom-link">
        <router-link to="/">返回系統</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../api'

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

async function resend() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.post('/email/verification-notification')
    successMessage.value = res.data.message || '驗證信已寄出'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '寄送失敗'
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
.subtitle { margin: 0 0 24px; color: #666; line-height: 1.7; }
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