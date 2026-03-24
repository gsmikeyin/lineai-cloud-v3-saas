<template>
  <div class="page-grid">
    <div class="card">
      <div class="page-head">
        <div>
          <h2>知識命中測試</h2>
          <p>輸入一句客戶訊息，檢查會命中哪一筆 Knowledge。</p>
        </div>
      </div>

      <div class="form-group">
        <label>測試訊息</label>
        <textarea
          v-model="message"
          rows="4"
          placeholder="例如：請問你們幾點營業？"/>
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
      <h3>命中結果</h3>

      <div class="result-box">
        <div class="result-item">
          <label>是否命中</label>
          <div>{{ result.match?.matched ? '是' : '否' }}</div>
        </div>

        <div class="result-item">
          <label>命中分數</label>
          <div>{{ result.match?.score ?? 0 }}</div>
        </div>

        <div class="result-item">
          <label>命中關鍵字</label>
          <div class="chip-list">
            <span
              v-for="kw in result.match?.matched_keywords || []"
              :key="kw"
              class="chip"
            >
              {{ kw }}
            </span>
            <span v-if="!(result.match?.matched_keywords || []).length">-</span>
          </div>
        </div>
      </div>

      <div v-if="result.match?.item" class="matched-card">
        <h4>最佳命中知識</h4>
        <div class="info-row"><strong>標題：</strong>{{ result.match.item.title }}</div>
        <div class="info-row"><strong>類型：</strong>{{ result.match.item.type }}</div>
        <div class="info-row"><strong>排序：</strong>{{ result.match.item.sort_order }}</div>
        <div class="info-row"><strong>問題：</strong>{{ result.match.item.question || '-' }}</div>
        <div class="info-row"><strong>答案：</strong>{{ result.match.item.answer || '-' }}</div>
        <div class="info-row"><strong>補充：</strong>{{ result.match.item.content || '-' }}</div>
      </div>
    </div>

    <div class="card span-2" v-if="result">
      <h3>候選命中清單</h3>

      <div v-if="(result.match?.candidates || []).length === 0" class="empty-box">
        沒有任何候選命中
      </div>

      <div v-else class="candidate-list">
        <div
          v-for="item in result.match?.candidates || []"
          :key="item.id"
          class="candidate-item"
        >
          <div class="candidate-head">
            <div class="candidate-title">{{ item.title }}</div>
            <div class="candidate-score">分數：{{ item.score }}</div>
          </div>

          <div class="candidate-meta">
            <span class="badge">{{ item.type }}</span>
            <span>排序：{{ item.sort_order }}</span>
          </div>

          <div class="chip-list">
            <span
              v-for="kw in item.matched_keywords || []"
              :key="kw"
              class="chip"
            >
              {{ kw }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="card" v-if="result">
      <h3>AI Prompt Rules</h3>
      <div v-if="!(result.prompt_rules || []).length" class="empty-box">無</div>
      <ul v-else class="plain-list">
        <li v-for="(rule, idx) in result.prompt_rules" :key="idx">
          {{ rule }}
        </li>
      </ul>
    </div>

    <div class="card" v-if="result">
      <h3>AI Knowledge Context</h3>
      <div v-if="!(result.knowledge_context || []).length" class="empty-box">無</div>
      <div v-else class="context-list">
        <div
          v-for="(ctx, idx) in result.knowledge_context"
          :key="idx"
          class="context-item"
        >
          <pre>{{ ctx }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../api'

const message = ref('請問你們幾點營業？')
const loading = ref(false)
const result = ref(null)
const errorMessage = ref('')

async function runTest() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/knowledge-items/test-match', {
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
.card h3,
.card h4 {
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
  gap: 12px;
}

.result-item label {
  display: block;
  margin-bottom: 6px;
  font-size: 12px;
  color: #6b7280;
}

.matched-card {
  margin-top: 20px;
  padding: 16px;
  border-radius: 14px;
  background: #f8fafc;
}

.info-row {
  margin-bottom: 10px;
  line-height: 1.6;
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
}

.badge,
.chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 4px 8px;
  font-size: 12px;
}

.badge {
  background: #eef2ff;
  color: #4338ca;
}

.chip {
  background: #dbeafe;
  color: #1d4ed8;
}

.chip-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.context-list {
  display: grid;
  gap: 12px;
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

.plain-list {
  margin: 0;
  padding-left: 18px;
  line-height: 1.8;
}

.empty-box {
  color: #6b7280;
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