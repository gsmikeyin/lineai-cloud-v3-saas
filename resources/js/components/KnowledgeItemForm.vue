<template>
  <div class="form-shell">
    <div class="form-grid">
      <div class="form-group">
        <label>類型</label>
        <select v-model="localForm.type">
          <option value="faq">FAQ</option>
          <option value="product">產品</option>
          <option value="policy">政策</option>
          <option value="prompt">提示</option>
        </select>
      </div>

      <div class="form-group">
        <label>狀態</label>
        <select v-model="localForm.status">
          <option value="draft">草稿</option>
          <option value="published">發佈</option>
          <option value="archived">存檔</option>
        </select>
      </div>

      <div class="form-group full">
        <label>標題</label>
        <input v-model="localForm.title" type="text" placeholder="例如：營業時間" />
      </div>

      <div class="form-group full">
        <label>問題</label>
        <textarea
          v-model="localForm.question"
          rows="3"
          placeholder="例如：請問你們幾點營業？"
        />
      </div>

      <div class="form-group full">
        <label>答案</label>
        <textarea
          v-model="localForm.answer"
          rows="5"
          placeholder="例如：您好，我們的營業時間為週一到週五 09:00–18:00。"
        />
      </div>

      <div class="form-group full">
        <label>補充內容</label>
        <textarea
          v-model="localForm.content"
          rows="4"
          placeholder="補充說明、例外條件、其他備註"
        />
      </div>

      <div class="form-group">
        <label>排序</label>
        <input v-model.number="localForm.sort_order" type="number" min="0" />
      </div>

      <div class="form-group switch-group">
        <label>提供給 AI 使用</label>
        <input v-model="localForm.is_ai_enabled" type="checkbox" />
      </div>

      <div class="form-group full">
        <label>關鍵字</label>
        <input
          v-model="keywordsInput"
          type="text"
          placeholder="以逗號分隔，例如：營業時間, 上班, 幾點, 開門"
        />
        <div class="hint">
          系統會把逗號分隔內容轉成關鍵字陣列。
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button class="ghost-btn" type="button" @click="$emit('cancel')">
        取消
      </button>
      <button class="primary-btn" type="button" @click="submitForm">
        {{ submitText }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, watch, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  submitText: {
    type: String,
    default: '儲存',
  },
})

const emit = defineEmits(['submit', 'cancel'])

const localForm = reactive({
  type: 'faq',
  title: '',
  question: '',
  answer: '',
  content: '',
  status: 'draft',
  sort_order: 0,
  is_ai_enabled: true,
  keywords: [],
})

const keywordsInput = ref('')

watch(
  () => props.modelValue,
  (value) => {
    localForm.type = value?.type || 'faq'
    localForm.title = value?.title || ''
    localForm.question = value?.question || ''
    localForm.answer = value?.answer || ''
    localForm.content = value?.content || ''
    localForm.status = value?.status || 'draft'
    localForm.sort_order = value?.sort_order ?? 0
    localForm.is_ai_enabled =
      typeof value?.is_ai_enabled === 'boolean' ? value.is_ai_enabled : true
    localForm.keywords = Array.isArray(value?.keywords) ? value.keywords : []
    keywordsInput.value = localForm.keywords.join(', ')
  },
  { immediate: true, deep: true }
)

function submitForm() {
  const keywords = keywordsInput.value
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean)

  emit('submit', {
    type: localForm.type,
    title: localForm.title,
    question: localForm.question,
    answer: localForm.answer,
    content: localForm.content,
    status: localForm.status,
    sort_order: Number(localForm.sort_order || 0),
    is_ai_enabled: !!localForm.is_ai_enabled,
    keywords,
  })
}
</script>

<style scoped>
.form-shell {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full {
  grid-column: span 2;
}

.form-group label {
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
  background: #fff;
}

.switch-group {
  justify-content: flex-end;
}

.switch-group input[type='checkbox'] {
  width: 18px;
  height: 18px;
  align-self: flex-start;
  margin-top: 8px;
}

.hint {
  margin-top: 8px;
  font-size: 12px;
  color: #6b7280;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.primary-btn,
.ghost-btn {
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.full {
    grid-column: span 1;
  }
}
</style>