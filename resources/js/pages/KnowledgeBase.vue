<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>知識基礎</h2>
        <p>管理 FAQ、商品知識、政策與 AI 使用內容</p>
      </div>
      <button class="primary-btn" @click="openCreateModal">
        新增知識
      </button>
    </div>

    <div class="toolbar">
      <input
        v-model="filters.keyword"
        type="text"
        placeholder="搜尋標題 / 問題 / 答案"
      />

      <select v-model="filters.type">
        <option value="">全部類型</option>
        <option value="faq">FAQ</option>
        <option value="product">Product</option>
        <option value="policy">Policy</option>
        <option value="prompt">Prompt</option>
      </select>

      <select v-model="filters.status">
        <option value="">全部狀態</option>
        <option value="draft">Draft</option>
        <option value="published">Published</option>
        <option value="archived">Archived</option>
      </select>

      <button class="ghost-btn" @click="fetchItems">搜尋</button>
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
            <th>標題</th>
            <th>類型</th>
            <th>狀態</th>
            <th>AI</th>
            <th>排序</th>
            <th>關鍵字</th>
            <th>更新時間</th>
            <th>操作</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>
              <div class="title-cell">
                <div class="title-main">{{ item.title }}</div>
                <div class="title-sub">{{ item.question || '-' }}</div>
              </div>
            </td>
            <td>{{ item.type }}</td>
            <td>
              <span class="badge" :class="`status-${item.status}`">
                {{ item.status }}
              </span>
            </td>
            <td>
              <span class="badge" :class="item.is_ai_enabled ? 'ai-on' : 'ai-off'">
                {{ item.is_ai_enabled ? 'ON' : 'OFF' }}
              </span>
            </td>
            <td>{{ item.sort_order ?? 0 }}</td>
            <td>
              <div class="keyword-list">
                <span
                  v-for="kw in item.keywords || []"
                  :key="kw"
                  class="keyword-chip"
                >
                  {{ kw }}
                </span>
              </div>
            </td>
            <td>{{ formatDate(item.updated_at) }}</td>
            <td>
              <div class="action-group">
                <button class="ghost-btn sm" @click="openEditModal(item)">
                  編輯
                </button>
                <button class="danger-btn sm" @click="deleteItem(item)">
                  刪除
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!loading && items.length === 0">
            <td colspan="8">
              <div class="empty-box">目前沒有資料</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="pagination-bar" v-if="pagination.last_page > 1">
      <button
        class="ghost-btn"
        :disabled="pagination.current_page <= 1"
        @click="changePage(pagination.current_page - 1)"
      >
        上一頁
      </button>

      <div class="page-text">
        第 {{ pagination.current_page }} / {{ pagination.last_page }} 頁
      </div>

      <button
        class="ghost-btn"
        :disabled="pagination.current_page >= pagination.last_page"
        @click="changePage(pagination.current_page + 1)"
      >
        下一頁
      </button>
    </div>

    <div v-if="showModal" class="modal-mask" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ editingItem ? '編輯知識' : '新增知識' }}</h3>
          <button class="close-btn" @click="closeModal">✕</button>
        </div>

        <KnowledgeItemForm
          :model-value="editingItem || defaultForm"
          :submit-text="saving ? '儲存中...' : (editingItem ? '更新' : '建立')"
          @submit="handleSubmit"
          @cancel="closeModal"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../api'
import KnowledgeItemForm from '../components/KnowledgeItemForm.vue'

const loading = ref(false)
const saving = ref(false)
const items = ref([])
const showModal = ref(false)
const editingItem = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

const filters = reactive({
  keyword: '',
  type: '',
  status: '',
  page: 1,
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
})

const defaultForm = {
  type: 'faq',
  title: '',
  question: '',
  answer: '',
  content: '',
  status: 'draft',
  sort_order: 0,
  is_ai_enabled: true,
  keywords: [],
}

async function fetchItems() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.get('/knowledge-items', {
      params: {
        keyword: filters.keyword || undefined,
        type: filters.type || undefined,
        status: filters.status || undefined,
        page: filters.page,
      },
    })

    items.value = res.data.data || []
    pagination.current_page = res.data.current_page || 1
    pagination.last_page = res.data.last_page || 1
    pagination.total = res.data.total || 0
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '讀取 Knowledge Base 失敗'
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editingItem.value = null
  successMessage.value = ''
  errorMessage.value = ''
  showModal.value = true
}

function openEditModal(item) {
  editingItem.value = { ...item }
  successMessage.value = ''
  errorMessage.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingItem.value = null
}

async function handleSubmit(payload) {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    if (editingItem.value?.id) {
      await api.put(`/knowledge-items/${editingItem.value.id}`, payload)
      successMessage.value = '知識已更新'
    } else {
      await api.post('/knowledge-items', payload)
      successMessage.value = '知識已建立'
    }

    closeModal()
    await fetchItems()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '儲存失敗'
  } finally {
    saving.value = false
  }
}

async function deleteItem(item) {
  const ok = window.confirm(`確定要刪除「${item.title}」嗎？`)
  if (!ok) return

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.delete(`/knowledge-items/${item.id}`)
    successMessage.value = '知識已刪除'
    await fetchItems()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '刪除失敗'
  }
}

async function changePage(page) {
  filters.page = page
  await fetchItems()
}

function formatDate(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('zh-TW')
}

onMounted(fetchItems)
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
  grid-template-columns: 1.3fr 180px 180px auto;
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
  font-size: 14px;
}

.primary-btn,
.ghost-btn,
.danger-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
}

.primary-btn {
  background: #111827;
  color: #fff;
}

.ghost-btn {
  background: #eef2f7;
  color: #111827;
}

.danger-btn {
  background: #fee2e2;
  color: #b91c1c;
}

.sm {
  padding: 8px 10px;
  font-size: 12px;
}

.success-message {
  margin-bottom: 12px;
  color: #15803d;
  font-size: 14px;
}

.error-message {
  margin-bottom: 12px;
  color: #dc2626;
  font-size: 14px;
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

.title-main {
  font-weight: 700;
  color: #111827;
}

.title-sub {
  margin-top: 4px;
  font-size: 12px;
  color: #6b7280;
}

.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
}

.status-draft {
  background: #f3f4f6;
  color: #4b5563;
}

.status-published {
  background: #dcfce7;
  color: #166534;
}

.status-archived {
  background: #fee2e2;
  color: #991b1b;
}

.ai-on {
  background: #dbeafe;
  color: #1d4ed8;
}

.ai-off {
  background: #f3f4f6;
  color: #4b5563;
}

.keyword-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.keyword-chip {
  background: #eef2ff;
  color: #4338ca;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 999px;
}

.action-group {
  display: flex;
  gap: 8px;
}

.empty-box {
  text-align: center;
  color: #6b7280;
  padding: 24px;
}

.pagination-bar {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 12px;
  margin-top: 18px;
}

.page-text {
  font-size: 14px;
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
  z-index: 50;
}

.modal-card {
  width: 100%;
  max-width: 920px;
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.modal-header h3 {
  margin: 0;
}

.close-btn {
  border: 0;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
  color: #6b7280;
}

@media (max-width: 960px) {
  .toolbar {
    grid-template-columns: 1fr;
  }
}
</style>