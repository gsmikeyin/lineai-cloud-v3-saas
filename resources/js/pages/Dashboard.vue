<template>
  <div class="dashboard-wrap">
    <EmailVerificationBanner :email-verified="emailVerified" />

    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-title">{{ $t('adminPages.dashboard.conversations') }}</div>
        <div class="stat-value">{{ stats.conversation_count }}</div>
        <div class="stat-sub">{{ $t('adminPages.dashboard.conversationsSub') }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-title">{{ $t('adminPages.dashboard.unread') }}</div>
        <div class="stat-value">{{ stats.unread_count }}</div>
        <div class="stat-sub">{{ $t('adminPages.dashboard.unreadSub') }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-title">{{ $t('adminPages.dashboard.customers') }}</div>
        <div class="stat-value">{{ stats.customer_count }}</div>
        <div class="stat-sub">{{ $t('adminPages.dashboard.customersSub') }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-title">{{ $t('adminPages.dashboard.aiReplies') }}</div>
        <div class="stat-value">{{ stats.ai_reply_count }}</div>
        <div class="stat-sub">{{ $t('adminPages.dashboard.aiRepliesSub') }}</div>
      </div>

      <div class="panel-card span-2">
        <h3>{{ $t('adminPages.dashboard.statusTitle') }}</h3>
        <p>{{ $t('adminPages.dashboard.statusDesc') }}</p>
      </div>

      <div class="panel-card">
        <h3>{{ $t('adminPages.dashboard.quickLinks') }}</h3>
        <div class="quick-links">
          <router-link to="/app/conversations" class="quick-link">{{ $t('adminPages.dashboard.goConversations') }}</router-link>
          <router-link to="/app/customers" class="quick-link">{{ $t('adminPages.dashboard.goCustomers') }}</router-link>
          <router-link to="/app/settings/line-bot" class="quick-link">{{ $t('adminPages.dashboard.goLineBot') }}</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../api'
import EmailVerificationBanner from '../components/EmailVerificationBanner.vue'

const emailVerified = ref(true)

const stats = reactive({
  conversation_count: 0,
  unread_count: 0,
  customer_count: 0,
  ai_reply_count: 0,
})

async function fetchMe() {
  try {
    const res = await api.get('/me')
    emailVerified.value = Boolean(res.data.email_verified)
  } catch (error) {
    console.error('dashboard me error =', error.response?.data || error)
  }
}

async function fetchStats() {
  try {
    const res = await api.get('/dashboard/stats')
    const data = res.data.data || {}

    stats.conversation_count = data.conversation_count ?? 0
    stats.unread_count = data.unread_count ?? 0
    stats.customer_count = data.customer_count ?? 0
    stats.ai_reply_count = data.ai_reply_count ?? 0
  } catch (error) {
    console.error('dashboard stats error =', error.response?.data || error)
  }
}

onMounted(() => {
  fetchMe()
  fetchStats()
})
</script>

<style scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.stat-card,
.panel-card {
  background: #fff;
  border-radius: 8px;
  padding: 22px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.stat-title,
.stat-sub {
  color: #6b7280;
  font-size: 13px;
}

.stat-value {
  font-size: 32px;
  font-weight: 800;
  margin-top: 10px;
}

.stat-sub {
  margin-top: 8px;
}

.panel-card h3 {
  margin-top: 0;
}

.panel-card p {
  color: #4b5563;
  line-height: 1.6;
  margin-bottom: 0;
}

.span-2 {
  grid-column: span 2;
}

.quick-links {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.quick-link {
  text-decoration: none;
  color: #111827;
  background: #eef2f7;
  padding: 12px 14px;
  border-radius: 8px;
  font-weight: 600;
}

.quick-link:hover {
  background: #e5e7eb;
}

@media (max-width: 1100px) {
  .dashboard-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 700px) {
  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .span-2 {
    grid-column: span 1;
  }
}
</style>
