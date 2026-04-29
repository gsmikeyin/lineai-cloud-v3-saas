<template>
  <div v-if="show" class="verify-banner">
    <div class="verify-content">
      <div class="verify-text">
        <div class="title">{{ text.title }}</div>
        <div class="desc">{{ text.desc }}</div>
      </div>

      <div class="verify-actions">
        <button class="ghost-btn" type="button" @click="resend" :disabled="loading">
          {{ loading ? text.sending : text.resend }}
        </button>
        <router-link class="primary-link" to="/app/verify-email">
          {{ text.open }}
        </router-link>
      </div>
    </div>

    <div v-if="successMessage" class="success-message">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'

const props = defineProps({
  emailVerified: {
    type: Boolean,
    default: true,
  },
})

const { locale } = useI18n()
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const show = computed(() => !props.emailVerified)
const text = computed(() => {
  if (locale.value === 'en') {
    return {
      title: 'Email is not verified',
      desc: 'Please verify your email to enable protected settings and account features.',
      resend: 'Resend verification email',
      sending: 'Sending...',
      open: 'Open verification page',
      sent: 'Verification email sent.',
      failed: 'Failed to send verification email.',
    }
  }

  return {
    title: 'Email 尚未驗證',
    desc: '請完成 Email 驗證，才能使用受保護的設定與帳號功能。',
    resend: '重新寄送驗證信',
    sending: '寄送中...',
    open: '前往驗證頁',
    sent: '驗證信已寄出。',
    failed: '驗證信寄送失敗。',
  }
})

async function resend() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/email/verification-notification')
    successMessage.value = text.value.sent
  } catch (error) {
    errorMessage.value = text.value.failed
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.verify-banner {
  background: #fff7ed;
  border: 1px solid #fed7aa;
  border-radius: 8px;
  padding: 16px 18px;
  margin-bottom: 18px;
}

.verify-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.title {
  font-weight: 700;
  color: #9a3412;
  margin-bottom: 4px;
}

.desc {
  color: #9a3412;
  font-size: 14px;
}

.verify-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.ghost-btn,
.primary-link {
  border: 0;
  border-radius: 8px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
  white-space: nowrap;
}

.ghost-btn {
  background: #ffedd5;
  color: #9a3412;
}

.ghost-btn:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.primary-link {
  background: #111827;
  color: #fff;
}

.success-message {
  margin-top: 10px;
  color: #15803d;
  font-size: 14px;
}

.error-message {
  margin-top: 10px;
  color: #dc2626;
  font-size: 14px;
}

@media (max-width: 768px) {
  .verify-content {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
