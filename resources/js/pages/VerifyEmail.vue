<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>Email 驗證</h1>
      <p class="subtitle">
        系統正在驗證你的 Email。
      </p>

      <div v-if="loading" class="info-message">
        驗證中...
      </div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <div class="action-group">
        <button v-if="!loading" @click="resend" :disabled="resending">
          {{ resending ? '寄送中...' : '重新寄送驗證信' }}
        </button>

        <router-link class="link-btn" to="/login">
          返回登入
        </router-link>

        <router-link class="link-btn secondary" to="/">
          返回系統
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const resending = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

async function verifyNow() {
  successMessage.value = ''
  errorMessage.value = ''

  const verifyUrl = route.query.verify_url

  if (!verifyUrl) {
    errorMessage.value = '缺少驗證連結'
    loading.value = false
    return
  }

  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  try {
    const url = new URL(verifyUrl)
    const apiPath = url.pathname + url.search

    await api.get(apiPath)

    successMessage.value = 'Email 驗證成功'
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Email 驗證失敗'
  } finally {
    loading.value = false
  }
}

async function resend() {
  resending.value = true
  successMessage.value = ''
  errorMessage.value = ''

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

onMounted(verifyNow)
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
  max-width: 460px;
  background: #fff;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}
h1 {
  margin: 0 0 8px;
}
.subtitle {
  margin: 0 0 24px;
  color: #666;
  line-height: 1.7;
}
.info-message {
  margin-bottom: 16px;
  color: #2563eb;
}
.success-message {
  margin-bottom: 16px;
  color: #15803d;
}
.error-message {
  margin-bottom: 16px;
  color: #dc2626;
}
.action-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
button,
.link-btn {
  width: 100%;
  box-sizing: border-box;
  border: 0;
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 15px;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  background: #111827;
  color: #fff;
}
.link-btn.secondary {
  background: #eef2f7;
  color: #111827;
}
</style>