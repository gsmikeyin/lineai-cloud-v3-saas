<template>
  <div class="card">
    <div class="page-head">
      <div>
        <h2>{{ $t('adminPages.difyPool.title') }}</h2>
        <p>{{ $t('adminPages.difyPool.desc') }}</p>
      </div>
      <button class="ghost-btn" type="button" @click="fetchPools">{{ $t('adminPages.difyPool.refresh') }}</button>
    </div>

    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

    <div class="search-row">
      <input v-model="searchKeyword" type="search" :placeholder="$t('adminPages.difyPool.searchPlaceholder')" />
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>{{ $t('adminPages.difyPool.appName') }}</th>
            <th>{{ $t('adminPages.difyPool.status') }}</th>
            <th>{{ $t('adminPages.difyPool.assignedTenant') }}</th>
            <th>{{ $t('adminPages.difyPool.name') }}</th>
            <th>{{ $t('adminPages.difyPool.email') }}</th>
            <th class="action-col">{{ $t('adminPages.difyPool.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredPools" :key="item.id">
            <td>{{ item.id }}</td>
            <td>{{ item.app_name }}</td>
            <td><span class="badge" :class="badgeClass(item.status)">{{ item.status }}</span></td>
            <td>{{ item.assigned_tenant_id || '-' }}</td>
            <td>{{ assignedName(item) }}</td>
            <td>{{ assignedEmail(item) }}</td>
            <td class="action-col">
              <button class="danger-btn" type="button" :disabled="deletingId === item.id" @click="confirmDelete(item)">
                {{ deletingId === item.id ? $t('adminPages.difyPool.deleting') : $t('adminPages.difyPool.delete') }}
              </button>
            </td>
          </tr>

          <tr v-if="!filteredPools.length">
            <td colspan="7"><div class="empty-box">{{ $t('adminPages.difyPool.empty') }}</div></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import api from '../api'
import { isAdmin } from '../utils/auth'

const { t } = useI18n()
const router = useRouter()
const pools = ref([])
const errorMessage = ref('')
const successMessage = ref('')
const searchKeyword = ref('')
const deletingId = ref(null)

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
    router.replace('/app')
    return
  }

  errorMessage.value = ''

  try {
    const res = await api.get('/dify-app-pools')
    pools.value = res.data.data || res.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.difyPool.loadFailed')
  }
}

async function confirmDelete(item) {
  const detail = item.assigned_tenant_id
    ? t('adminPages.difyPool.confirmAssigned')
    : t('adminPages.difyPool.confirmUnassigned')

  if (!window.confirm(t('adminPages.difyPool.confirmDelete', { name: item.app_name, detail }))) return

  deletingId.value = item.id
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.delete(`/dify-app-pools/${item.id}`)
    pools.value = pools.value.filter((pool) => pool.id !== item.id)
    successMessage.value = t('adminPages.difyPool.deleted')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.difyPool.deleteFailed')
  } finally {
    deletingId.value = null
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
.card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.search-row { margin-bottom:14px; max-width:420px; }
input { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:8px; padding:12px 14px; }
.ghost-btn,.danger-btn { border:0; border-radius:8px; padding:10px 14px; cursor:pointer; white-space:nowrap; }
.ghost-btn { background:#eef2f7; color:#111827; }
.danger-btn { background:#fee2e2; color:#b91c1c; }
.danger-btn:disabled { cursor:not-allowed; opacity:.7; }
.error-message,.success-message { margin-bottom:12px; font-size:14px; }
.error-message { color:#dc2626; }
.success-message { color:#15803d; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:14px 12px; border-bottom:1px solid #eef2f7; text-align:left; vertical-align:top; }
.table th { font-size:13px; color:#6b7280; }
.action-col { text-align:right; }
.badge { padding:4px 8px; border-radius:999px; font-size:12px; }
.status-success { background:#dcfce7; color:#166534; }
.status-warning { background:#fef3c7; color:#92400e; }
.status-default { background:#eef2f7; color:#374151; }
.empty-box { color:#6b7280; text-align:center; padding:24px; }
</style>
