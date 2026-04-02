<template>
  <div v-if="show" class="verify-banner">
    <div class="verify-content">
      <div class="verify-text">
        <div class="title">Email 尚未驗證</div>
        <div class="desc">
          請先完成 Email 驗證，以啟用完整 SaaS 功能。
        </div>
      </div>

      <div class="verify-actions">
        <button class="ghost-btn" @click="resend" :disabled="loading">
          {{ loading ? '寄送中...' : '重新寄送驗證信' }}
        </button>
        <router-link class="primary-link" to="/app/verify-email">
          前往驗證頁
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
import api from '../api'

const props = defineProps({
  emailVerified: {
    type: Boolean,
    default: true,
  },
})

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const show = computed(() => !props.emailVerified)

async function resend() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.post('/email/verification-notification')
    successMessage.value = res.data.message || '驗證信已寄出'
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '寄送失敗'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.verify-banner {
  background: #fff7ed;
  border: 1px solid #fed7aa;
  border-radius: 16px;
  padding: 16px 18px;
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
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
  text-decoration: none;
}

.ghost-btn {
  background: #ffedd5;
  color: #9a3412;
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