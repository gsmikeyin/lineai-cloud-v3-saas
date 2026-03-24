<template>
  <div class="chat-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div>
          <h2>Conversations</h2>
          <p>{{ conversations.length }} 筆對話</p>
        </div>
        <button class="ghost-btn" @click="manualRefresh">刷新</button>
      </div>

      <div class="sidebar-search">
       <input v-model="keyword" placeholder="搜尋名稱 / 電話 / Email" />
      </div>


<div class="filter-tabs">
  <button
    v-for="filter in filterOptions"
    :key="filter.key"
    class="filter-tab"
    :class="{ active: activeFilter === filter.key }"
    @click="activeFilter = filter.key"
  >
    <span>{{ filter.label }}</span>
    <span class="filter-count">
      {{ filterCounts[filter.key] ?? 0 }}
    </span>
  </button>
</div>




      <div class="conversation-list">
        <div
          v-for="item in filteredConversations"
          :key="item.id"
          class="conversation-card"
          :class="{
               active: currentConversation && currentConversation.id === item.id,
               unread: item.unread_count > 0
          }"
          @click="selectConversation(item.id)"
        >
          <div class="avatar">
            <img
              v-if="item.customer?.avatar_url"
              :src="item.customer.avatar_url"
              alt=""
            />
            <span v-else>{{ getInitial(item.customer?.display_name) }}</span>
          </div>

          <div class="conversation-meta">



        <div class="top-row">
            <div class="name-wrap">
               <div class="name">
                    {{ item.customer?.display_name || 'Unknown' }}
               </div>

             <span v-if="item.unread_count > 0" class="unread-badge">
                  {{ item.unread_count > 99 ? '99+' : item.unread_count }}
             </span>
        </div>

        <div class="status-badge" :class="item.mode === 'human' ? 'human' : 'ai'">
           {{ item.mode === 'human' ? '人工' : 'AI' }}
        </div>
    </div>





            <div class="sub-row">
              <span>{{ item.assignedUser?.name || item.assigned_user?.name || '未指派' }}</span>
              <span>{{ formatDate(item.last_message_at) }}</span>
            </div>

             <div class="tag-row">
  <span v-if="(item.unread_count || 0) > 0" class="mini-tag unread-tag">
    未讀 {{ item.unread_count }}
  </span>

  <span v-if="item.customer?.is_vip" class="mini-tag vip-tag">
    VIP
  </span>

  <span v-if="!item.assignedUser && !item.assigned_user" class="mini-tag wait-tag">
    待指派
  </span>

  <span v-if="item.mode === 'human' || item.human_handoff === true" class="mini-tag human-tag">
    人工中
  </span>

  <span v-else class="mini-tag ai-tag">
    AI中
  </span>
</div>


          </div>
        </div>
      </div>
    </aside>

    <main class="chat-panel" v-if="currentConversation">
      <div class="chat-header">
        <div class="chat-header-left">
          <div class="avatar large">
            <img
              v-if="currentConversation.customer?.avatar_url"
              :src="currentConversation.customer.avatar_url"
              alt=""
            />
            <span v-else>{{ getInitial(currentConversation.customer?.display_name) }}</span>
          </div>

          <div>
            <h3>{{ currentConversation.customer?.display_name || 'Unknown' }}</h3>
            <div class="header-sub">
              <span class="mode-pill" :class="currentConversation.mode === 'human' ? 'human' : 'ai'">
                {{ currentConversation.mode === 'human' ? '人工接手中' : 'AI 自動回覆中' }}
              </span>
              <span>
                指派：{{ currentConversation.assignedUser?.name || currentConversation.assigned_user?.name || '未指派' }}
              </span>
            </div>
          </div>
        </div>

        <div class="chat-header-actions">
          <button class="secondary-btn" @click="handoff" :disabled="loadingAction">
            人工接手
          </button>
          <button class="primary-btn" @click="resumeAi" :disabled="loadingAction">
            切回 AI
          </button>
        </div>
      </div>

      <div ref="messagePanelRef" class="message-panel">
        <div
          v-for="msg in currentConversation.messages"
          :key="msg.id"
          class="message-row"
          :class="msg.direction"
        >
          <div class="message-bubble" :class="msg.sender_type">
            <div class="message-role">
              {{ senderLabel(msg.sender_type) }}
            </div>
            <div class="message-content">
              {{ msg.content }}
            </div>
            <div class="message-time">
              {{ formatDate(msg.sent_at || msg.created_at, true) }}
            </div>
          </div>
        </div>
      </div>

      <form class="reply-panel" @submit.prevent="sendReply">
        <textarea
          v-model="replyText"
          rows="3"
          placeholder="輸入人工回覆內容..."
          @focus="isTyping = true"
          @blur="handleTypingBlur"
        />
        <div class="reply-actions">
          <span class="refresh-note">
            {{ autoRefreshLabel }}
          </span>
          <button type="button" class="ghost-btn" @click="manualRefresh">
            重新整理
          </button>
          <button type="submit" class="primary-btn" :disabled="sending">
            {{ sending ? '送出中...' : '送出回覆' }}
          </button>
        </div>
      </form>
    </main>

    <section class="customer-panel" v-if="currentConversation">
      <div class="panel-card">
        <h3>客戶資訊</h3>

        <div class="info-item">
          <label>名稱</label>
          <div>{{ currentConversation.customer?.display_name || '-' }}</div>
        </div>

        <div class="info-item">
          <label>電話</label>
          <div>{{ currentConversation.customer?.phone || '-' }}</div>
        </div>

        <div class="info-item">
          <label>Email</label>
          <div>{{ currentConversation.customer?.email || '-' }}</div>
        </div>

        <div class="info-item">
          <label>VIP</label>
          <div>{{ currentConversation.customer?.is_vip ? '是' : '否' }}</div>
        </div>

        <div class="info-item">
          <label>互動次數</label>
          <div>{{ currentConversation.customer?.total_messages ?? 0 }}</div>
        </div>

        <div class="info-item">
          <label>訂單數</label>
          <div>{{ currentConversation.customer?.total_orders ?? 0 }}</div>
        </div>

        <div class="info-item">
          <label>累計消費</label>
          <div>{{ currentConversation.customer?.total_spent ?? 0 }}</div>
        </div>

        <div class="info-item">
          <label>最後互動</label>
          <div>{{ formatDate(currentConversation.customer?.last_interaction_at) }}</div>
        </div>
      </div>
    </section>

    <main class="chat-panel empty" v-else>
      <div class="empty-box">
        <h3>請先選擇一筆對話</h3>
        <p>左側可查看所有 LINE 對話。</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import api from '../api'

