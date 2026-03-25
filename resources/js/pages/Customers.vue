<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>客戶</h2>
        <p>管理 LINE 客戶資料與互動狀態</p>
      </div>
      <button class="ghost-btn" @click="fetchCustomers">刷新</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>名稱</th>
            <th>電話</th>
            <th>Email</th>
            <th>VIP</th>
            <th>訊息數</th>
            <th>最後互動</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in customers" :key="item.id">
            <td>{{ item.display_name || '-' }}</td>
            <td>{{ item.phone || '-' }}</td>
            <td>{{ item.email || '-' }}</td>
            <td>{{ item.is_vip ? '是' : '否' }}</td>
            <td>{{ item.total_messages ?? 0 }}</td>
            <td>{{ formatDate(item.last_interaction_at) }}</td>
            <td>
              <router-link :to="`/customers/${item.id}`" class="view-link">
                查看
              </router-link>
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
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  background: #eef2f7;
  cursor: pointer;
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
}
.table th {
  font-size: 13px;
  color: #6b7280;
}
.view-link {
  text-decoration: none;
  color: #2563eb;
  font-weight: 600;
}
</style>