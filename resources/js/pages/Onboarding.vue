<template>
  <div class="onboarding-page">
    <div class="onboarding-card">
      <EmailVerificationBanner :email-verified="emailVerified" />

      <div class="hero">
        <h1>歡迎使用 ServiceAI Cloud</h1>
        <p>再幾步就能完成你的 LINE AI SaaS 初始設定。</p>
      </div>

      <div class="steps">
        <div class="step" :class="{ done: !!webhookUrl }">
          <div class="step-index">1</div>
          <div class="step-content">
            <h3>確認你的專屬 Webhook URL</h3>
            <p>請將下方網址貼到 LINE Developers Console 的 Webhook URL。</p>

            <div class="copy-row">
              <input :value="webhookUrl" readonly />
              <button class="ghost-btn" @click="copyWebhookUrl">複製</button>
            </div>
          </div>
        </div>

        <div class="step">
          <div class="step-index">2</div>
          <div class="step-content">
            <h3>填寫 LINE Bot 設定</h3>
            <p>請先到 LINE Bot 設定頁，填入 Channel Secret 與 Access Token。</p>

            <router-link class="primary-link" to="/settings/line-bot">
              前往 LINE Bot 設定
            </router-link>
          </div>
        </div>

        <div class="step">
          <div class="step-index">3</div>
          <div class="step-content">
            <h3>建立 Knowledge Base</h3>
            <p>加入營業時間、出貨時間、退換貨政策，讓 AI 能夠更準確回答。</p>

            <router-link class="primary-link secondary" to="/knowledge-base">
              前往 Knowledge Base
            </router-link>
          </div>
        </div>

        <div class="step">
          <div class="step-index">4</div>
          <div class="step-content">
            <h3>開始使用</h3>
            <p>完成後即可進入 Dashboard 與 Conversations 後台。</p>

            <div class="action-row">
              <router-link class="primary-link dark" to="/">
                進入 Dashboard
              </router-link>
              <router-link class="ghost-link" to="/conversations">
                進入 Conversations
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
    errorMessage.value = error.response?.data?.message || '讀取設定失敗'
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