const conversations = ref([])
const currentConversation = ref(null)
const currentConversationId = ref(null)
const replyText = ref('')
const sending = ref(false)
const loadingAction = ref(false)
const keyword = ref('')
const isTyping = ref(false)
const isPolling = ref(false)
const pollTimer = ref(null)
const messagePanelRef = ref(null)

const POLL_INTERVAL = 5000

const activeFilter = ref('all')

const filterOptions = [
  { key: 'all', label: '全部' },
  { key: 'unread', label: '未讀' },
  { key: 'human', label: '人工中' },
  { key: 'ai', label: 'AI中' },
  { key: 'unassigned', label: '待指派' },
  { key: 'vip', label: 'VIP' },
]



const filteredConversations = computed(() => {
  let list = [...conversations.value]

  if (activeFilter.value === 'unread') {
    list = list.filter((item) => (item.unread_count || 0) > 0)
  }

  if (activeFilter.value === 'human') {
    list = list.filter((item) => item.mode === 'human' || item.human_handoff === true)
  }

  if (activeFilter.value === 'ai') {
    list = list.filter((item) => item.mode === 'ai' || item.human_handoff === false)
  }

  if (activeFilter.value === 'unassigned') {
    list = list.filter((item) => !item.assignedUser && !item.assigned_user)
  }

  if (activeFilter.value === 'vip') {
    list = list.filter((item) => item.customer?.is_vip)
  }

  if (keyword.value.trim()) {
    const q = keyword.value.toLowerCase()

    list = list.filter((item) => {
      const name = item.customer?.display_name || ''
      const phone = item.customer?.phone || ''
      const email = item.customer?.email || ''
      return (
        name.toLowerCase().includes(q) ||
        phone.toLowerCase().includes(q) ||
        email.toLowerCase().includes(q)
      )
    })
  }

  return list
})


const filterCounts = computed(() => {
  const items = conversations.value

  return {
    all: items.length,
    unread: items.filter((item) => (item.unread_count || 0) > 0).length,
    human: items.filter((item) => item.mode === 'human' || item.human_handoff === true).length,
    ai: items.filter((item) => item.mode === 'ai' || item.human_handoff === false).length,
    unassigned: items.filter((item) => !item.assignedUser && !item.assigned_user).length,
    vip: items.filter((item) => item.customer?.is_vip).length,
  }
})




const autoRefreshLabel = computed(() => {
  if (sending.value) return '訊息送出中'
  if (loadingAction.value) return '更新中'
  if (isTyping.value) return '輸入中，暫停自動刷新內容'
  if (isPolling.value) return '自動刷新中'
  return '每 5 秒自動刷新'
})

function getInitial(name) {
  if (!name) return '?'
  return name.charAt(0).toUpperCase()
}

