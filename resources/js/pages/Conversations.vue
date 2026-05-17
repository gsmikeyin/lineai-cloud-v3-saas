<template>
  <div class="chat-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div>
          <h2>{{ $t('adminPages.conversations.title') }}</h2>
          <p>{{ t('adminPages.conversations.count', { count: conversations.length }) }}</p>
        </div>
        <button class="ghost-btn" type="button" :disabled="refreshing" @click="manualRefresh">
          {{ refreshing ? $t('adminPages.conversations.refreshing') : $t('adminPages.conversations.refresh') }}
        </button>
      </div>

      <div class="sidebar-search">
        <input v-model="keyword" :placeholder="$t('adminPages.conversations.searchPlaceholder')" />
      </div>

      <div class="filter-tabs">
        <button
          v-for="filter in filterOptions"
          :key="filter.key"
          class="filter-tab"
          :class="{ active: activeFilter === filter.key }"
          type="button"
          @click="activeFilter = filter.key"
        >
          <span>{{ filter.label }}</span>
          <span class="filter-count">{{ filterCounts[filter.key] ?? 0 }}</span>
        </button>
      </div>

      <div class="conversation-list">
        <button
          v-for="item in filteredConversations"
          :key="item.id"
          class="conversation-card"
          :class="{ active: currentConversationId === item.id, unread: item.unread_count > 0 }"
          type="button"
          @click="selectConversation(item.id)"
        >
          <div class="avatar">
            <img v-if="item.customer?.avatar_url" :src="item.customer.avatar_url" alt="" />
            <span v-else>{{ getInitial(item.customer?.display_name) }}</span>
          </div>

          <div class="conversation-meta">
            <div class="top-row">
              <div class="name-wrap">
                <div class="name">{{ item.customer?.display_name || $t('adminPages.conversations.unknown') }}</div>
                <span v-if="item.unread_count > 0" class="unread-badge">
                  {{ item.unread_count > 99 ? '99+' : item.unread_count }}
                </span>
              </div>
              <span class="status-badge" :class="isHumanMode(item) ? 'human' : 'ai'">
                {{ isHumanMode(item) ? $t('adminPages.conversations.human') : 'AI' }}
              </span>
            </div>

            <div class="sub-row">
              <span>{{ item.assignedUser?.name || item.assigned_user?.name || $t('adminPages.conversations.unassigned') }}</span>
              <span>{{ formatDate(item.last_message_at) }}</span>
            </div>

            <div class="tag-row">
              <span v-if="(item.unread_count || 0) > 0" class="mini-tag unread-tag">
                {{ t('adminPages.conversations.unread', { count: item.unread_count }) }}
              </span>
              <span v-if="!item.assignedUser && !item.assigned_user" class="mini-tag wait-tag">
                {{ $t('adminPages.conversations.unassigned') }}
              </span>
              <span class="mini-tag" :class="isHumanMode(item) ? 'human-tag' : 'ai-tag'">
                {{ isHumanMode(item) ? $t('adminPages.conversations.humanActive') : $t('adminPages.conversations.aiActive') }}
              </span>
            </div>
          </div>
        </button>
      </div>
    </aside>

    <main class="chat-panel" v-if="currentConversation">
      <div class="chat-header">
        <div class="chat-header-left">
          <div class="avatar large">
            <img v-if="currentConversation.customer?.avatar_url" :src="currentConversation.customer.avatar_url" alt="" />
            <span v-else>{{ getInitial(currentConversation.customer?.display_name) }}</span>
          </div>

          <div>
            <h3>{{ currentConversation.customer?.display_name || $t('adminPages.conversations.unknown') }}</h3>
            <div class="header-sub">
              <span class="mode-pill" :class="isHumanMode(currentConversation) ? 'human' : 'ai'">
                {{ isHumanMode(currentConversation) ? $t('adminPages.conversations.humanMode') : $t('adminPages.conversations.aiMode') }}
              </span>
              <span>
                {{ t('adminPages.conversations.assignedTo', { name: currentConversation.assignedUser?.name || currentConversation.assigned_user?.name || $t('adminPages.conversations.unassigned') }) }}
              </span>
            </div>
          </div>
        </div>

        <div class="chat-header-actions">
          <button class="secondary-btn" type="button" :disabled="loadingAction" @click="handoff">
            {{ $t('adminPages.conversations.handoff') }}
          </button>
          <button class="primary-btn" type="button" :disabled="loadingAction" @click="resumeAi">
            {{ $t('adminPages.conversations.resumeAi') }}
          </button>
        </div>
      </div>

      <div v-if="errorMessage" class="error-banner">{{ errorMessage }}</div>

      <div ref="messagePanelRef" class="message-panel">
        <div v-for="msg in currentConversation.messages" :key="msg.id" class="message-row" :class="msg.direction">
          <div class="message-bubble" :class="msg.sender_type">
            <div class="message-role">{{ senderLabel(msg.sender_type) }}</div>
            <div v-if="shouldRenderMarkdown(msg)" class="message-content markdown-body" v-html="renderMarkdown(msg.content)"></div>
            <div v-else class="message-content">{{ msg.content }}</div>
            <div class="message-time">{{ formatDate(msg.sent_at || msg.created_at, true) }}</div>
          </div>
        </div>
      </div>

      <form class="reply-panel" @submit.prevent="sendReply">
        <textarea
          v-model="replyText"
          rows="3"
          :placeholder="$t('adminPages.conversations.replyPlaceholder')"
          @focus="isTyping = true"
          @blur="handleTypingBlur"
        />
        <div class="reply-actions">
          <span class="refresh-note">{{ autoRefreshLabel }}</span>
          <button type="button" class="ghost-btn" :disabled="refreshing" @click="manualRefresh">
            {{ refreshing ? $t('adminPages.conversations.refreshing') : $t('adminPages.conversations.refresh') }}
          </button>
          <button type="submit" class="primary-btn" :disabled="sending || !replyText.trim()">
            {{ sending ? $t('adminPages.conversations.sending') : $t('adminPages.conversations.send') }}
          </button>
        </div>
      </form>
    </main>

    <section class="customer-panel" v-if="currentConversation">
      <div class="panel-card">
        <h3>{{ $t('adminPages.conversations.customerInfo') }}</h3>

        <div class="info-item">
          <label>{{ $t('adminPages.conversations.name') }}</label>
          <div>{{ currentConversation.customer?.display_name || '-' }}</div>
        </div>
        <div class="info-item">
          <label>{{ $t('adminPages.conversations.phone') }}</label>
          <div>{{ currentConversation.customer?.phone || '-' }}</div>
        </div>
        <div class="info-item">
          <label>{{ $t('adminPages.conversations.email') }}</label>
          <div>{{ currentConversation.customer?.email || '-' }}</div>
        </div>
        <div class="info-item">
          <label>{{ $t('adminPages.conversations.totalMessages') }}</label>
          <div>{{ currentConversation.customer?.total_messages ?? 0 }}</div>
        </div>
        <div class="info-item">
          <label>{{ $t('adminPages.conversations.lastInteraction') }}</label>
          <div>{{ formatDate(currentConversation.customer?.last_interaction_at) }}</div>
        </div>
      </div>
    </section>

    <main class="chat-panel empty" v-else>
      <div class="empty-box">
        <h3>{{ $t('adminPages.conversations.emptyTitle') }}</h3>
        <p>{{ $t('adminPages.conversations.emptyDesc') }}</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'
