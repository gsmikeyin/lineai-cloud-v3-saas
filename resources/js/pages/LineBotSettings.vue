<template>
  <div class="page">
    <EmailVerificationBanner :email-verified="emailVerified" />

    <div class="page-header">
      <div>
        <h1>{{ $t('adminPages.lineBot.title') }}</h1>
        <p>{{ $t('adminPages.lineBot.desc') }}</p>
      </div>
    </div>

    <div v-if="!emailVerified" class="lock-box">
      {{ $t('adminPages.lineBot.lock') }}
    </div>

    <div class="grid" :class="{ disabled: !emailVerified }">
      <div class="card">
        <h2>{{ $t('adminPages.lineBot.basicSettings') }}</h2>

        <div v-if="loading" class="loading-box">{{ $t('adminPages.lineBot.loading') }}</div>

        <form v-else @submit.prevent="saveSettings">
          <div class="form-group">
            <label>Channel Name</label>
            <input v-model="form.channel_name" type="text" :placeholder="$t('adminPages.lineBot.channelNamePlaceholder')" :disabled="!emailVerified" />
          </div>

          <div class="form-group">
            <label>Channel ID</label>
            <input v-model="form.channel_id" type="text" :placeholder="$t('adminPages.lineBot.channelIdPlaceholder')" :disabled="!emailVerified" />
          </div>

          <div class="form-group">
            <label>Channel Secret</label>
            <input v-model="form.channel_secret" :type="showSecret ? 'text' : 'password'" :placeholder="$t('adminPages.lineBot.channelSecretPlaceholder')" :disabled="!emailVerified" />
          </div>

          <div class="form-actions-inline">
            <button type="button" class="ghost-btn" @click="showSecret = !showSecret" :disabled="!emailVerified">
              {{ showSecret ? $t('adminPages.lineBot.hideSecret') : $t('adminPages.lineBot.showSecret') }}
            </button>
          </div>

          <div class="form-group">
            <label>Channel Access Token</label>
            <textarea v-model="form.channel_access_token" rows="4" :placeholder="$t('adminPages.lineBot.accessTokenPlaceholder')" :disabled="!emailVerified" />
          </div>

          <div class="form-group switch-row">
            <label>{{ $t('adminPages.lineBot.activeBot') }}</label>
            <input v-model="form.is_active" type="checkbox" :disabled="!emailVerified" />
          </div>

          <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
          <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

          <div class="form-actions">
            <button type="submit" class="primary-btn" :disabled="saving || !emailVerified">
              {{ saving ? $t('adminPages.lineBot.saving') : $t('adminPages.lineBot.save') }}
            </button>
          </div>
        </form>
      </div>

      <div class="card">
        <h2>{{ $t('adminPages.lineBot.webhookInfo') }}</h2>

        <div class="info-block">
          <label>Webhook URL</label>
          <div class="copy-row">
            <input :value="webhookUrl" type="text" readonly />
            <button class="ghost-btn" @click="copyWebhookUrl">{{ $t('adminPages.lineBot.copy') }}</button>
          </div>
          <p class="hint">{{ $t('adminPages.lineBot.webhookHint') }}</p>
        </div>

        <div class="info-block">
          <label>{{ $t('adminPages.lineBot.setupSteps') }}</label>
          <ol class="steps">
            <li v-for="step in $tm('adminPages.lineBot.steps')" :key="step">{{ step }}</li>
          </ol>
        </div>

        <div class="info-block">
          <label>{{ $t('adminPages.lineBot.currentStatus') }}</label>
          <div class="status-list">
            <div class="status-item">
              <span>{{ $t('adminPages.lineBot.activeBot') }}</span>
              <strong>{{ form.is_active ? $t('adminPages.lineBot.enabled') : $t('adminPages.lineBot.disabled') }}</strong>
            </div>
            <div class="status-item">
              <span>Webhook URL</span>
              <strong>{{ webhookUrl ? $t('adminPages.lineBot.generated') : $t('adminPages.lineBot.notGenerated') }}</strong>
            </div>
            <div class="status-item">
              <span>Channel Secret</span>
              <strong>{{ form.channel_secret ? $t('adminPages.lineBot.configured') : $t('adminPages.lineBot.notConfigured') }}</strong>
            </div>
            <div class="status-item">
              <span>Access Token</span>
              <strong>{{ form.channel_access_token ? $t('adminPages.lineBot.configured') : $t('adminPages.lineBot.notConfigured') }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'
import EmailVerificationBanner from '../components/EmailVerificationBanner.vue'
import { useAuthState } from '../composables/useAuthState'

const { t } = useI18n()
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
    form.is_active = typeof data.is_active === 'boolean' ? data.is_active : true
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.lineBot.loadFailed')
  } finally {
    loading.value = false
  }
}

