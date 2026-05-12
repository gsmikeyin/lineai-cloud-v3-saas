<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>登入註冊統計</h2>
        <p>查看每日、每週、每月的登入人數、登入次數與註冊人數。</p>
      </div>

      <div class="actions">
        <div class="period-tabs" role="tablist" aria-label="登入註冊統計區間">
          <button
            v-for="option in periodOptions"
            :key="option.value"
            type="button"
            class="period-tab"
            :class="{ active: period === option.value }"
            @click="selectPeriod(option.value)"
          >
            {{ option.label }}
          </button>
        </div>
        <button class="ghost-btn" type="button" @click="fetchStats">重新整理</button>
      </div>
    </div>

    <div class="summary-grid">
      <div class="summary-item">
        <div class="summary-title">登入人數</div>
        <div class="summary-value">{{ summary.login_users }}</div>
      </div>
      <div class="summary-item">
        <div class="summary-title">登入次數</div>
        <div class="summary-value">{{ summary.login_events }}</div>
      </div>
      <div class="summary-item">
        <div class="summary-title">註冊人數</div>
        <div class="summary-value">{{ summary.registered_users }}</div>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>期間</th>
            <th>登入人數</th>
            <th>登入次數</th>
            <th>註冊人數</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in pagedRows" :key="item.period">
            <td>{{ item.period }}</td>
            <td>{{ item.login_users }}</td>
            <td>{{ item.login_events }}</td>
            <td>{{ item.registered_users }}</td>
          </tr>

          <tr v-if="!loading && rows.length === 0">
            <td colspan="4" class="empty">目前沒有統計資料</td>
          </tr>

          <tr v-if="loading">
            <td colspan="4" class="empty">載入中...</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="rows.length > 0" class="pagination">
      <div class="page-info">
        第 {{ currentPage }} / {{ totalPages }} 頁，共 {{ rows.length }} 筆
      </div>

      <div class="page-actions">
        <label>
          每頁
          <select v-model.number="pageSize" @change="goPage(1)">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </label>

        <button type="button" class="ghost-btn" :disabled="currentPage === 1" @click="goPage(currentPage - 1)">
          上一頁
        </button>
        <button type="button" class="ghost-btn" :disabled="currentPage === totalPages" @click="goPage(currentPage + 1)">
          下一頁
        </button>
      </div>
    </div>

    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../api'

const period = ref('daily')
const rows = ref([])
const loading = ref(false)
const errorMessage = ref('')
const currentPage = ref(1)
const pageSize = ref(10)
const summary = ref({
  login_users: 0,
  login_events: 0,
  registered_users: 0,
})

const periodOptions = [
  { label: '每日', value: 'daily' },
  { label: '每週', value: 'weekly' },
  { label: '每月', value: 'monthly' },
]

async function fetchStats() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.get('/analytics/auth', {
      params: { period: period.value },
    })

    rows.value = res.data.data || []
    currentPage.value = 1
    summary.value = res.data.summary || {
      login_users: 0,
      login_events: 0,
      registered_users: 0,
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取登入註冊統計失敗'
  } finally {
    loading.value = false
  }
}

function selectPeriod(value) {
  if (period.value === value) return

  period.value = value
  fetchStats()
}

const totalPages = computed(() => Math.max(1, Math.ceil(rows.value.length / pageSize.value)))

const pagedRows = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return rows.value.slice(start, start + pageSize.value)
})

function goPage(page) {
  currentPage.value = Math.min(Math.max(page, 1), totalPages.value)
}

onMounted(fetchStats)
</script>

<style scoped>
.page-card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:20px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.period-tabs { display:inline-flex; border:1px solid #d1d5db; border-radius:8px; overflow:hidden; background:#fff; }
.period-tab { border:0; border-right:1px solid #d1d5db; min-width:64px; padding:10px 14px; background:#fff; color:#374151; cursor:pointer; }
.period-tab:last-child { border-right:0; }
.period-tab.active { background:#111827; color:#fff; font-weight:700; }
.ghost-btn { border:0; border-radius:8px; padding:10px 14px; background:#eef2f7; cursor:pointer; }
.summary-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
.summary-item { border:1px solid #eef2f7; border-radius:8px; padding:14px; background:#f9fafb; }
.summary-title { font-weight:700; color:#111827; }
.summary-value { margin-top:14px; font-size:30px; font-weight:800; color:#2563eb; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:13px 12px; border-bottom:1px solid #eef2f7; text-align:left; }
.table th { color:#6b7280; font-size:13px; }
.empty { text-align:center; color:#6b7280; padding:24px; }
.pagination { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:16px; color:#6b7280; font-size:14px; }
.page-actions { display:flex; align-items:center; gap:10px; }
.page-actions label { display:flex; align-items:center; gap:8px; }
.page-actions select { border:1px solid #d1d5db; border-radius:8px; padding:8px 10px; background:#fff; }
.page-actions .ghost-btn:disabled { opacity:.5; cursor:not-allowed; }
.error-message { margin-top:14px; color:#dc2626; }
@media (max-width:900px) { .summary-grid { grid-template-columns:1fr; } }
@media (max-width:700px) { .page-head,.actions,.pagination,.page-actions { flex-direction:column; align-items:flex-start; } }
</style>