function senderLabel(type) {
  if (type === 'customer') return '客戶'
  if (type === 'ai') return 'AI'
  if (type === 'agent') return '客服'
  return type || '系統'
}

function formatDate(value, short = false) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value

  if (short) {
    return d.toLocaleString('zh-TW', {
      hour: '2-digit',
      minute: '2-digit',
      month: '2-digit',
      day: '2-digit',
    })
  }

  return d.toLocaleString('zh-TW')
}

function isNearBottom() {
  const el = messagePanelRef.value
  if (!el) return true
  return el.scrollHeight - el.scrollTop - el.clientHeight < 120
}

async function scrollToBottom(force = false) {
  await nextTick()
  const el = messagePanelRef.value
  if (!el) return

  if (force || isNearBottom()) {
    el.scrollTop = el.scrollHeight
  }
}

async function fetchConversations() {
  const res = await api.get('/conversations')
  conversations.value = res.data.data || []
}

async function loadConversation(id, options = {}) {
  const { preserveScroll = true, forceScrollBottom = false } = options
  const wasNearBottom = preserveScroll ? isNearBottom() : true

  const res = await api.get(`/conversations/${id}`)
  currentConversation.value = res.data
  currentConversationId.value = id

  if (forceScrollBottom || wasNearBottom) {
    await scrollToBottom(true)
  }
}

async function selectConversation(id) {
  currentConversationId.value = id
  await loadConversation(id, { preserveScroll: false, forceScrollBottom: true })
}

async function handoff() {
  if (!currentConversation.value) return
  loadingAction.value = true
  try {
    await api.post(`/conversations/${currentConversation.value.id}/handoff`)
    await refreshCurrentConversation(true)
    await fetchConversations()
  } finally {
    loadingAction.value = false
  }
}

async function resumeAi() {
  if (!currentConversation.value) return
  loadingAction.value = true
  try {
    await api.post(`/conversations/${currentConversation.value.id}/resume-ai`)
    await refreshCurrentConversation(true)
    await fetchConversations()
  } finally {
    loadingAction.value = false
  }
}

async function sendReply() {
  if (!currentConversation.value || !replyText.value.trim()) return

  sending.value = true
  try {
    await api.post(`/conversations/${currentConversation.value.id}/reply`, {
      message: replyText.value,
    })

    replyText.value = ''
    isTyping.value = false

    await refreshCurrentConversation(true)
    await fetchConversations()
  } finally {
    sending.value = false
  }
}

async function refreshCurrentConversation(forceScrollBottom = false) {
  if (!currentConversationId.value) return
  await loadConversation(currentConversationId.value, {
    preserveScroll: !forceScrollBottom,
    forceScrollBottom,
  })
}

async function manualRefresh() {
  await fetchConversations()
  await refreshCurrentConversation(false)
}

function handleTypingBlur() {
  setTimeout(() => {
    if (!replyText.value.trim()) {
      isTyping.value = false
    }
  }, 150)
}

async function poll() {
  if (sending.value || loadingAction.value) return

  isPolling.value = true
  try {
    await fetchConversations()

    if (currentConversationId.value && !isTyping.value) {
      await refreshCurrentConversation(false)
    }
  } finally {
    isPolling.value = false
  }
}

function startPolling() {
  stopPolling()
  pollTimer.value = window.setInterval(() => {
    poll()
  }, POLL_INTERVAL)
}

function stopPolling() {
  if (pollTimer.value) {
    clearInterval(pollTimer.value)
    pollTimer.value = null
  }
}

function handleVisibilityChange() {
  if (document.hidden) {
    stopPolling()
  } else {
    poll()
    startPolling()
  }
}