import { renderMarkdown } from '../utils/markdown'

const { t } = useI18n()
const conversations = ref([])
const currentConversation = ref(null)
const currentConversationId = ref(null)
const replyText = ref('')
const sending = ref(false)
const loadingAction = ref(false)
const refreshing = ref(false)
const keyword = ref('')
const isTyping = ref(false)
const isPolling = ref(false)
const pollTimer = ref(null)
const messagePanelRef = ref(null)
const errorMessage = ref('')

const POLL_INTERVAL = 5000
const activeFilter = ref('all')
const filterOptions = computed(() => [
  { key: 'all', label: t('adminPages.conversations.filters.all') },
  { key: 'unread', label: t('adminPages.conversations.filters.unread') },
  { key: 'human', label: t('adminPages.conversations.filters.human') },
  { key: 'ai', label: t('adminPages.conversations.filters.ai') },
  { key: 'unassigned', label: t('adminPages.conversations.filters.unassigned') },
])

const filteredConversations = computed(() => {
  let list = [...conversations.value]
  if (activeFilter.value === 'unread') list = list.filter((item) => (item.unread_count || 0) > 0)
  if (activeFilter.value === 'human') list = list.filter(isHumanMode)
  if (activeFilter.value === 'ai') list = list.filter((item) => !isHumanMode(item))
  if (activeFilter.value === 'unassigned') list = list.filter((item) => !item.assignedUser && !item.assigned_user)

  if (keyword.value.trim()) {
    const q = keyword.value.toLowerCase()
    list = list.filter((item) => {
      const name = item.customer?.display_name || ''
      const phone = item.customer?.phone || ''
      const email = item.customer?.email || ''
      return name.toLowerCase().includes(q) || phone.toLowerCase().includes(q) || email.toLowerCase().includes(q)
    })
  }
  return list
})

