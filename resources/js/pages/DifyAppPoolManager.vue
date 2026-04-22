<template>
  <div class="page-grid">
    <div class="card">
      <div class="page-head">
        <div>
          <h2>Dify App Pool 管理</h2>
          <p>管理 App Pool、查看指派紀錄、執行 release / reassign。</p>
        </div>
        <button class="ghost-btn" @click="fetchPools">刷新</button>
      </div>

      <form class="create-form" @submit.prevent="createPool">
        <div class="form-row">
          <input v-model="createForm.app_name" type="text" placeholder="App Name" />
          <input v-model="createForm.app_api_key" type="text" placeholder="App API Key" />
          <select v-model="createForm.app_mode">
            <option value="chat">chat</option>
            <option value="workflow">workflow</option>
          </select>
          <button class="primary-btn" type="submit" :disabled="creating">
            {{ creating ? '建立中...' : '新增 App Pool' }}
          </button>
        </div>
      </form>

      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
      <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>App Name</th>
              <th>Mode</th>
              <th>Status</th>
              <th>Assigned Tenant</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in pools" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.app_name }}</td>
              <td>{{ item.app_mode }}</td>
              <td>
                <span class="badge" :class="badgeClass(item.status)">
                  {{ item.status }}
                </span>
              </td>
              <td>{{ item.assigned_tenant_id || '-' }}</td>
              <td class="actions">
                <button class="ghost-btn sm" @click="openLogs(item)">Logs</button>
                <button
                  class="ghost-btn sm"
                  @click="releasePool(item)"
                  :disabled="item.status !== 'assigned'"
                >
                  Release
                </button>
                <button class="ghost-btn sm" @click="openReassign(item)">Reassign</button>
              </td>
            </tr>

            <tr v-if="!pools.length">
              <td colspan="6">
                <div class="empty-box">目前沒有 App Pool 資料</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="selectedPool" class="card">
      <div class="page-head">
        <div>
          <h3>指派紀錄 - {{ selectedPool.app_name }}</h3>
          <p>顯示該 App Pool 的歷史指派紀錄</p>
        </div>
      </div>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Tenant</th>
              <th>Action</th>
              <th>Remark</th>
              <th>At</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in assignments" :key="log.id">
              <td>{{ log.tenant_id }}</td>
              <td>{{ log.action }}</td>
              <td>{{ log.remark || '-' }}</td>
              <td>{{ formatDate(log.created_at) }}</td>
            </tr>

            <tr v-if="!assignments.length">
              <td colspan="4">
                <div class="empty-box">沒有指派紀錄</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="reassign-box">
        <h4>重新指派</h4>
        <div class="form-row form-row-2">
          <input v-model="reassignForm.tenant_id" type="number" placeholder="New Tenant ID" />
          <input v-model="reassignForm.remark" type="text" placeholder="Remark" />
          <button class="primary-btn" @click="submitReassign" :disabled="reassigning">
            {{ reassigning ? '處理中...' : '執行 Reassign' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>

import { isAdmin } from '../utils/auth'
import { useRouter } from 'vue-router'


import { onMounted, ref, reactive } from 'vue'

import api from '../api'

const pools = ref([])
const assignments = ref([])
const selectedPool = ref(null)

const creating = ref(false)
const reassigning = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const router = useRouter()


const createForm = reactive({
  app_name: '',
  app_api_key: '',
  app_mode: 'chat',
})

const reassignForm = reactive({
  tenant_id: '',
  remark: '',
})

async function fetchPools() {

   if (!isAdmin()) {
    router.replace('/app/dashboard')
  }

  
  successMessage.value = ''
  errorMessage.value = ''
  try {
    const res = await api.get('/dify-app-pools')
    pools.value = res.data.data || res.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取 App Pool 失敗'
  }
}

async function createPool() {
  creating.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post('/dify-app-pools', createForm)

    successMessage.value = 'App Pool 已建立'
    createForm.app_name = ''
    createForm.app_api_key = ''
    createForm.app_mode = 'chat'

    await fetchPools()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '建立失敗'
  } finally {
    creating.value = false
  }
}

async function openLogs(item) {
  selectedPool.value = item
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.get(`/dify-app-pools/${item.id}/assignments`)
    assignments.value = res.data.data || res.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取指派紀錄失敗'
  }
}

async function releasePool(item) {
  if (!confirm(`Release ${item.app_name}?`)) return

  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post(`/dify-app-pools/${item.id}/release`, {
      remark: 'released from admin ui',
    })

    successMessage.value = '已釋放 App Pool'
    await fetchPools()

    if (selectedPool.value?.id === item.id) {
      await openLogs(item)
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Release 失敗'
  }
}

function openReassign(item) {
  selectedPool.value = item
  if (!assignments.value.length) {
    openLogs(item)
  }
}

async function submitReassign() {
  if (!selectedPool.value || !reassignForm.tenant_id) return

  reassigning.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.post(`/dify-app-pools/${selectedPool.value.id}/reassign`, {
      tenant_id: Number(reassignForm.tenant_id),
      remark: reassignForm.remark || 'reassigned from admin ui',
    })

    successMessage.value = '已重新指派 App Pool'
    reassignForm.tenant_id = ''
    reassignForm.remark = ''

    await fetchPools()
    await openLogs(selectedPool.value)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Reassign 失敗'
  } finally {
    reassigning.value = false
  }
}

function badgeClass(status) {
  if (status === 'assigned') return 'status-warning'
  if (status === 'available') return 'status-success'
  return 'status-default'
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
.page-grid { display:grid; grid-template-columns:1.15fr 0.85fr; gap:20px; }
.card { background:#fff; border-radius:18px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
.page-head h2,.page-head h3 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.create-form,.reassign-box { margin-bottom:18px; }
.form-row { display:grid; grid-template-columns:1.2fr 1.5fr 160px auto; gap:12px; }
.form-row-2 { grid-template-columns:1fr 1fr auto; }
input,select { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:12px; padding:12px 14px; }
.primary-btn,.ghost-btn { border:0; border-radius:10px; padding:10px 14px; cursor:pointer; }
.primary-btn { background:#111827; color:#fff; }
.ghost-btn { background:#eef2f7; color:#111827; }
.sm { padding:8px 10px; font-size:12px; }
.success-message { margin-bottom:12px; color:#15803d; }
.error-message { margin-bottom:12px; color:#dc2626; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:14px 12px; border-bottom:1px solid #eef2f7; text-align:left; vertical-align:top; }
.table th { font-size:13px; color:#6b7280; }
.actions { display:flex; gap:8px; flex-wrap:wrap; }
.badge { padding:4px 8px; border-radius:999px; font-size:12px; }
.status-success { background:#dcfce7; color:#166534; }
.status-warning { background:#fef3c7; color:#92400e; }
.status-default { background:#eef2f7; color:#374151; }
.empty-box { color:#6b7280; text-align:center; padding:24px; }
.reassign-box h4 { margin:0 0 12px; }
@media (max-width: 980px) {
  .page-grid, .form-row, .form-row-2 { grid-template-columns:1fr; }
}
</style>