onMounted(async () => {
  await fetchConversations()

  if (conversations.value.length > 0) {
    await selectConversation(conversations.value[0].id)
  }

  startPolling()
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onBeforeUnmount(() => {
  stopPolling()
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>

<style scoped>

.filter-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 12px 20px 4px;
  border-bottom: 1px solid #eef2f7;
}

.filter-tab {
  border: 0;
  background: #eef2f7;
  color: #111827;
  padding: 8px 10px;
  border-radius: 999px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}

.filter-tab.active {
  background: #111827;
  color: #fff;
}

.filter-count {
  background: rgba(255, 255, 255, 0.25);
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  font-size: 11px;
}

.filter-tab:not(.active) .filter-count {
  background: #dbe4ee;
}

.tag-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
}

.mini-tag {
  font-size: 11px;
  padding: 3px 7px;
  border-radius: 999px;
  white-space: nowrap;
}

.unread-tag {
  background: #fee2e2;
  color: #b91c1c;
}

.vip-tag {
  background: #fef3c7;
  color: #92400e;
}

.wait-tag {
  background: #ede9fe;
  color: #6d28d9;
}

.human-tag {
  background: #ffedd5;
  color: #c2410c;
}

.ai-tag {
  background: #dbeafe;
  color: #1d4ed8;
}



.name-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.unread-badge {
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.conversation-card.unread {
  border-color: #fecaca;
  background: #fff7f7;
}


.chat-layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 320px 1fr 320px;
  background: #f3f6fb;
}

.sidebar {
  background: #ffffff;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #eef2f7;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sidebar-header h2 {
  margin: 0 0 6px;
  font-size: 20px;
}

.sidebar-header p {
  margin: 0;
  color: #6b7280;
  font-size: 13px;
}

.sidebar-search {
  padding: 14px 20px;
  border-bottom: 1px solid #eef2f7;
}

.sidebar-search input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d8dee9;
  border-radius: 10px;
  padding: 10px 12px;
  background: #f9fbfd;
}

.conversation-list {
  overflow-y: auto;
  padding: 14px;
}

.conversation-card {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-radius: 14px;
  cursor: pointer;
  border: 1px solid transparent;
  margin-bottom: 10px;
  background: #fff;
}

.conversation-card:hover {
  background: #f8fafc;
}

.conversation-card.active {
  border-color: #111827;
  background: #f8fafc;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  background: #111827;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  font-weight: 700;
  flex-shrink: 0;
}

.avatar.large {
  width: 52px;
  height: 52px;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.conversation-meta {
  flex: 1;
  min-width: 0;
}

.top-row,
.sub-row {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.name {
  font-weight: 700;
  color: #111827;
}

.sub-row {
  margin-top: 8px;
  font-size: 12px;
  color: #6b7280;
}

.status-badge,
.mode-pill {
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
  white-space: nowrap;
}

.status-badge.ai,
.mode-pill.ai {
  background: #e0f2fe;
  color: #0369a1;
}

.status-badge.human,
.mode-pill.human {
  background: #fef3c7;
  color: #92400e;
}

.chat-panel {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.chat-header {
  padding: 20px 24px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-header-left {
  display: flex;
  gap: 14px;
  align-items: center;
}

.chat-header-left h3 {
  margin: 0 0 8px;
}

.header-sub {
  display: flex;
  gap: 10px;
  align-items: center;
  color: #6b7280;
  font-size: 13px;
  flex-wrap: wrap;
}

.chat-header-actions {
  display: flex;
  gap: 10px;
}

.message-panel {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.message-row {
  display: flex;
  margin-bottom: 14px;
}

.message-row.inbound {
  justify-content: flex-start;
}

.message-row.outbound {
  justify-content: flex-end;
}

.message-bubble {
  max-width: 72%;
  border-radius: 18px;
  padding: 12px 14px;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
}

.message-bubble.customer {
  background: #ffffff;
}

.message-bubble.ai {
  background: #eef2ff;
}

.message-bubble.agent {
  background: #dcfce7;
}

.message-role {
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 6px;
  color: #374151;
}

.message-content {
  white-space: pre-wrap;
  line-height: 1.6;
  color: #111827;
}

.message-time {
  margin-top: 8px;
  font-size: 11px;
  color: #6b7280;
}

.reply-panel {
  background: #fff;
  border-top: 1px solid #e5e7eb;
  padding: 18px 24px;
}

.reply-panel textarea {
  width: 100%;
  resize: vertical;
  box-sizing: border-box;
  border: 1px solid #d8dee9;
  border-radius: 14px;
  padding: 14px;
  font-size: 14px;
  min-height: 96px;
}

.reply-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 12px;
  align-items: center;
}

.refresh-note {
  margin-right: auto;
  color: #6b7280;
  font-size: 12px;
}

.customer-panel {
  border-left: 1px solid #e5e7eb;
  background: #fff;
  padding: 20px;
}

.panel-card {
  background: #fff;
}

.panel-card h3 {
  margin-top: 0;
  margin-bottom: 18px;
}

.info-item {
  margin-bottom: 16px;
}

.info-item label {
  display: block;
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 6px;
}

.primary-btn,
.secondary-btn,
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

.secondary-btn {
  background: #f59e0b;
  color: #fff;
}

.ghost-btn {
  background: #eef2f7;
  color: #111827;
}

.primary-btn:disabled,
.secondary-btn:disabled,
.ghost-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.empty {
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-box {
  text-align: center;
  color: #6b7280;
}

@media (max-width: 1280px) {
  .chat-layout {
    grid-template-columns: 280px 1fr;
  }

  .customer-panel {
    display: none;
  }
}

@media (max-width: 900px) {
  .chat-layout {
    grid-template-columns: 1fr;
  }

  .sidebar {
    display: none;
  }
}
</style>