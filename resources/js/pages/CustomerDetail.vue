<template>
  <div class="detail-grid" v-if="customer">
    <div class="main-col">
      <div class="card hero-card">
        <div class="hero-top">
          <div class="avatar">
            <img v-if="customer.avatar_url" :src="customer.avatar_url" alt="" />
            <span v-else>{{ getInitial(customer.display_name) }}</span>
          </div>

          <div class="hero-meta">
            <h2>{{ customer.display_name || 'Unknown Customer' }}</h2>
            <div class="sub-line">
              <span class="badge" :class="customer.is_vip ? 'vip' : 'normal'">
                {{ customer.is_vip ? 'VIP 客戶' : '一般客戶' }}
              </span>
              <span>{{ customer.status || 'active' }}</span>
            </div>
          </div>

          <div class="hero-actions">
            <router-link
              v-if="latestConversationId"
              :to="`/conversations`"
              class="primary-link"
            >
              前往 Conversations
            </router-link>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>基本資料</h3>
        <div class="info-grid">
          <div class="info-item">
            <label>電話</label>
            <div>{{ customer.phone || '-' }}</div>
          </div>
          <div class="info-item">
            <label>Email</label>
            <div>{{ customer.email || '-' }}</div>
          </div>
          <div class="info-item">
            <label>語系</label>
            <div>{{ customer.language || '-' }}</div>
          </div>
          <div class="info-item">
            <label>城市</label>
            <div>{{ customer.city || '-' }}</div>
          </div>
          <div class="info-item">
            <label>國家</label>
            <div>{{ customer.country || '-' }}</div>
          </div>
          <div class="info-item">
            <label>生日</label>
            <div>{{ customer.birthday || '-' }}</div>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>最近對話</h3>

        <div v-if="!(customer.conversations || []).length" class="empty-box">
          尚無對話資料
        </div>

        <div v-else class="conversation-list">
          <div
            v-for="item in customer.conversations"
            :key="item.id"
            class="conversation-item"
          >
            <div>
              <div class="conversation-title">
                對話 #{{ item.id }}
              </div>
              <div class="conversation-sub">
                狀態：{{ item.status }} ・
                模式：{{ item.human_handoff ? '人工' : 'AI' }} ・
                指派：{{ item.assigned_user?.name || item.assignedUser?.name || '未指派' }}
              </div>
            </div>
            <div class="conversation-time">
              {{ formatDate(item.last_message_at) }}
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>最近訊息</h3>

        <div v-if="!(customer.messages || []).length" class="empty-box">
          尚無訊息資料
        </div>

        <div v-else class="message-list">
          <div
            v-for="msg in customer.messages"
            :key="msg.id"
            class="message-item"
          >
            <div class="message-head">
              <span class="msg-role">{{ msg.sender_type || '-' }}</span>
              <span class="msg-time">{{ formatDate(msg.sent_at || msg.created_at) }}</span>
            </div>
            <div class="message-content">{{ msg.content || '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="side-col">
      <div class="card">
        <h3>統計資訊</h3>
        <div class="stat-list">
          <div class="stat-item">
            <label>總訊息數</label>
            <strong>{{ customer.total_messages ?? 0 }}</strong>
          </div>
          <div class="stat-item">
            <label>總訂單數</label>
            <strong>{{ customer.total_orders ?? 0 }}</strong>
          </div>
          <div class="stat-item">
            <label>累計消費</label>
            <strong>{{ customer.total_spent ?? 0 }}</strong>
          </div>
          <div class="stat-item">
            <label>首次互動</label>
            <strong>{{ formatDate(customer.first_interaction_at) }}</strong>
          </div>
          <div class="stat-item">
            <label>最後互動</label>
            <strong>{{ formatDate(customer.last_interaction_at) }}</strong>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>客戶標籤</h3>
        <div v-if="!(customer.tags || []).length" class="empty-box">
          尚無標籤
        </div>
        <div v-else class="tag-list">
          <span
            v-for="tag in customer.tags"
            :key="tag.id"
            class="tag-chip"
          >
            {{ tag.name }}
          </span>
        </div>
      </div>

      <div class="card">
        <h3>客服備註</h3>
        <div v-if="!(customer.notes || []).length" class="empty-box">
          尚無備註
        </div>
        <div v-else class="note-list">
          <div
            v-for="note in customer.notes"
            :key="note.id"
            class="note-item"
          >
            <div class="note-content">{{ note.note }}</div>
            <div class="note-meta">
              {{ note.user?.name || 'System' }} ・ {{ formatDate(note.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="loading-wrap">
    讀取中...
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'

const route = useRoute()
const customer = ref(null)

const latestConversationId = computed(() => {
  return customer.value?.conversations?.[0]?.id || null
})

async function fetchCustomer() {
  const res = await api.get(`/customers/${route.params.id}`)
  customer.value = res.data
}

function formatDate(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleString('zh-TW')
}

function getInitial(name) {
  if (!name) return '?'
  return name.charAt(0).toUpperCase()
}

onMounted(fetchCustomer)
</script>

<style scoped>
.detail-grid {
  display: grid;
  grid-template-columns: 1.4fr 0.8fr;
  gap: 20px;
}

.main-col,
.side-col {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.hero-top {
  display: flex;
  gap: 18px;
  align-items: center;
}

.avatar {
  width: 72px;
  height: 72px;
  border-radius: 999px;
  background: #111827;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  font-weight: 700;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-meta {
  flex: 1;
}

.hero-meta h2 {
  margin: 0 0 8px;
}

.sub-line {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  color: #6b7280;
  font-size: 14px;
}

.badge {
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
}

.badge.vip {
  background: #fef3c7;
  color: #92400e;
}

.badge.normal {
  background: #eef2f7;
  color: #374151;
}

.primary-link {
  text-decoration: none;
  background: #111827;
  color: #fff;
  border-radius: 10px;
  padding: 10px 14px;
  display: inline-flex;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.info-item label,
.stat-item label {
  display: block;
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 6px;
}

.conversation-list,
.message-list,
.note-list,
.stat-list {
  display: grid;
  gap: 12px;
}

.conversation-item,
.message-item,
.note-item,
.stat-item {
  border: 1px solid #eef2f7;
  border-radius: 14px;
  padding: 14px;
  background: #f9fbfd;
}

.conversation-title {
  font-weight: 700;
  margin-bottom: 4px;
}

.conversation-sub,
.note-meta,
.msg-time {
  color: #6b7280;
  font-size: 12px;
}

.message-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 8px;
}

.msg-role {
  font-weight: 700;
  color: #111827;
}

.message-content,
.note-content {
  line-height: 1.7;
  color: #111827;
}

.tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag-chip {
  background: #eef2ff;
  color: #4338ca;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
}

.empty-box,
.loading-wrap {
  color: #6b7280;
}

.loading-wrap {
  padding: 24px;
}

@media (max-width: 980px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .hero-top {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>