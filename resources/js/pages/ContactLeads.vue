<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>Contact Leads</h2>
        <p>管理前台聯絡表單送進來的潛在客戶資料</p>
      </div>
      <button class="ghost-btn" @click="fetchLeads">刷新</button>
    </div>

    <div class="toolbar">
      <input v-model="filters.keyword" type="text" placeholder="搜尋姓名 / Email / 公司 / 電話 / 內容" />
      <select v-model="filters.status">
        <option value="">全部狀態</option>
        <option value="new">New</option>
        <option value="contacted">Contacted</option>
        <option value="closed">Closed</option>
      </select>
      <button class="ghost-btn" @click="search">搜尋</button>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>姓名</th>
            <th>Email</th>
            <th>公司</th>
            <th>電話</th>
            <th>狀態</th>
            <th>建立時間</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in leads" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.email }}</td>
            <td>{{ item.company || '-' }}</td>
            <td>{{ item.phone || '-' }}</td>
            <td>
              <select v-model="item.status" @change="updateStatus(item)">
                <option value="new">new</option>
                <option value="contacted">contacted</option>
                <option value="closed">closed</option>
              </select>
            </td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td>
              <button class="ghost-btn sm" @click="openLead(item)">查看</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="selectedLead" class="modal-mask" @click.self="selectedLead = null">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Lead 詳細資料</h3>
          <button class="close-btn" @click="selectedLead = null">✕</button>
        </div>

        <div class="detail-grid">
          <div><strong>姓名：</strong>{{ selectedLead.name }}</div>
          <div><strong>Email：</strong>{{ selectedLead.email }}</div>
          <div><strong>公司：</strong>{{ selectedLead.company || '-' }}</div>
          <div><strong>電話：</strong>{{ selectedLead.phone || '-' }}</div>
          <div><strong>狀態：</strong>{{ selectedLead.status }}</div>
          <div><strong>建立時間：</strong>{{ formatDate(selectedLead.created_at) }}</div>
          <div class="full"><strong>內容：</strong></div>
          <div class="full message-box">{{ selectedLead.message }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../api'

const leads = ref([])
const selectedLead = ref(null)

const filters = reactive({
  keyword: '',
  status: '',
})

async function fetchLeads() {
  const res = await api.get('/contact-leads', {
    params: {
      keyword: filters.keyword || undefined,
      status: filters.status || undefined,
    },
  })

  leads.value = res.data.data || []
}

async function search() {
  await fetchLeads()
}

async function updateStatus(item) {
  await api.put(`/contact-leads/${item.id}`, {
    status: item.status,
  })
}

function openLead(item) {
  selectedLead.value = item
}

function formatDate(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('zh-TW')
}

onMounted(fetchLeads)
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
.toolbar {
  display: grid;
  grid-template-columns: 1fr 220px auto;
  gap: 12px;
  margin-bottom: 18px;
}
.toolbar input,
.toolbar select {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
}
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  background: #eef2f7;
  cursor: pointer;
}
.sm {
  padding: 8px 10px;
  font-size: 12px;
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
.modal-mask {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.modal-card {
  width: 100%;
  max-width: 760px;
  background: #fff;
  border-radius: 18px;
  padding: 24px;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 18px;
}
.close-btn {
  border: 0;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
}
.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}
.full {
  grid-column: span 2;
}
.message-box {
  background: #f8fafc;
  border-radius: 12px;
  padding: 14px;
  line-height: 1.8;
  white-space: pre-wrap;
}
@media (max-width: 900px) {
  .toolbar,
  .detail-grid {
    grid-template-columns: 1fr;
  }
  .full {
    grid-column: span 1;
  }
}
</style>