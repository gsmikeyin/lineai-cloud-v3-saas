<template>
  <div class="card">
    <div class="page-head">
      <div>
        <h2>使用者權限</h2>
        <p>調整使用者角色為 super_admin、admin、free、basic 或 pro。</p>
      </div>
      <button class="ghost-btn" type="button" @click="fetchUsers">重新整理</button>
    </div>

    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>

    <div class="toolbar">
      <input v-model="keyword" type="search" placeholder="搜尋 Name 或 Email" @keyup.enter="search" />
      <button class="ghost-btn" type="button" @click="search">搜尋</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Tenant</th>
            <th>Status</th>
            <th>Role</th>
            <th class="action-col">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.name || '-' }}</td>
            <td>{{ user.email || '-' }}</td>
            <td>{{ tenantLabel(user) }}</td>
            <td>{{ user.status || '-' }}</td>
            <td>
              <select v-model="draftRoles[user.id]" :disabled="isSelf(user)">
                <option v-for="role in roleOptions" :key="role" :value="role">{{ role }}</option>
              </select>
              <div v-if="isSelf(user)" class="hint">不能修改自己的權限</div>
            </td>
            <td class="action-col">
              <button
                class="primary-btn"
                type="button"
                :disabled="savingId === user.id || isSelf(user) || draftRoles[user.id] === user.role"
                @click="saveRole(user)"
              >
                {{ savingId === user.id ? '儲存中...' : '儲存' }}
              </button>
            </td>
          </tr>

          <tr v-if="!loading && users.length === 0">
            <td colspan="7"><div class="empty-box">目前沒有使用者資料</div></td>
          </tr>

          <tr v-if="loading">
            <td colspan="7"><div class="empty-box">載入中...</div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="total > 0" class="pagination">
      <div>第 {{ currentPage }} / {{ totalPages }} 頁，共 {{ total }} 筆</div>
      <div class="page-actions">
        <label>
          每頁
          <select v-model.number="perPage" @change="goPage(1)">
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="30">30</option>
            <option :value="50">50</option>
          </select>
        </label>
        <button class="ghost-btn" type="button" :disabled="currentPage === 1" @click="goPage(currentPage - 1)">上一頁</button>
        <button class="ghost-btn" type="button" :disabled="currentPage === totalPages" @click="goPage(currentPage + 1)">下一頁</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../api'
import { getAuthUser } from '../utils/auth'

const roleOptions = ['super_admin', 'admin', 'free', 'basic', 'pro']
const users = ref([])
const draftRoles = ref({})
const keyword = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const total = ref(0)
const loading = ref(false)
const savingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')
const authUser = getAuthUser()

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / perPage.value)))

async function fetchUsers() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.get('/admin/users/roles', {
      params: {
        keyword: keyword.value.trim() || undefined,
        page: currentPage.value,
        per_page: perPage.value,
      },
    })

    users.value = res.data.data || []
    total.value = res.data.total || 0
    currentPage.value = res.data.current_page || currentPage.value
    draftRoles.value = Object.fromEntries(users.value.map((user) => [user.id, user.role]))
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取使用者權限失敗'
  } finally {
    loading.value = false
  }
}

function search() {
  currentPage.value = 1
  fetchUsers()
}

function goPage(page) {
  currentPage.value = Math.min(Math.max(page, 1), totalPages.value)
  fetchUsers()
}

async function saveRole(user) {
  const role = draftRoles.value[user.id]
  if (!role || role === user.role) return
  if (!window.confirm(`確認將 ${user.email || user.name} 的權限改為 ${role}？`)) return

  savingId.value = user.id
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.put(`/admin/users/${user.id}/role`, { role })
    const updated = res.data.data
    users.value = users.value.map((item) => (item.id === user.id ? updated : item))
    draftRoles.value[user.id] = updated.role
    successMessage.value = res.data.message || '權限已更新'
  } catch (error) {
    draftRoles.value[user.id] = user.role
    errorMessage.value = error.response?.data?.message || '更新使用者權限失敗'
  } finally {
    savingId.value = null
  }
}

function isSelf(user) {
  return authUser?.id === user.id
}

function tenantLabel(user) {
  return user.tenant?.name || user.tenant?.contact_email || user.tenant_id || '-'
}

onMounted(fetchUsers)
</script>

<style scoped>
.card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.toolbar { display:flex; gap:10px; max-width:520px; margin-bottom:14px; }
input,select { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:8px; padding:10px 12px; background:#fff; }
.ghost-btn,.primary-btn { border:0; border-radius:8px; padding:10px 14px; cursor:pointer; white-space:nowrap; }
.ghost-btn { background:#eef2f7; color:#111827; }
.primary-btn { background:#2563eb; color:#fff; }
.primary-btn:disabled,.ghost-btn:disabled { cursor:not-allowed; opacity:.55; }
.error-message,.success-message { margin-bottom:12px; font-size:14px; }
.error-message { color:#dc2626; }
.success-message { color:#15803d; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:14px 12px; border-bottom:1px solid #eef2f7; text-align:left; vertical-align:top; }
.table th { font-size:13px; color:#6b7280; }
.action-col { text-align:right; }
.hint { margin-top:6px; color:#6b7280; font-size:12px; }
.empty-box { color:#6b7280; text-align:center; padding:24px; }
.pagination { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:16px; color:#6b7280; font-size:14px; }
.page-actions { display:flex; align-items:center; gap:10px; }
.page-actions label { display:flex; align-items:center; gap:8px; }
.page-actions select { min-width:78px; }
@media (max-width:700px) {
  .page-head,.toolbar,.pagination,.page-actions { flex-direction:column; align-items:flex-start; }
  .toolbar { max-width:none; }
}
</style>