const filterCounts = computed(() => {
  const items = conversations.value
  return {
    all: items.length,
    unread: items.filter((item) => (item.unread_count || 0) > 0).length,
    human: items.filter(isHumanMode).length,
    ai: items.filter((item) => !isHumanMode(item)).length,
    unassigned: items.filter((item) => !item.assignedUser && !item.assigned_user).length,
  }
})

const autoRefreshLabel = computed(() => {
  if (sending.value) return t('adminPages.conversations.autoSending')
  if (loadingAction.value) return t('adminPages.conversations.autoUpdating')
  if (isTyping.value) return t('adminPages.conversations.autoTyping')
  if (isPolling.value) return t('adminPages.conversations.autoPolling')
  return t('adminPages.conversations.autoIdle')
})

function isHumanMode(item) {
  return item?.human_handoff === true || item?.mode === 'human'
}

function getInitial(name) {
  if (!name) return '?'
  return name.charAt(0).toUpperCase()
}

function senderLabel(type) {
  if (type === 'customer') return t('adminPages.conversations.senderCustomer')
  if (type === 'ai') return t('adminPages.conversations.senderAi')
  if (type === 'agent') return t('adminPages.conversations.senderAgent')
  return type || t('adminPages.conversations.senderSystem')
}

function shouldRenderMarkdown(message) {
  return message?.direction === 'outbound' || ['ai', 'agent'].includes(message?.sender_type)
}

function formatDate(value, short = false) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  if (short) {
    return d.toLocaleString('zh-TW', { hour: '2-digit', minute: '2-digit', month: '2-digit', day: '2-digit' })
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
  if (force || isNearBottom()) el.scrollTop = el.scrollHeight
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
  if (forceScrollBottom || wasNearBottom) await scrollToBottom(true)
}

async function selectConversation(id) {
  errorMessage.value = ''
  await loadConversation(id, { preserveScroll: false, forceScrollBottom: true })
}

async function handoff() {
  if (!currentConversation.value) return
  loadingAction.value = true
  errorMessage.value = ''
  try {
    await api.post(`/conversations/${currentConversation.value.id}/handoff`)
    await refreshCurrentConversation(true)
    await fetchConversations()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.conversations.handoffFailed')
  } finally {
    loadingAction.value = false
  }
}

async function resumeAi() {
  if (!currentConversation.value) return
  loadingAction.value = true
  errorMessage.value = ''
  try {
    await api.post(`/conversations/${currentConversation.value.id}/resume-ai`)
    await refreshCurrentConversation(true)
    await fetchConversations()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.conversations.resumeAiFailed')
  } finally {
    loadingAction.value = false
  }
}

async function sendReply() {
  if (!currentConversation.value || !replyText.value.trim()) return
  sending.value = true
  errorMessage.value = ''
  try {
    await api.post(`/conversations/${currentConversation.value.id}/reply`, { message: replyText.value.trim() })
    replyText.value = ''
    isTyping.value = false
    await refreshCurrentConversation(true)
    await fetchConversations()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.conversations.replyFailed')
  } finally {
    sending.value = false
  }
}

async function refreshCurrentConversation(forceScrollBottom = false) {
  if (!currentConversationId.value) return
  await loadConversation(currentConversationId.value, { preserveScroll: !forceScrollBottom, forceScrollBottom })
}

async function manualRefresh() {
  refreshing.value = true
  errorMessage.value = ''
  try {
    await fetchConversations()
    await refreshCurrentConversation(false)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.conversations.refreshFailed')
  } finally {
    refreshing.value = false
  }
}

