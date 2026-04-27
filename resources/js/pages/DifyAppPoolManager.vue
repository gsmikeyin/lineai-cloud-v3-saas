<template>
  <div class="card">
    <div class="page-head">
      <div>
        <h2>Dify App Pool 管理</h2>
        <p>查看 App Pool 指派狀態。</p>
      </div>
      <button class="ghost-btn" type="button" @click="fetchPools">重新整理</button>
    </div>

    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

    <div class="search-row">
      <input v-model="searchKeyword" type="search" placeholder="Search by name or email" />
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>App Name</th>
            <th>Status</th>
            <th>Assigned Tenant</th>
            <th>Name</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredPools" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.app_name }}</td>
            <td>
              <span class="badge" :class="badgeClass(item.status)">
                {{ item.status }}
              </span>
            </td>
            <td>{{ item.assigned_tenant_id || '-' }}</td>
            <td>{{ assignedName(item) }}</td>
            <td>{{ assignedEmail(item) }}</td>
          </tr>

          <tr v-if="!filteredPools.length">
            <td colspan="6">
              <div class="empty-box">尚無 App Pool 資料</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import { isAdmin } from '../utils/auth'

const router = useRouter()
const pools = ref([])
const errorMessage = ref('')
const searchKeyword = ref('')

const filteredPools = computed(() => {
  const keyword = searchKeyword.value.trim().toLowerCase()

  if (!keyword) return pools.value

  return pools.value.filter((item) => {
    const name = assignedName(item).toLowerCase()
    const email = assignedEmail(item).toLowerCase()

    return name.includes(keyword) || email.includes(keyword)
  })
})

async function fetchPools() {
  if (!isAdmin()) {
    router.replace('/app/dashboard')
    return
  }

  errorMessage.value = ''

  try {
    const res = await api.get('/dify-app-pools')
    pools.value = res.data.data || res.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取 App Pool 失敗'
  }
}

function badgeClass(status) {
  if (status === 'assigned') return 'status-warning'
  if (status === 'available') return 'status-success'
  return 'status-default'
}

function assignedName(item) {
  return item.assigned_tenant?.contact_name
    || item.assignedTenant?.contact_name
    || item.assigned_tenant?.name
    || item.assignedTenant?.name
    || '-'
}

function assignedEmail(item) {
  return item.assigned_tenant?.contact_email
    || item.assignedTenant?.contact_email
    || '-'
}

onMounted(fetchPools)
</script>

<style scoped>
.card { background:#fff; border-radius:18px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.search-row { margin-bottom:14px; max-width:420px; }
input { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:12px; padding:12px 14px; }
.ghost-btn { border:0; border-radius:10px; padding:10px 14px; cursor:pointer; background:#eef2f7; color:#111827; }
.error-message { margin-bottom:12px; color:#dc2626; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:14px 12px; border-bottom:1px solid #eef2f7; text-align:left; vertical-align:top; }
.table th { font-size:13px; color:#6b7280; }
.badge { padding:4px 8px; border-radius:999px; font-size:12px; }
.status-success { background:#dcfce7; color:#166534; }
.status-warning { background:#fef3c7; color:#92400e; }
.status-default { background:#eef2f7; color:#374151; }
.empty-box { color:#6b7280; text-align:center; padding:24px; }
</style>
