<template>
  <div class="onboarding-page">
    <div class="onboarding-card">
      <EmailVerificationBanner :email-verified="emailVerified" />

      <div class="hero">
        <h1>開始使用 ServiceAI Cloud</h1>
        <p>依照步驟完成 ServiceAI SaaS 的基本設定。</p>
      </div>

      <div class="steps">
        <div class="step" :class="{ done: !!webhookUrl }">
          <div class="step-index">1</div>
          <div class="step-content">
            <h3>設定 Webhook URL</h3>
            <p>請將下方 Webhook URL 複製到 LINE Developers Console。</p>

            <div class="copy-row">
              <input :value="webhookUrl" readonly />
              <button class="ghost-btn" @click="copyWebhookUrl">複製</button>
            </div>
          </div>
        </div>

        <div class="step">
          <div class="step-index">2</div>
          <div class="step-content">
            <h3>設定 LINE Bot</h3>
            <p>填入 Channel Secret 與 Channel Access Token。</p>

            <router-link class="primary-link" to="/app/settings/line-bot">
              前往 LINE Bot 設定
            </router-link>
          </div>
        </div>

        <div class="step">
          <div class="step-index">3</div>
          <div class="step-content">
            <h3>建立知識庫</h3>
            <p>上傳文件，讓 AI 可以依照你的資料回覆客戶。</p>

            <router-link class="primary-link secondary" to="/app/knowledge-base">
              前往知識庫
            </router-link>
          </div>
        </div>

        <div class="step">
          <div class="step-index">4</div>
          <div class="step-content">
            <h3>開始使用</h3>
            <p>回到首頁或直接進入對話工作區。</p>

            <div class="action-row">
              <router-link class="primary-link dark" to="/app">
                進入首頁
              </router-link>
              <router-link class="ghost-link" to="/app/conversations">
                進入對話
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../api'
import EmailVerificationBanner from '../components/EmailVerificationBanner.vue'
import { useAuthState } from '../composables/useAuthState'

const { emailVerified } = useAuthState()

const webhookUrl = ref('')
const successMessage = ref('')
const errorMessage = ref('')

async function fetchBotSettings() {
  errorMessage.value = ''

  try {
    const res = await api.get('/settings/line-bot')
    webhookUrl.value = res.data.webhook_url || ''
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取 LINE Bot 設定失敗'
  }
}

async function copyWebhookUrl() {
  if (!webhookUrl.value) return

  try {
    await navigator.clipboard.writeText(webhookUrl.value)
    successMessage.value = 'Webhook URL 已複製'
    errorMessage.value = ''
  } catch (error) {
    errorMessage.value = '複製失敗，請手動複製'
  }
}

onMounted(fetchBotSettings)
</script>

<style scoped>
.onboarding-page {
  padding: 32px;
}

.onboarding-card {
  max-width: 980px;
  margin: 0 auto;
  background: #fff;
  border-radius: 8px;
  padding: 28px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.hero h1 {
  margin: 0 0 8px;
  font-size: 28px;
}

.hero p,
.step-content p {
  color: #4b5563;
  line-height: 1.6;
}

.steps {
  display: grid;
  gap: 16px;
  margin-top: 24px;
}

.step {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 16px;
  padding: 18px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.step.done {
  border-color: #86efac;
  background: #f0fdf4;
}

.step-index {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: #111827;
  color: #fff;
  font-weight: 700;
}

.step-content h3 {
  margin: 0 0 6px;
}

.copy-row,
.action-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.copy-row input {
  flex: 1;
  min-width: 240px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 10px 12px;
}

.primary-link,
.ghost-link,
.ghost-btn {
  display: inline-flex;
  align-items: center;
  border: 0;
  border-radius: 8px;
  padding: 10px 14px;
  text-decoration: none;
  cursor: pointer;
  font-size: 14px;
}

.primary-link,
.ghost-btn {
  background: #2563eb;
  color: #fff;
}

.primary-link.secondary {
  background: #0f766e;
}

.primary-link.dark {
  background: #111827;
}

.ghost-link {
  background: #eef2f7;
  color: #111827;
}

.success-message,
.error-message {
  margin-top: 16px;
  font-size: 14px;
}

.success-message {
  color: #15803d;
}

.error-message {
  color: #dc2626;
}
</style>
