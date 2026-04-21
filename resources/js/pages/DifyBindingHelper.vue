<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>📦 Dify 綁定助手</h2>
        <p>顯示尚未完成 dataset 綁定的 tenant，並提供快速操作指引。</p>
      </div>
      <button class="ghost-btn" @click="load">刷新</button>
    </div>

    <div v-if="successMessage" class="success-message">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Dataset ID</th>
            <th>狀態</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in list" :key="item.id">
            <td>{{ item.tenant?.name || ('Tenant #' + item.tenant_id) }}</td>
            <td class="mono">{{ item.dify_dataset_id || '-' }}</td>
            <td>
              <span class="badge status-warning">待綁定</span>
            </td>
            <td class="actions">
              <button class="ghost-btn sm" @click="openDify(item)">前往 Dify</button>
              <button class="primary-btn sm" @click="confirmBind(item)">已完成</button>
            </td>
          </tr>

          <tr v-if="!loading && list.length === 0">
            <td colspan="4">
              <div class="empty-box">目前沒有待綁定的 tenant</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="selectedInfo" class="guide-card">
      <h3>操作指引</h3>
      <ol>
        <li>打開 Dify App</li>
        <li>找到 Knowledge Retrieval 節點</li>
        <li>選擇 dataset：<strong>{{ selectedInfo.dataset_id }}</strong></li>
        <li>按 Publish</li>
        <li>回來此頁點「已完成」</li>
      </ol>
      <div class="guide-meta">
        <div><strong>Tenant：</strong>{{ selectedInfo.tenant_name }}</div>
        <div><strong>Dataset：</strong>{{ selectedInfo.dataset_id }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const list = ref([])
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const selectedInfo = ref(null)

async function load() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.get('/dify-binding/pending')
    list.value = res.data.data || res.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取待綁定清單失敗'
  } finally {
    loading.value = false
  }
}

async function openDify(item) {
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.get(`/dify-binding/link/${item.tenant_id}`)
    selectedInfo.value = res.data

    window.open(res.data.app_url, '_blank')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '取得 Dify 連結失敗'
  }
}

async function confirmBind(item) {
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/dify-binding/confirm', {
      tenant_id: item.tenant_id,
    })

    successMessage.value = '已標記為完成綁定'
    await load()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '標記綁定失敗'
  }
}

onMounted(load)
</script>

<style scoped>
.page-card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}
.page-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}
.page-head h2 {
  margin: 0 0 6px;
}
.page-head p {
  margin: 0;
  color: #6b7280;
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
.sm {
  padding: 8px 10px;
  font-size: 12px;
}
.success-message {
  margin-bottom: 12px;
  color: #15803d;
}
.error-message {
  margin-bottom: 12px;
  color: #dc2626;
}
.table-wrap {
  overflow-x: auto;
}
.table {
  width: 100%;
  border-collapse: collapse;
}
.table th,
.table td {
  padding: 14px 12px;
  border-bottom: 1px solid #eef2f7;
  text-align: left;
  vertical-align: top;
}
.table th {
  font-size: 13px;
  color: #6b7280;
}
.actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.badge {
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
}
.status-warning {
  background: #fef3c7;
  color: #92400e;
}
.empty-box {
  color: #6b7280;
  text-align: center;
  padding: 24px;
}
.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: 12px;
}
.guide-card {
  margin-top: 20px;
  background: #f8fafc;
  border-radius: 16px;
  padding: 18px;
}
.guide-card h3 {
  margin-top: 0;
}
.guide-card ol {
  margin: 0 0 14px 18px;
}
.guide-meta {
  color: #374151;
  line-height: 1.8;
}
</style>