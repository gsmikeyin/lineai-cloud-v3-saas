<template>
  <div class="page">
    <div class="topbar">
      <div>
        <h1>Dashboard</h1>
        <p>LineAI Cloud 後台已登入</p>
      </div>

      <div class="actions">
        <router-link to="/conversations" class="nav-btn">Conversations</router-link>

        <router-link to="/settings/line-bot" class="nav-btn secondary-link">LINE Bot 設定</router-link>

        <button @click="logout">Logout</button>
      </div>
    </div>

    <div class="card-grid">
      <div class="card">
        <h3>客服中心</h3>
        <p>查看 LINE 對話、人工接手、切回 AI。</p>
      </div>

      <div class="card">
        <h3>CRM</h3>
        <p>客戶資料、標籤、互動紀錄。</p>
      </div>

      <div class="card">
        <h3>AI 回覆</h3>
        <p>FAQ 優先，未命中再交給 AI。</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

async function logout() {
  try {
    await api.post('/logout')
  } catch (e) {
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}
</script>

<style scoped>

.secondary-link {
  background: #0f766e;
}


.page {
  padding: 32px;
  background: #f4f7fb;
  min-height: 100vh;
}
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}
.actions {
  display: flex;
  gap: 10px;
}
h1 {
  margin: 0 0 6px;
}
p {
  margin: 0;
  color: #666;
}
button {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  background: #111827;
  color: #fff;
  cursor: pointer;
}
.nav-btn {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  border-radius: 10px;
  padding: 10px 14px;
  background: #2563eb;
  color: #fff;
}
.card-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}
.card {
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
}
.card h3 {
  margin-top: 0;
  margin-bottom: 8px;
}
@media (max-width: 900px) {
  .card-grid {
    grid-template-columns: 1fr;
  }
}
</style>