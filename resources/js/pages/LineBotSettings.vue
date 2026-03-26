<template>
  <div class="page">
    <EmailVerificationBanner :email-verified="emailVerified" />

    <div class="page-header">
      <div>
        <h1>LINE Bot 設定</h1>
        <p>每個 Tenant 對應一個 LINE Bot，可在此修改設定。</p>
      </div>
    </div>

    <div v-if="!emailVerified" class="lock-box">
      為了安全性，請先完成 Email 驗證後再設定 LINE Bot。
    </div>

    <div class="grid" :class="{ disabled: !emailVerified }">
      <div class="card">
        <h2>Bot 基本設定</h2>

        <div v-if="loading" class="loading-box">讀取中...</div>

        <form v-else @submit.prevent="saveSettings">
          <div class="form-group">
            <label>Channel Name</label>
            <input v-model="form.channel_name" type="text" placeholder="例如：客服 Bot" :disabled="!emailVerified" />
          </div>

          <div class="form-group">
            <label>Channel ID</label>
            <input v-model="form.channel_id" type="text" placeholder="請輸入 LINE Channel ID" :disabled="!emailVerified" />
          </div>

          <div class="form-group">
            <label>Channel Secret</label>
            <input
              v-model="form.channel_secret"
              :type="showSecret ? 'text' : 'password'"
              placeholder="請輸入 LINE Channel Secret"
              :disabled="!emailVerified"
            />
          </div>

          <div class="form-actions-inline">
            <button type="button" class="ghost-btn" @click="showSecret = !showSecret" :disabled="!emailVerified">
              {{ showSecret ? '隱藏 Secret' : '顯示 Secret' }}
            </button>
          </div>

          <div class="form-group">
            <label>Channel Access Token</label>
            <textarea
              v-model="form.channel_access_token"
              rows="4"
              placeholder="請輸入 LINE Channel Access Token"
              :disabled="!emailVerified"
            />
          </div>

          <div class="form-group">
            <label>Basic ID</label>
            <input v-model="form.basic_id" type="text" placeholder="@yourbot" :disabled="!emailVerified" />
          </div>

          <div class="form-group">
            <label>Bot User ID</label>
            <input v-model="form.bot_user_id" type="text" placeholder="Uxxxxxxxxxxxxxxxx" :disabled="!emailVerified" />
          </div>

          <div class="form-group switch-row">
            <label>啟用 Bot</label>
            <input v-model="form.is_active" type="checkbox" :disabled="!emailVerified" />
          </div>

          <div v-if="successMessage" class="success-message">
            {{ successMessage }}
          </div>

          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <div class="form-actions">
            <button type="submit" class="primary-btn" :disabled="saving || !emailVerified">
              {{ saving ? '儲存中...' : '儲存設定' }}
            </button>
          </div>
        </form>
      </div>

      <div class="card">
        <h2>Webhook 資訊</h2>

        <div class="info-block">
          <label>Webhook URL</label>
          <div class="copy-row">
            <input :value="webhookUrl" type="text" readonly />
            <button class="ghost-btn" @click="copyWebhookUrl">
              複製
            </button>
          </div>
          <p class="hint">
            請將此網址填到 LINE Developers Console 的 Webhook URL。
          </p>
        </div>

        <div class="info-block">
          <label>設定步驟</label>
          <ol class="steps">
            <li>登入 LINE Developers Console</li>
            <li>進入 Messaging API 設定頁</li>
            <li>貼上上方 Webhook URL</li>
            <li>啟用 Use webhook</li>
            <li>點 Verify 驗證</li>
          </ol>
        </div>

        <div class="info-block">
          <label>目前狀態</label>
          <div class="status-list">
            <div class="status-item">
              <span>Bot 啟用</span>
              <strong>{{ form.is_active ? '是' : '否' }}</strong>
            </div>
            <div class="status-item">
              <span>Webhook URL</span>
              <strong>{{ webhookUrl ? '已建立' : '未建立' }}</strong>
            </div>
            <div class="status-item">
              <span>Channel Secret</span>
              <strong>{{ form.channel_secret ? '已設定' : '未設定' }}</strong>
            </div>
            <div class="status-item">
              <span>Access Token</span>
              <strong>{{ form.channel_access_token ? '已設定' : '未設定' }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../api'
import EmailVerificationBanner from '../components/EmailVerificationBanner.vue'
import { useAuthState } from '../composables/useAuthState'

const { emailVerified } = useAuthState()

const loading = ref(true)
const saving = ref(false)
const showSecret = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const webhookUrl = ref('')

const form = reactive({
  channel_name: '',
  channel_id: '',
  channel_secret: '',
  channel_access_token: '',
  basic_id: '',
  bot_user_id: '',
  is_active: true,
})

async function fetchSettings() {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.get('/settings/line-bot')
    const data = res.data.data || {}
    webhookUrl.value = res.data.webhook_url || ''

    form.channel_name = data.channel_name || ''
    form.channel_id = data.channel_id || ''
    form.channel_secret = data.channel_secret || ''
    form.channel_access_token = data.channel_access_token || ''
    form.basic_id = data.basic_id || ''
    form.bot_user_id = data.bot_user_id || ''
    form.is_active = typeof data.is_active === 'boolean' ? data.is_active : true
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '讀取設定失敗'
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  if (!emailVerified.value) {
    errorMessage.value = '請先完成 Email 驗證'
    return
  }

  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.put('/settings/line-bot', {
      channel_name: form.channel_name,
      channel_id: form.channel_id,
      channel_secret: form.channel_secret,
      channel_access_token: form.channel_access_token,
      basic_id: form.basic_id,
      bot_user_id: form.bot_user_id,
      is_active: form.is_active,
    })

    webhookUrl.value = res.data.webhook_url || webhookUrl.value
    successMessage.value = 'LINE Bot 設定已儲存'
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '儲存失敗，請稍後再試'
  } finally {
    saving.value = false
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

onMounted(fetchSettings)
</script>

<style scoped>
.page {
  display: grid;
  gap: 18px;
  padding: 32px;
  background: #f4f7fb;
  min-height: 100vh;
}
.page-header {
  margin-bottom: 6px;
}
.page-header h1 {
  margin: 0 0 8px;
}
.page-header p {
  margin: 0;
  color: #6b7280;
}
.lock-box {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
  border-radius: 14px;
  padding: 14px 16px;
}
.grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 20px;
}
.grid.disabled {
  opacity: 0.85;
}
.card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}
.card h2 {
  margin-top: 0;
  margin-bottom: 20px;
}
.loading-box { color: #6b7280; }
.form-group { margin-bottom: 16px; }
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  color: #374151;
  font-weight: 600;
}
.form-group input,
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
  background: #fff;
}
.form-actions-inline {
  margin-top: -4px;
  margin-bottom: 16px;
}
.switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.switch-row input[type="checkbox"] {
  width: 18px;
  height: 18px;
}
.form-actions {
  margin-top: 20px;
}
.primary-btn,
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
}
.primary-btn {
  background: #111827;
  color: #fff;
}
.ghost-btn {
  background: #eef2f7;
  color: #111827;
}
.primary-btn:disabled,
.ghost-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.success-message {
  margin-top: 8px;
  color: #15803d;
  font-size: 14px;
}
.error-message {
  margin-top: 8px;
  color: #dc2626;
  font-size: 14px;
}
.info-block { margin-bottom: 22px; }
.info-block label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  color: #374151;
  font-weight: 600;
}
.copy-row {
  display: flex;
  gap: 10px;
}
.copy-row input {
  flex: 1;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
  background: #f9fafb;
}
.hint {
  margin-top: 8px;
  color: #6b7280;
  font-size: 13px;
}
.steps {
  margin: 0;
  padding-left: 18px;
  color: #374151;
  line-height: 1.8;
}
.status-list {
  display: grid;
  gap: 10px;
}
.status-item {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 10px 12px;
  border-radius: 10px;
  background: #f9fafb;
}
@media (max-width: 980px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>