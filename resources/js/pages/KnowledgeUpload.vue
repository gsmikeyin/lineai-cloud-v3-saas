<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>{{ $t('adminPages.knowledgeUpload.title') }}</h2>
        <p>{{ $t('adminPages.knowledgeUpload.desc') }}</p>
      </div>
      <div class="page-actions">
        <a class="ghost-btn" href="/files/sample_sales.pdf" download>樣本檔案下載</a>
        <button class="ghost-btn" type="button" @click="fetchDocuments">{{ $t('adminPages.knowledgeUpload.refresh') }}</button>
      </div>
    </div>

    <div class="upload-card">
      <form @submit.prevent="submitUpload">
        <div class="upload-row">
          <input ref="fileInput" type="file" accept=".pdf,.txt,.doc,.docx,.md" :disabled="uploadLimitReached" @change="handleFileChange" />

          <button class="primary-btn" type="submit" :disabled="uploading || !selectedFile || uploadLimitReached">
            {{ uploading ? $t('adminPages.knowledgeUpload.uploading') : $t('adminPages.knowledgeUpload.upload') }}
          </button>
        </div>

        <div class="limit-info" :class="{ reached: uploadLimitReached }">
          {{ t('adminPages.knowledgeUpload.limitInfo', { count: documents.length, max: maxDocuments, size: formatSize(MAX_FILE_SIZE_BYTES) }) }}
        </div>

        <div v-if="selectedFile" class="file-info">
          {{ t('adminPages.knowledgeUpload.selected', { name: selectedFile.name, size: formatSize(selectedFile.size) }) }}
        </div>

        <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      </form>
    </div>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>{{ $t('adminPages.knowledgeUpload.fileName') }}</th>
            <th>{{ $t('adminPages.knowledgeUpload.type') }}</th>
            <th>{{ $t('adminPages.knowledgeUpload.size') }}</th>
            <th>{{ $t('adminPages.knowledgeUpload.status') }}</th>
            <th>{{ $t('adminPages.knowledgeUpload.indexingStatus') }}</th>
            <th>Dify Document ID</th>
            <th>{{ $t('adminPages.knowledgeUpload.createdAt') }}</th>
            <th class="action-col">{{ $t('adminPages.knowledgeUpload.action') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in documents" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.mime_type || '-' }}</td>
            <td>{{ formatSize(item.file_size) }}</td>
            <td><span class="badge" :class="statusClass(item.status)">{{ item.status || '-' }}</span></td>
            <td>{{ item.indexing_status || '-' }}</td>
            <td class="mono">{{ item.dify_document_id || '-' }}</td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td class="action-col">
              <button class="danger-btn" type="button" :disabled="deletingId === item.id" @click="deleteDocument(item)">
                {{ deletingId === item.id ? $t('adminPages.knowledgeUpload.deleting') : $t('adminPages.knowledgeUpload.delete') }}
              </button>
            </td>
          </tr>

          <tr v-if="!loading && documents.length === 0">
            <td colspan="8"><div class="empty-box">{{ $t('adminPages.knowledgeUpload.empty') }}</div></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'

const { t } = useI18n()
const maxDocuments = ref(2)
const MAX_FILE_SIZE_BYTES = 10 * 1024 * 1024
const loading = ref(false)
const uploading = ref(false)
const deletingId = ref(null)
const selectedFile = ref(null)
const documents = ref([])
const fileInput = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const uploadLimitReached = computed(() => documents.value.length >= maxDocuments.value)

function handleFileChange(event) {
  successMessage.value = ''
  errorMessage.value = ''

  if (uploadLimitReached.value) {
    clearSelectedFile()
    errorMessage.value = t('adminPages.knowledgeUpload.maxFiles', { max: maxDocuments.value })
    return
  }

  const file = event.target.files?.[0] || null

  if (file && isFileTooLarge(file)) {
    clearSelectedFile()
    errorMessage.value = t('adminPages.knowledgeUpload.tooLarge')
    return
  }

  if (file && isDuplicateFile(file)) {
    clearSelectedFile()
    errorMessage.value = t('adminPages.knowledgeUpload.duplicate')
    return
  }

  selectedFile.value = file
}

async function submitUpload() {
  if (!selectedFile.value) return

  if (uploadLimitReached.value) {
    errorMessage.value = t('adminPages.knowledgeUpload.maxFiles', { max: maxDocuments.value })
    return
  }

  if (isFileTooLarge(selectedFile.value)) {
    errorMessage.value = t('adminPages.knowledgeUpload.tooLarge')
    return
  }

  if (isDuplicateFile(selectedFile.value)) {
    errorMessage.value = t('adminPages.knowledgeUpload.duplicate')
    return
  }

  uploading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    const res = await api.post('/knowledge/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } })

    successMessage.value = res.data?.success ? t('adminPages.knowledgeUpload.uploaded') : t('adminPages.knowledgeUpload.uploadComplete')
    clearSelectedFile()
    await fetchDocuments()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.knowledgeUpload.uploadFailed')
  } finally {
    uploading.value = false
  }
}

async function deleteDocument(item) {
  if (!window.confirm(t('adminPages.knowledgeUpload.confirmDelete', { name: item.name }))) return

  deletingId.value = item.id
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await api.delete(`/knowledge/documents/${item.id}`)
    successMessage.value = t('adminPages.knowledgeUpload.deleted')
    await fetchDocuments()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.knowledgeUpload.deleteFailed')
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
    maxDocuments.value = res.data.max_documents || 2
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.knowledgeUpload.loadFailed')
  } finally {
    loading.value = false
  }
}

function clearSelectedFile() {
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
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

function isFileTooLarge(file) {
  return file.size > MAX_FILE_SIZE_BYTES
}

onMounted(fetchDocuments)
</script>

<style scoped>
.page-card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:18px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.page-actions { display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end; }
.upload-card { background:#f8fafc; border-radius:8px; padding:18px; margin-bottom:20px; }
.upload-row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.primary-btn,.ghost-btn,.danger-btn { border:0; border-radius:8px; padding:10px 14px; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
.primary-btn:disabled,.danger-btn:disabled { cursor:not-allowed; opacity:.55; }
.primary-btn { background:#111827; color:#fff; }
.ghost-btn { background:#eef2f7; color:#111827; }
.danger-btn { background:#fee2e2; color:#991b1b; }
.file-info,.limit-info { margin-top:12px; color:#4b5563; font-size:14px; }
.limit-info.reached { color:#dc2626; }
.success-message { margin-top:12px; color:#15803d; }
.error-message { margin-top:12px; color:#dc2626; }
.table-wrap { overflow-x:auto; }
.table { width:100%; border-collapse:collapse; }
.table th,.table td { padding:14px 12px; border-bottom:1px solid #eef2f7; text-align:left; vertical-align:top; }
.table th { font-size:13px; color:#6b7280; }
.action-col { white-space:nowrap; text-align:right; }
.badge { padding:4px 8px; border-radius:999px; font-size:12px; }
.status-success { background:#dcfce7; color:#166534; }
.status-warning { background:#fef3c7; color:#92400e; }
.status-danger { background:#fee2e2; color:#991b1b; }
.status-default { background:#eef2f7; color:#374151; }
.mono { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:12px; }
.empty-box { color:#6b7280; text-align:center; padding:24px; }
</style>
