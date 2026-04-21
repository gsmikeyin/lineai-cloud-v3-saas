<template>
  <div class="page-grid">
    <div class="card">
      <div class="page-head">
        <div>
          <h2>Dify 知識命中測試</h2>
          <p>輸入一句客戶訊息，直接測試 Dify Retrieval + LLM 回答。</p>
        </div>
      </div>

      <div class="form-group">
        <label>測試訊息</label>
        <textarea
          v-model="message"
          rows="4"
          placeholder="例如：請問你們幾點營業？"
        />
      </div>

      <div class="action-row">
        <button class="primary-btn" :disabled="loading" @click="runTest">
          {{ loading ? '測試中...' : '開始測試' }}
        </button>
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
    </div>

    <div class="card" v-if="result">
      <h3>Dify 回答</h3>

      <div class="result-box">
        <div class="result-item">
          <label>Conversation ID</label>
          <div>{{ result.conversation_id || '-' }}</div>
        </div>

        <div class="result-item">
          <label>Answer</label>
          <div class="answer-box">{{ result.answer || '-' }}</div>
        </div>
      </div>
    </div>

    <div class="card span-2" v-if="retrieverResources.length">
      <h3>Retriever Resources</h3>

      <div class="candidate-list">
        <div
          v-for="(item, idx) in retrieverResources"
          :key="idx"
          class="candidate-item"
        >
          <div class="candidate-head">
            <div class="candidate-title">
              {{ item.document_name || item.segment_name || `Resource #${idx + 1}` }}
            </div>
            <div class="candidate-score">
              分數：{{ item.score ?? '-' }}
            </div>
          </div>

          <div class="candidate-meta">
            <span class="badge">{{ item.dataset_name || 'dataset' }}</span>
            <span>{{ item.document_id || '-' }}</span>
          </div>

          <div class="context-item">
            <pre>{{ item.content || item.segment || JSON.stringify(item, null, 2) }}</pre>
          </div>
        </div>
      </div>
    </div>

    <div class="card span-2" v-if="result">
      <h3>Raw Response</h3>
      <div class="context-item">
        <pre>{{ prettyRaw }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import api from '../api'

const message = ref('請問你們幾點營業？')
const loading = ref(false)
const result = ref(null)
const errorMessage = ref('')

const retrieverResources = computed(() => {
  return result.value?.raw?.metadata?.retriever_resources || []
})

const prettyRaw = computed(() => {
  if (!result.value?.raw) return ''
  return JSON.stringify(result.value.raw, null, 2)
})

async function runTest() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/dify/test', {
      message: message.value,
    })
    result.value = res.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '測試失敗，請稍後再試'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.page-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}
.span-2 {
  grid-column: span 2;
}
.card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}
.page-head h2,
.card h3 {
  margin-top: 0;
}
.page-head p {
  margin: 6px 0 0;
  color: #6b7280;
}
.form-group label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
}
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
}
.action-row {
  margin-top: 16px;
}
.primary-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
  background: #111827;
  color: #fff;
}
.primary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.error-message {
  margin-top: 12px;
  color: #dc2626;
  font-size: 14px;
}
.result-box {
  display: grid;
  gap: 14px;
}
.result-item label {
  display: block;
  margin-bottom: 6px;
  font-size: 12px;
  color: #6b7280;
}
.answer-box {
  background: #f8fafc;
  border-radius: 12px;
  padding: 14px;
  line-height: 1.8;
  white-space: pre-wrap;
}
.candidate-list {
  display: grid;
  gap: 12px;
}
.candidate-item {
  border: 1px solid #eef2f7;
  border-radius: 14px;
  padding: 14px;
}
.candidate-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 8px;
}
.candidate-title {
  font-weight: 700;
}
.candidate-score {
  color: #2563eb;
  font-weight: 600;
}
.candidate-meta {
  display: flex;
  gap: 10px;
  align-items: center;
  color: #6b7280;
  font-size: 13px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}
.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 4px 8px;
  font-size: 12px;
  background: #eef2ff;
  color: #4338ca;
}
.context-item {
  background: #f8fafc;
  border-radius: 14px;
  padding: 12px;
}
.context-item pre {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-word;
  font-family: inherit;
  font-size: 13px;
  line-height: 1.6;
}
@media (max-width: 900px) {
  .page-grid {
    grid-template-columns: 1fr;
  }
  .span-2 {
    grid-column: span 1;
  }
}
</style>