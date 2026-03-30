<template>
  <div class="dashboard-wrap">
    <EmailVerificationBanner :email-verified="emailVerified" />

    <div class="dashboard-grid">
      <div class="stat-card">
        <div class="stat-title">對話</div>
        <div class="stat-value">{{ stats.conversation_count }}</div>
        <div class="stat-sub">本月累計對話</div>
      </div>

      <div class="stat-card">
        <div class="stat-title">未讀</div>
         <div class="stat-value">{{ stats.conversation_count }}</div>
        <div class="stat-sub">待處理訊息</div>
      </div>

      <div class="stat-card">
        <div class="stat-title">客戶</div>
        <div class="stat-value">{{ stats.unread_count }}</div>
        <div class="stat-sub">累積客戶數</div>
      </div>

      <div class="stat-card">
        <div class="stat-title">AI 回覆</div>
        <div class="stat-value">{{ stats.customer_count }}</div>
        <div class="stat-sub">AI 已回覆數</div>
      </div>

      <div class="panel-card span-2">
        <h3>系統概覽</h3>
        <p>你現在可以從左側選單進入 對話、客戶、LINE Bot 設定、知識基礎 與 設定。</p>
        <p><a href="/files/manual.pdf" download>點擊下載使用說明</a></p>
      </div>

      <div class="panel-card">
        <h3>快速入口</h3>
        <div class="quick-links">
          <router-link to="/conversations" class="quick-link">進入 對話</router-link>
          <router-link to="/customers" class="quick-link">進入 客戶</router-link>
          <router-link to="/settings/line-bot" class="quick-link">設定 LINE Bot</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import api from '../api'

const stats = reactive({
  conversation_count: 0,
  unread_count: 0,
  customer_count: 0,
  ai_reply_count: 0,
})

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

onMounted(fetchStats)
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
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}
.stat-title {
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
  color: #6b7280;
  font-size: 13px;
}
.panel-card h3 {
  margin-top: 0;
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
  border-radius: 10px;
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