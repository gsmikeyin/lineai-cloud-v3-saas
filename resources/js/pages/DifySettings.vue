<template>
  <div class="page-grid">
    <div class="card">
      <div class="page-head">
        <div>
          <h2>Dify 設定</h2>
          <p>設定 Dify Base URL、App API Key、Dataset API Key 與 Dataset ID。</p>
        </div>
      </div>

      <form @submit.prevent="saveSettings">
        <div class="form-grid">
          <div class="form-group full">
            <label>Dify Base URL</label>
            <input
              v-model="form.dify_base_url"
              type="text"
              placeholder="例如：https://api.dify.ai/v1"
            />
          </div>

          <div class="form-group full">
            <label>App API Key</label>
            <input
              v-model="form.dify_app_api_key"
              :type="showAppKey ? 'text' : 'password'"
              placeholder="請輸入 Dify App API Key"
            />
          </div>

          <div class="form-group full">
            <label>Dataset API Key</label>
            <input
              v-model="form.dify_dataset_api_key"
              :type="showDatasetKey ? 'text' : 'password'"
              placeholder="請輸入 Dify Dataset API Key"
            />
          </div>

          <div class="form-group">
            <label>Dataset ID</label>
            <input
              v-model="form.dify_dataset_id"
              type="text"
              placeholder="請輸入 Dataset ID"
            />
          </div>

          <div class="form-group switch-group">
            <label>啟用 Dify</label>
            <input v-model="form.is_active" type="checkbox" />
          </div>
        </div>

        <div class="inline-actions">
          <button type="button" class="ghost-btn" @click="showAppKey = !showAppKey">
            {{ showAppKey ? '隱藏 App Key' : '顯示 App Key' }}
          </button>

          <button type="button" class="ghost-btn" @click="showDatasetKey = !showDatasetKey">
            {{ showDatasetKey ? '隱藏 Dataset Key' : '顯示 Dataset Key' }}
          </button>
        </div>

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="form-actions">
          <button class="primary-btn" type="submit" :disabled="saving">
            {{ saving ? '儲存中...' : '儲存設定' }}
          </button>
        </div>
      </form>
    </div>

    <div class="card">
      <h3>說明</h3>
      <div class="info-list">
        <div class="info-item">
          <strong>App API Key</strong>
          <p>提供對話呼叫 Dify App API 使用。</p>
        </div>
        <div class="info-item">
          <strong>Dataset API Key</strong>
          <p>提供上傳檔案到 Dify Knowledge Base 使用。</p>
        </div>
        <div class="info-item">
          <strong>Dataset ID</strong>
          <p>指定目前 Tenant 上傳到哪個 Dify Dataset。</p>
        </div>
        <div class="info-item">
          <strong>建議</strong>
          <p>每個 Tenant 最好使用自己的 Dataset，以避免知識混用。</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../api'

const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const showAppKey = ref(false)
const showDatasetKey = ref(false)

const form = reactive({
  dify_base_url: 'https://api.dify.ai/v1',
  dify_app_api_key: '',
  dify_dataset_api_key: '',
  dify_dataset_id: '',
  is_active: false,
})

async function fetchSettings() {
  errorMessage.value = ''
  try {
    const res = await api.get('/settings/dify')
    const data = res.data.data || {}

    form.dify_base_url = data.dify_base_url || 'https://api.dify.ai/v1'
    form.dify_app_api_key = data.dify_app_api_key || ''
    form.dify_dataset_api_key = data.dify_dataset_api_key || ''
    form.dify_dataset_id = data.dify_dataset_id || ''
    form.is_active = !!data.is_active
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取設定失敗'
  }
}

async function saveSettings() {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.put('/settings/dify', {
      dify_base_url: form.dify_base_url,
      dify_app_api_key: form.dify_app_api_key,
      dify_dataset_api_key: form.dify_dataset_api_key,
      dify_dataset_id: form.dify_dataset_id,
      is_active: form.is_active,
    })

    successMessage.value = res.data?.success ? 'Dify 設定已儲存' : '已完成'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '儲存失敗'
  } finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>

<style scoped>
.page-grid {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 20px;
}
.card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}
.page-head {
  margin-bottom: 18px;
}
.page-head h2 {
  margin: 0 0 6px;
}
.page-head p {
  margin: 0;
  color: #6b7280;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}
.form-group {
  display: flex;
  flex-direction: column;
}
.form-group.full {
  grid-column: span 2;
}
.form-group label {
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
}
.form-group input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
}
.switch-group {
  justify-content: flex-end;
}
.switch-group input[type='checkbox'] {
  width: 18px;
  height: 18px;
  margin-top: 8px;
}
.inline-actions {
  display: flex;
  gap: 10px;
  margin-top: 16px;
  flex-wrap: wrap;
}
.form-actions {
  margin-top: 18px;
}
.primary-btn,
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
}
.primary-btn {
  background: #111827;
  color: #fff;
}
.ghost-btn {
  background: #eef2f7;
  color: #111827;
}
.success-message {
  margin-top: 16px;
  color: #15803d;
}
.error-message {
  margin-top: 16px;
  color: #dc2626;
}
.info-list {
  display: grid;
  gap: 14px;
}
.info-item {
  padding: 14px;
  background: #f8fafc;
  border-radius: 14px;
}
.info-item p {
  margin: 8px 0 0;
  color: #4b5563;
  line-height: 1.8;
}
@media (max-width: 900px) {
  .page-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }
  .form-group.full {
    grid-column: span 1;
  }
}
</style>