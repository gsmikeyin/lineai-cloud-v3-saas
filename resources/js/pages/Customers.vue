<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>{{ $t('adminPages.customers.title') }}</h2>
        <p>{{ $t('adminPages.customers.desc') }}</p>
      </div>
      <button class="ghost-btn" type="button" @click="fetchCustomers">{{ $t('adminPages.customers.refresh') }}</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>{{ $t('adminPages.customers.name') }}</th>
            <th>{{ $t('adminPages.customers.phone') }}</th>
            <th>{{ $t('adminPages.customers.email') }}</th>
            <th>{{ $t('adminPages.customers.totalMessages') }}</th>
            <th>{{ $t('adminPages.customers.lastInteraction') }}</th>
            <th>{{ $t('adminPages.customers.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in customers" :key="item.id">
            <td>{{ item.display_name || '-' }}</td>
            <td>{{ item.phone || '-' }}</td>
            <td>{{ item.email || '-' }}</td>
            <td>{{ item.total_messages ?? 0 }}</td>
            <td>{{ formatDate(item.last_interaction_at) }}</td>
            <td>
              <router-link :to="`/app/customers/${item.id}`" class="view-link">
                {{ $t('adminPages.customers.view') }}
              </router-link>
            </td>
          </tr>

          <tr v-if="customers.length === 0">
            <td colspan="6">
              <div class="empty-box">{{ $t('adminPages.customers.empty') }}</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../api'

const customers = ref([])

async function fetchCustomers() {
  const res = await api.get('/customers')
  customers.value = res.data.data || res.data || []
}

function formatDate(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('zh-TW')
}

onMounted(fetchCustomers)
</script>

<style scoped>
.page-card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:16px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.ghost-btn { border:0; border-radius:8px; padding:10px 14px; background:#eef2f7; cursor:pointer; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:14px 12px; border-bottom:1px solid #eef2f7; text-align:left; }
.table th { font-size:13px; color:#6b7280; }
.view-link { text-decoration:none; color:#2563eb; font-weight:600; }
.empty-box { color:#6b7280; text-align:center; padding:24px; }
</style>
