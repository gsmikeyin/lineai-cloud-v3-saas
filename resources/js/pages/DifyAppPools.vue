<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>Dify App Pool</h2>
        <p>管理可分配給 tenant 的 Dify App API Keys。</p>
      </div>
      <button class="ghost-btn" @click="fetchPools">刷新</button>
    </div>

    <div class="create-card">
      <h3>新增 App Pool</h3>
      <form @submit.prevent="createPool">
        <div class="form-grid">
          <div class="form-group">
            <label>App Name</label>
            <input v-model="form.app_name" type="text" placeholder="例如：Support App 001" />
          </div>

          <div class="form-group">
            <label>App Mode</label>
            <select v-model="form.app_mode">
              <option value="chat">chat</option>
              <option value="workflow">workflow</option>
            </select>
          </div>

          <div class="form-group full">
            <label>App API Key</label>
            <input v-model="form.app_api_key" type="password" placeholder="請輸入 Dify App API Key" />
          </div>
        </div>

        <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

        <div class="actions">
          <button class="primary-btn" type="submit" :disabled="creating">
            {{ creating ? '建立中...' : '建立 App Pool' }}
          </button>
        </div>
      </form>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>App Name</th>
            <th>Mode</th>
            <th>Status</th>
            <th>Assigned Tenant</th>
            <th>建立時間</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in pools" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.app_name }}</td>
            <td>{{ item.app_mode }}</td>
            <td>
              <select v-model="item.status" @change="updateStatus(item)">
                <option value="available">available</option>
                <option value="assigned">assigned</option>
                <option value="disabled">disabled</option>
              </select>
            </td>
            <td>{{ item.assigned_tenant_id || '-' }}</td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td>
              <button class="ghost-btn sm" @click="copyKey(item.app_api_key)">複製 Key</button>
            </td>
          </tr>

          <tr v-if="!loading && pools.length === 0">
            <td colspan="7"><div class="empty-box">目前沒有任何 App Pool</div></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../api'

const pools = ref([])
const loading = ref(false)
const creating = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const form = reactive({
  app_name: '',
  app_mode: 'chat',
  app_api_key: '',
})

async function fetchPools() {
  loading.value = true
  try {
    const res = await api.get('/dify-app-pools')
    pools.value = res.data.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取 App Pool 失敗'
  } finally {
    loading.value = false
  }
}

async function createPool() {
  creating.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/dify-app-pools', {
      app_name: form.app_name,
      app_mode: form.app_mode,
      app_api_key: form.app_api_key,
    })

    successMessage.value = 'App Pool 已建立'
    form.app_name = ''
    form.app_mode = 'chat'
    form.app_api_key = ''
    await fetchPools()
  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      errorMessage.value = data.errors[firstKey][0]
    } else {
      errorMessage.value = data?.message || '建立失敗'
    }
  } finally {
    creating.value = false
  }
}

async function updateStatus(item) {
  try {
    await api.put(`/dify-app-pools/${item.id}`, {
      status: item.status,
    })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '更新狀態失敗'
    await fetchPools()
  }
}

async function copyKey(key) {
  try {
    await navigator.clipboard.writeText(key)
    successMessage.value = 'App API Key 已複製'
  } catch {
    errorMessage.value = '複製失敗'
  }
}

function formatDate(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('zh-TW')
}

onMounted(fetchPools)
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
.page-head h2 { margin: 0 0 6px; }
.page-head p { margin: 0; color: #6b7280; }
.create-card {
  background: #f8fafc;
  border-radius: 16px;
  padding: 18px;
  margin-bottom: 20px;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}
.form-group { display: flex; flex-direction: column; }
.form-group.full { grid-column: span 2; }
.form-group label { margin-bottom: 8px; font-size: 14px; font-weight: 600; }
.form-group input, .form-group select {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
}
.actions { margin-top: 16px; }
.primary-btn, .ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
}
.primary-btn { background: #111827; color: #fff; }
.ghost-btn { background: #eef2f7; color: #111827; }
.sm { padding: 8px 10px; font-size: 12px; }
.success-message { margin-top: 12px; color: #15803d; }
.error-message { margin-top: 12px; color: #dc2626; }
.table-wrap { overflow-x: auto; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td {
  padding: 14px 12px;
  border-bottom: 1px solid #eef2f7;
  text-align: left;
  vertical-align: top;
}
.table th { font-size: 13px; color: #6b7280; }
.empty-box { color: #6b7280; text-align: center; padding: 24px; }
@media (max-width: 900px) {
  .form-grid { grid-template-columns: 1fr; }
  .form-group.full { grid-column: span 1; }
}
</style>
