<template>
  <div class="page-grid">
    <div class="card">
      <div class="page-head">
        <div>
          <h2>{{ $t('adminPages.knowledgeTest.title') }}</h2>
          <p>{{ $t('adminPages.knowledgeTest.desc') }}</p>
        </div>
      </div>

      <div class="form-group">
        <label>{{ $t('adminPages.knowledgeTest.message') }}</label>
        <textarea v-model="message" rows="4" :placeholder="$t('adminPages.knowledgeTest.placeholder')" />
      </div>

      <div class="action-row">
        <button class="primary-btn" :disabled="loading" @click="runTest">
          {{ loading ? $t('adminPages.knowledgeTest.running') : $t('adminPages.knowledgeTest.run') }}
        </button>
      </div>

      <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
    </div>

    <div class="card" v-if="result">
      <div class="result-head">
        <h3>{{ $t('adminPages.knowledgeTest.answerTitle') }}</h3>
        <span v-if="result.tested_at" class="last-badge">Last test: {{ formatDate(result.tested_at) }}</span>
      </div>

      <div class="result-box">
        <div class="result-item" v-if="result.message">
          <label>{{ $t('adminPages.knowledgeTest.message') }}</label>
          <div class="answer-box">{{ result.message }}</div>
        </div>

        <div class="result-item">
          <label>Conversation ID</label>
          <div>{{ result.conversation_id || '-' }}</div>
        </div>

        <div class="result-item" v-if="result.tested_by">
          <label>Tested By</label>
          <div>{{ result.tested_by.name || result.tested_by.email || '-' }}</div>
        </div>

        <div class="result-item">
          <label>{{ $t('adminPages.knowledgeTest.answer') }}</label>
          <div v-if="result.answer" class="answer-box markdown-body" v-html="formattedAnswer"></div>
          <div v-else class="answer-box">-</div>
        </div>
      </div>
    </div>

    <div class="card span-2" v-if="retrieverResources.length">
      <h3>{{ $t('adminPages.knowledgeTest.resources') }}</h3>

      <div class="candidate-list">
        <div v-for="(item, idx) in retrieverResources" :key="idx" class="candidate-item">
          <div class="candidate-head">
            <div class="candidate-title">
              {{ item.document_name || item.segment_name || `Resource #${idx + 1}` }}
            </div>
            <div class="candidate-score">
              {{ t('adminPages.knowledgeTest.score', { score: item.score ?? '-' }) }}
            </div>
          </div>

          <div class="candidate-meta">
            <span class="badge">{{ item.dataset_name || '智識庫' }}</span>
            <span>{{ item.document_id || '-' }}</span>
          </div>

          <div class="context-item">
            <pre>{{ item.content || item.segment || JSON.stringify(item, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>

    <div class="card span-2" v-if="result && isSuperAdmin()">
      <h3>{{ $t('adminPages.knowledgeTest.raw') }}</h3>
      <div class="context-item">
        <pre>{{ prettyRaw }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'
import { isSuperAdmin } from '../utils/auth'
import { renderMarkdown } from '../utils/markdown'

const { t } = useI18n()
const message = ref('')
const loading = ref(false)
const result = ref(null)
const errorMessage = ref('')
const loadingLast = ref(false)

const retrieverResources = computed(() => result.value?.raw?.metadata?.retriever_resources || [])

const formattedAnswer = computed(() => renderMarkdown(result.value?.answer || ''))

const prettyRaw = computed(() => {
  if (!result.value?.raw) return ''
  return JSON.stringify(result.value.raw, null, 2)
})

async function runTest() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/dify/test', { message: message.value })
    result.value = res.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.knowledgeTest.failed')
  } finally {
    loading.value = false
  }
}

async function fetchLastTest() {
  loadingLast.value = true

  try {
    const res = await api.get('/dify/test/last')
    if (res.data?.data) {
      result.value = res.data.data
      message.value = res.data.data.message || ''
    }
  } catch (error) {
  } finally {
    loadingLast.value = false
  }
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleString()
}

onMounted(fetchLastTest)
</script>

<style scoped>
.page-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
.span-2 { grid-column:span 2; }
.card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head h2,.card h3 { margin-top:0; }
.result-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:14px; }
.result-head h3 { margin-bottom:0; }
.last-badge { color:#6b7280; font-size:12px; background:#eef2f7; border-radius:999px; padding:6px 10px; white-space:nowrap; }
.page-head p { margin:6px 0 0; color:#6b7280; }
.form-group label { display:block; margin-bottom:8px; font-size:14px; font-weight:600; }
.form-group textarea { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:8px; padding:12px 14px; font-size:14px; }
.action-row { margin-top:16px; }
.primary-btn { border:0; border-radius:8px; padding:10px 14px; cursor:pointer; font-size:14px; background:#111827; color:#fff; }
.primary-btn:disabled { opacity:.6; cursor:not-allowed; }
.error-message { margin-top:12px; color:#dc2626; font-size:14px; }
.result-box { display:grid; gap:14px; }
.result-item label { display:block; margin-bottom:6px; font-size:12px; color:#6b7280; }
.answer-box { background:#f8fafc; border-radius:8px; padding:14px; line-height:1.8; white-space:pre-wrap; }
.markdown-body { white-space:normal; color:#111827; overflow-wrap:anywhere; }
.markdown-body :deep(p) { margin:0 0 10px; }
.markdown-body :deep(p:last-child) { margin-bottom:0; }
.markdown-body :deep(h3),
.markdown-body :deep(h4),
.markdown-body :deep(h5) { margin:14px 0 8px; line-height:1.35; font-size:15px; color:#111827; }
.markdown-body :deep(h3:first-child),
.markdown-body :deep(h4:first-child),
.markdown-body :deep(h5:first-child) { margin-top:0; }
.markdown-body :deep(ul),
.markdown-body :deep(ol) { margin:8px 0 12px; padding-left:20px; }
.markdown-body :deep(li) { margin:4px 0; }
.markdown-body :deep(strong) { font-weight:700; }
.markdown-body :deep(em) { font-style:italic; }
.markdown-body :deep(a) { color:#2563eb; text-decoration:underline; text-underline-offset:2px; }
.markdown-body :deep(code) { border-radius:6px; background:#e5e7eb; padding:2px 5px; font-family:ui-monospace,SFMono-Regular,Consolas,monospace; font-size:12px; }
.markdown-body :deep(pre) { margin:10px 0; overflow:auto; border-radius:8px; background:#111827; color:#f9fafb; padding:12px; }
.markdown-body :deep(pre code) { display:block; background:transparent; color:inherit; padding:0; white-space:pre; }
.candidate-list { display:grid; gap:12px; }
.candidate-item { border:1px solid #eef2f7; border-radius:8px; padding:14px; }
.candidate-head { display:flex; justify-content:space-between; gap:10px; margin-bottom:8px; }
.candidate-title { font-weight:700; }
.candidate-score { color:#2563eb; font-weight:600; }
.candidate-meta { display:flex; gap:10px; align-items:center; color:#6b7280; font-size:13px; margin-bottom:10px; flex-wrap:wrap; }
.badge { display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:4px 8px; font-size:12px; background:#eef2ff; color:#4338ca; }
.context-item { background:#f8fafc; border-radius:8px; padding:12px; }
.context-item pre { margin:0; white-space:pre-wrap; word-break:break-word; font-family:inherit; font-size:13px; line-height:1.6; }
@media (max-width:900px) { .page-grid { grid-template-columns:1fr; } .span-2 { grid-column:span 1; } }
</style>
