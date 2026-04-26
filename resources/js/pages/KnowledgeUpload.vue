<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>Dify Knowledge Upload</h2>
        <p>上傳 PDF / DOCX / TXT / MD 到 Dify Dataset，每個租戶最多 2 個檔案。</p>
      </div>
      <button class="ghost-btn" type="button" @click="fetchDocuments">重新整理</button>
    </div>

    <div class="upload-card">
      <form @submit.prevent="submitUpload">
        <div class="upload-row">
          <input
            ref="fileInput"
            type="file"
            accept=".pdf,.txt,.doc,.docx,.md"
            :disabled="uploadLimitReached"
            @change="handleFileChange"
          />

          <button class="primary-btn" type="submit" :disabled="uploading || !selectedFile || uploadLimitReached">
            {{ uploading ? '上傳中...' : '上傳文件' }}
          </button>
        </div>

        <div class="limit-info" :class="{ reached: uploadLimitReached }">
          已上傳 {{ documents.length }} / {{ MAX_DOCUMENTS }} 個檔案
        </div>

        <div v-if="selectedFile" class="file-info">
          已選擇：{{ selectedFile.name }}（{{ formatSize(selectedFile.size) }}）
        </div>

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>
      </form>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>檔名</th>
            <th>類型</th>
            <th>大小</th>
            <th>狀態</th>
            <th>索引狀態</th>
            <th>Dify Document ID</th>
            <th>建立時間</th>
            <th class="action-col">操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in documents" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.mime_type || '-' }}</td>
            <td>{{ formatSize(item.file_size) }}</td>
            <td>
              <span class="badge" :class="statusClass(item.status)">
                {{ item.status || '-' }}
              </span>
            </td>
            <td>{{ item.indexing_status || '-' }}</td>
            <td class="mono">{{ item.dify_document_id || '-' }}</td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td class="action-col">
              <button class="danger-btn" type="button" :disabled="deletingId === item.id" @click="deleteDocument(item)">
                {{ deletingId === item.id ? '刪除中...' : '刪除' }}
              </button>
            </td>
          </tr>

          <tr v-if="!loading && documents.length === 0">
            <td colspan="8">
              <div class="empty-box">尚未上傳任何知識文件</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../api'

const MAX_DOCUMENTS = 2
const loading = ref(false)
const uploading = ref(false)
const deletingId = ref(null)
const selectedFile = ref(null)
const documents = ref([])
const fileInput = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const uploadLimitReached = computed(() => documents.value.length >= MAX_DOCUMENTS)

function handleFileChange(event) {
  if (uploadLimitReached.value) {
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
    errorMessage.value = `最多只能上傳 ${MAX_DOCUMENTS} 個檔案`
    return
  }

  const file = event.target.files?.[0] || null

  if (file && isDuplicateFile(file)) {
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
    errorMessage.value = '此檔案已上傳，請選擇其他檔案'
    return
  }

  selectedFile.value = file
}

async function submitUpload() {
  if (!selectedFile.value) return
  if (uploadLimitReached.value) {
    errorMessage.value = `最多只能上傳 ${MAX_DOCUMENTS} 個檔案`
    return
  }
  if (isDuplicateFile(selectedFile.value)) {
    errorMessage.value = '此檔案已上傳，請選擇其他檔案'
    return
  }

  uploading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const res = await api.post('/knowledge/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    successMessage.value = res.data?.success ? '文件已上傳到 Dify，正在建立索引。' : '上傳完成'
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''

    await fetchDocuments()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '上傳失敗'
  } finally {
    uploading.value = false
  }
}

async function deleteDocument(item) {
  if (!window.confirm(`確定要刪除「${item.name}」？`)) return

  deletingId.value = item.id
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.delete(`/knowledge/documents/${item.id}`)
    successMessage.value = '文件已刪除'
    await fetchDocuments()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '刪除失敗'
  } finally {
    deletingId.value = null
  }
}

async function fetchDocuments() {
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const res = await api.get('/knowledge/documents')
    documents.value = res.data.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '讀取文件失敗'
  } finally {
    loading.value = false
  }
}

function formatDate(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('zh-TW')
}

function formatSize(size) {
  if (!size) return '-'
  if (size < 1024) return `${size} B`
  if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`
  return `${(size / 1024 / 1024).toFixed(1)} MB`
}

function statusClass(status) {
  if (status === 'available') return 'status-success'
  if (status === 'indexing') return 'status-warning'
  if (status === 'failed') return 'status-danger'
  return 'status-default'
}

function isDuplicateFile(file) {
  return documents.value.some((item) => item.name === file.name && Number(item.file_size) === file.size)
}

onMounted(fetchDocuments)
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
.upload-card {
  background: #f8fafc;
  border-radius: 16px;
  padding: 18px;
  margin-bottom: 20px;
}
.upload-row {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}
.primary-btn,
.ghost-btn,
.danger-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
}
.primary-btn:disabled,
.danger-btn:disabled {
  cursor: not-allowed;
  opacity: 0.55;
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
  color: #991b1b;
}
.file-info {
  margin-top: 12px;
  color: #4b5563;
}
.limit-info {
  margin-top: 12px;
  color: #4b5563;
  font-size: 14px;
}
.limit-info.reached {
  color: #dc2626;
}
.success-message {
  margin-top: 12px;
  color: #15803d;
}
.error-message {
  margin-top: 12px;
  color: #dc2626;
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
.action-col {
  white-space: nowrap;
  text-align: right;
}
.badge {
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
}
.status-success {
  background: #dcfce7;
  color: #166534;
}
.status-warning {
  background: #fef3c7;
  color: #92400e;
}
.status-danger {
  background: #fee2e2;
  color: #991b1b;
}
.status-default {
  background: #eef2f7;
  color: #374151;
}
.mono {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: 12px;
}
.empty-box {
  color: #6b7280;
  text-align: center;
  padding: 24px;
}
</style>
