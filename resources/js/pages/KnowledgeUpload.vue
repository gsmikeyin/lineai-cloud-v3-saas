<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>Dify Knowledge Upload</h2>
        <p>上傳 PDF / DOCX / TXT 到 Dify Dataset，並查看索引狀態。</p>
      </div>
      <button class="ghost-btn" @click="fetchDocuments">刷新</button>
    </div>

    <div class="upload-card">
      <form @submit.prevent="submitUpload">
        <div class="upload-row">
          <input
            ref="fileInput"
            type="file"
            accept=".pdf,.txt,.doc,.docx,.md"
            @change="handleFileChange"
          />
          <button class="primary-btn" type="submit" :disabled="uploading || !selectedFile">
            {{ uploading ? '上傳中...' : '上傳到知識庫' }}
          </button>
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
            <th>名稱</th>
            <th>類型</th>
            <th>大小</th>
            <th>狀態</th>
            <th>索引狀態</th>
            <th>Dify Document ID</th>
            <th>建立時間</th>
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
          </tr>

          <tr v-if="!loading && documents.length === 0">
            <td colspan="7">
              <div class="empty-box">目前還沒有上傳任何知識檔案</div>
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

const loading = ref(false)
const uploading = ref(false)
const selectedFile = ref(null)
const documents = ref([])
const fileInput = ref(null)
const successMessage = ref('')
const errorMessage = ref('')

function handleFileChange(event) {
  selectedFile.value = event.target.files?.[0] || null
}

async function submitUpload() {
  if (!selectedFile.value) return

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

    successMessage.value = res.data?.success
      ? '檔案已送出到 Dify，正在建立索引'
      : '上傳完成'

    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''

    await fetchDocuments()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || '上傳失敗'
  } finally {
    uploading.value = false
  }
}

async function fetchDocuments() {
  loading.value = true
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
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
}
.primary-btn {
  background: #111827;
  color: #fff;
}
.ghost-btn {
  background: #eef2f7;
  color: #111827;
}
.file-info {
  margin-top: 12px;
  color: #4b5563;
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