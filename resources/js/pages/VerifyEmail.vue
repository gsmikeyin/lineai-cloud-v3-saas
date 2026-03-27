<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Email 驗證</h1>
      <p class="subtitle">
        正在處理驗證，若尚未登入請先登入後再回來。
      </p>

      <div v-if="loading">驗證中...</div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <div class="action-row">
        <router-link to="/login" class="ghost-link">前往登入</router-link>
        <button @click="resend" :disabled="resending">
          {{ resending ? '寄送中...' : '重新寄送驗證信' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'

const route = useRoute()

const loading = ref(false)
const resending = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

async function verifyEmail() {
  const token = localStorage.getItem('token')

  if (!token) {
    errorMessage.value = '請先登入，再完成 Email 驗證。'
    return
  }

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.post('/verify-email', {
      id: route.query.id,
      hash: route.query.hash,
      expires: route.query.expires,
      signature: route.query.signature,
    })

    successMessage.value = res.data.message || 'Email 驗證成功'
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '驗證失敗，請稍後再試'
  } finally {
    loading.value = false
  }
}

async function resend() {
  resending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.post('/email/verification-notification')
    successMessage.value = res.data.message || '驗證信已寄出'
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '寄送失敗'
  } finally {
    resending.value = false
  }
}

onMounted(() => {
  if (route.query.id && route.query.hash && route.query.expires && route.query.signature) {
    verifyEmail()
  }
})
</script>