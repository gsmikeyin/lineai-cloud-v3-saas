<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1>{{ text.title }}</h1>
      <p class="subtitle">{{ text.subtitle }}</p>

      <div v-if="loading" class="status-message">{{ text.verifying }}</div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <div class="action-row">
        <router-link to="/login" class="ghost-link">{{ text.goLogin }}</router-link>
        <button type="button" @click="resend" :disabled="resending || !hasToken">
          {{ resending ? text.resending : text.resend }}
        </button>
      </div>

      <p v-if="!hasToken" class="hint">{{ text.resendHint }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import api from '../api'

const route = useRoute()
const { locale } = useI18n()

const loading = ref(false)
const resending = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const hasToken = computed(() => Boolean(localStorage.getItem('token')))
const text = computed(() => {
  if (locale.value === 'en') {
    return {
      title: 'Email Verification',
      subtitle: 'Click the verification link in your email. If you opened that link, the system will verify it automatically.',
      verifying: 'Verifying...',
      goLogin: 'Go to login',
      resend: 'Resend verification email',
      resending: 'Sending...',
      resendHint: 'Sign in first if you need to resend the verification email.',
      verified: 'Email verified successfully.',
      sent: 'Verification email sent.',
      failed: 'Verification failed. Please request a new verification email.',
      sendFailed: 'Failed to send verification email.',
      missingLink: 'This verification link is missing required data. Please request a new verification email.',
    }
  }

  return {
    title: 'Email 驗證',
    subtitle: '請點擊信箱中的驗證連結。若你是從驗證信開啟此頁，系統會自動完成驗證。',
    verifying: '驗證中...',
    goLogin: '前往登入',
    resend: '重新寄送驗證信',
    resending: '寄送中...',
    resendHint: '若需要重新寄送驗證信，請先登入帳號。',
    verified: 'Email 驗證成功。',
    sent: '驗證信已寄出。',
    failed: '驗證失敗，請重新寄送驗證信。',
    sendFailed: '驗證信寄送失敗。',
    missingLink: '此驗證連結缺少必要資料，請重新寄送驗證信。',
  }
})

async function verifyEmail() {
  if (route.query.verified === '1') {
    successMessage.value = text.value.verified
    return
  }

  if (route.query.verified === 'failed') {
    errorMessage.value = text.value.failed
    return
  }

  if (!route.query.id || !route.query.hash || !route.query.expires || !route.query.signature) {
    errorMessage.value = text.value.missingLink
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

    let currentUser = {}
    try {
      currentUser = JSON.parse(localStorage.getItem('user') || '{}')
    } catch (e) {
      currentUser = {}
    }

    if (hasToken.value && res.data.user && String(currentUser.id) === String(res.data.user.id)) {
      localStorage.setItem('user', JSON.stringify(res.data.user))
    }

    successMessage.value = text.value.verified
  } catch (error) {
    errorMessage.value = text.value.failed
  } finally {
    loading.value = false
  }
}

async function resend() {
  if (!hasToken.value) {
    errorMessage.value = text.value.resendHint
    return
  }

  resending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.post('/email/verification-notification')
    successMessage.value = text.value.sent
  } catch (error) {
    errorMessage.value = text.value.sendFailed
  } finally {
    resending.value = false
  }
}

onMounted(verifyEmail)
</script>

<style scoped>
.auth-page { min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f4f7fb; padding:24px; }
.auth-card { width:100%; max-width:480px; background:#fff; border-radius:8px; padding:32px; box-shadow:0 10px 30px rgba(0,0,0,.08); }
h1 { margin:0 0 10px; font-size:28px; }
.subtitle,.hint,.status-message { color:#6b7280; line-height:1.6; }
.success-message { margin-top:16px; color:#15803d; }
.error-message { margin-top:16px; color:#dc2626; }
.action-row { display:flex; gap:12px; align-items:center; margin-top:22px; flex-wrap:wrap; }
.ghost-link,button { border:0; border-radius:8px; padding:10px 14px; font-size:14px; text-decoration:none; }
.ghost-link { background:#eef2f7; color:#111827; }
button { background:#111827; color:#fff; cursor:pointer; }
button:disabled { opacity:.6; cursor:not-allowed; }
.hint { margin:12px 0 0; font-size:13px; }
</style>