function handleTypingBlur() {
  setTimeout(() => {
    if (!replyText.value.trim()) isTyping.value = false
  }, 150)
}

async function poll() {
  if (sending.value || loadingAction.value || refreshing.value) return
  isPolling.value = true
  try {
    await fetchConversations()
    if (currentConversationId.value && !isTyping.value) await refreshCurrentConversation(false)
  } catch {
  } finally {
    isPolling.value = false
  }
}

function startPolling() {
  stopPolling()
  pollTimer.value = window.setInterval(() => { poll() }, POLL_INTERVAL)
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
  try {
    await fetchConversations()
    if (conversations.value.length > 0) await selectConversation(conversations.value[0].id)
    startPolling()
    document.addEventListener('visibilitychange', handleVisibilityChange)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || t('adminPages.conversations.loadFailed')
  }
})

onBeforeUnmount(() => {
  stopPolling()
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>

<style scoped>
.chat-layout { height:calc(100vh - 136px); min-height:560px; display:grid; grid-template-columns:320px 1fr 320px; background:#f3f6fb; overflow:hidden; }
.sidebar { min-height:0; background:#fff; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; overflow:hidden; }
.sidebar-header { padding:20px; border-bottom:1px solid #eef2f7; display:flex; align-items:center; justify-content:space-between; gap:12px; }
.sidebar-header h2 { margin:0 0 6px; font-size:20px; }
.sidebar-header p { margin:0; color:#6b7280; font-size:13px; }
.sidebar-search { padding:14px 20px; border-bottom:1px solid #eef2f7; }
.sidebar-search input { width:100%; box-sizing:border-box; border:1px solid #d8dee9; border-radius:8px; padding:10px 12px; background:#f9fbfd; }
.filter-tabs { display:flex; flex-wrap:wrap; gap:8px; padding:12px 20px 4px; border-bottom:1px solid #eef2f7; }
.filter-tab { border:0; background:#eef2f7; color:#111827; padding:8px 10px; border-radius:999px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-size:12px; }
.filter-tab.active { background:#111827; color:#fff; }
.filter-count { background:rgba(255,255,255,.25); min-width:18px; height:18px; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; padding:0 4px; font-size:11px; }
.filter-tab:not(.active) .filter-count { background:#dbe4ee; }
.conversation-list { flex:1; min-height:0; overflow-y:auto; padding:14px; }
.conversation-card { width:100%; display:flex; gap:12px; padding:12px; border-radius:8px; cursor:pointer; border:1px solid transparent; margin-bottom:10px; background:#fff; text-align:left; }
.conversation-card:hover,.conversation-card.active { background:#f8fafc; }
.conversation-card.active { border-color:#111827; }
.conversation-card.unread { border-color:#fecaca; background:#fff7f7; }
.avatar { width:42px; height:42px; border-radius:999px; background:#111827; color:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; font-weight:700; flex-shrink:0; }
.avatar.large { width:52px; height:52px; }
.avatar img { width:100%; height:100%; object-fit:cover; }
.conversation-meta { flex:1; min-width:0; }
.top-row,.sub-row { display:flex; justify-content:space-between; gap:10px; }
.name-wrap { display:flex; align-items:center; gap:8px; min-width:0; }
.name { font-weight:700; color:#111827; }
.sub-row { margin-top:8px; font-size:12px; color:#6b7280; }
.tag-row { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.mini-tag { font-size:11px; padding:3px 7px; border-radius:999px; white-space:nowrap; }
.unread-badge { min-width:22px; height:22px; padding:0 6px; border-radius:999px; background:#ef4444; color:#fff; font-size:12px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
.unread-tag { background:#fee2e2; color:#b91c1c; }
.wait-tag { background:#ede9fe; color:#6d28d9; }
.human-tag,.status-badge.human,.mode-pill.human { background:#fef3c7; color:#92400e; }
.ai-tag,.status-badge.ai,.mode-pill.ai { background:#e0f2fe; color:#0369a1; }
.status-badge,.mode-pill { padding:4px 8px; border-radius:999px; font-size:12px; white-space:nowrap; }
.chat-panel { display:flex; flex-direction:column; min-width:0; min-height:0; overflow:hidden; }
.chat-header { padding:20px 24px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; gap:16px; }
.chat-header-left { display:flex; gap:14px; align-items:center; }
.chat-header-left h3 { margin:0 0 8px; }
.header-sub { display:flex; gap:10px; align-items:center; color:#6b7280; font-size:13px; flex-wrap:wrap; }
.chat-header-actions { display:flex; gap:10px; }
.error-banner { margin:12px 24px 0; padding:10px 12px; border-radius:8px; background:#fee2e2; color:#991b1b; }
.message-panel { flex:1; min-height:0; overflow-y:auto; padding:24px; }
.message-row { display:flex; margin-bottom:14px; }
.message-row.inbound { justify-content:flex-start; }
.message-row.outbound { justify-content:flex-end; }
.message-bubble { max-width:72%; border-radius:8px; padding:12px 14px; box-shadow:0 6px 18px rgba(15,23,42,.06); }
.message-bubble.customer { background:#fff; }
.message-bubble.ai { background:#eef2ff; }
.message-bubble.agent { background:#dcfce7; }
.message-role { font-size:12px; font-weight:700; margin-bottom:6px; color:#374151; }
.message-content { white-space:pre-wrap; line-height:1.6; color:#111827; }
.message-content.markdown-body { white-space:normal; overflow-wrap:anywhere; }
.markdown-body :deep(p) { margin:0 0 8px; }
.markdown-body :deep(p:last-child) { margin-bottom:0; }
.markdown-body :deep(h3),
.markdown-body :deep(h4),
.markdown-body :deep(h5) { margin:10px 0 6px; line-height:1.35; font-size:14px; color:#111827; }
.markdown-body :deep(h3:first-child),
.markdown-body :deep(h4:first-child),
.markdown-body :deep(h5:first-child) { margin-top:0; }
.markdown-body :deep(ul),
.markdown-body :deep(ol) { margin:6px 0 10px; padding-left:18px; }
.markdown-body :deep(li) { margin:3px 0; }
.markdown-body :deep(a) { color:#2563eb; text-decoration:underline; text-underline-offset:2px; }
.markdown-body :deep(code) { border-radius:6px; background:rgba(17,24,39,.08); padding:2px 5px; font-family:ui-monospace,SFMono-Regular,Consolas,monospace; font-size:12px; }
.markdown-body :deep(pre) { margin:8px 0; overflow:auto; border-radius:8px; background:#111827; color:#f9fafb; padding:10px; }
.markdown-body :deep(pre code) { display:block; background:transparent; color:inherit; padding:0; white-space:pre; }
.message-time { margin-top:8px; font-size:11px; color:#6b7280; }
.reply-panel { flex-shrink:0; background:#fff; border-top:1px solid #e5e7eb; padding:18px 24px; }
.reply-panel textarea { width:100%; resize:vertical; box-sizing:border-box; border:1px solid #d8dee9; border-radius:8px; padding:14px; font-size:14px; min-height:96px; }
.reply-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:12px; align-items:center; }
.refresh-note { margin-right:auto; color:#6b7280; font-size:12px; }
.customer-panel { min-height:0; overflow-y:auto; border-left:1px solid #e5e7eb; background:#fff; padding:20px; }
.panel-card h3 { margin-top:0; margin-bottom:18px; }
.info-item { margin-bottom:16px; }
.info-item label { display:block; font-size:12px; color:#6b7280; margin-bottom:6px; }
.primary-btn,.secondary-btn,.ghost-btn { border:0; border-radius:8px; padding:10px 14px; cursor:pointer; font-size:14px; }
.primary-btn { background:#111827; color:#fff; }
.secondary-btn { background:#f59e0b; color:#fff; }
.ghost-btn { background:#eef2f7; color:#111827; }
.primary-btn:disabled,.secondary-btn:disabled,.ghost-btn:disabled { opacity:.6; cursor:not-allowed; }
.empty { display:flex; align-items:center; justify-content:center; }
.empty-box { text-align:center; color:#6b7280; }
@media (max-width:1280px) { .chat-layout { grid-template-columns:280px 1fr; } .customer-panel { display:none; } }
@media (max-width:900px) { .chat-layout { height:calc(100vh - 190px); grid-template-columns:1fr; } .sidebar { display:none; } }
</style>