async function saveSettings() {
  if (!emailVerified.value) {
    errorMessage.value = t('adminPages.lineBot.verifyEmailFirst')
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
      is_active: form.is_active,
    })

    webhookUrl.value = res.data.webhook_url || webhookUrl.value
    successMessage.value = t('adminPages.lineBot.saved')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.lineBot.saveFailed')
  } finally {
    saving.value = false
  }
}

async function copyWebhookUrl() {
  if (!webhookUrl.value) return

  try {
    await navigator.clipboard.writeText(webhookUrl.value)
    successMessage.value = t('adminPages.lineBot.copied')
    errorMessage.value = ''
  } catch (error) {
    errorMessage.value = t('adminPages.lineBot.copyFailed')
  }
}

onMounted(fetchSettings)
</script>

<style scoped>
.page { display:grid; gap:8px; padding:16px 32px 32px; background:#f4f7fb; min-height:100vh; }
.page-header { margin-bottom:0; }
.page-header h1 { margin:0 0 4px; font-size:22px; line-height:1.25; }
.page-header p { margin:0; color:#6b7280; font-size:13px; line-height:1.5; }
.lock-box { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:8px; padding:10px 14px; }
.grid { display:grid; grid-template-columns:1.1fr .9fr; gap:20px; }
.grid.disabled { opacity:.85; }
.card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.card h2 { margin-top:0; margin-bottom:20px; }
.loading-box { color:#6b7280; }
.form-group { margin-bottom:16px; }
.form-group label,.info-block label { display:block; margin-bottom:8px; font-size:14px; color:#374151; font-weight:600; }
.form-group input,.form-group textarea { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:8px; padding:12px 14px; font-size:14px; background:#fff; }
.form-actions-inline { margin-top:-4px; margin-bottom:16px; }
.switch-row { display:flex; align-items:center; justify-content:space-between; }
.switch-row input[type="checkbox"] { width:18px; height:18px; }
.form-actions { margin-top:20px; }
.primary-btn,.ghost-btn { border:0; border-radius:8px; padding:10px 14px; cursor:pointer; font-size:14px; }
.primary-btn { background:#111827; color:#fff; }
.ghost-btn { background:#eef2f7; color:#111827; }
.primary-btn:disabled,.ghost-btn:disabled { opacity:.6; cursor:not-allowed; }
.success-message { margin-top:8px; color:#15803d; font-size:14px; }
.error-message { margin-top:8px; color:#dc2626; font-size:14px; }
.info-block { margin-bottom:22px; }
.copy-row { display:flex; gap:10px; }
.copy-row input { flex:1; box-sizing:border-box; border:1px solid #d7dce5; border-radius:8px; padding:12px 14px; font-size:14px; background:#f9fafb; }
.hint { margin-top:8px; color:#6b7280; font-size:13px; }
.steps { margin:0; padding-left:18px; color:#374151; line-height:1.8; }
.status-list { display:grid; gap:10px; }
.status-item { display:flex; justify-content:space-between; gap:16px; padding:10px 12px; border-radius:8px; background:#f9fafb; }
@media (max-width:980px) { .grid { grid-template-columns:1fr; } }
</style>
