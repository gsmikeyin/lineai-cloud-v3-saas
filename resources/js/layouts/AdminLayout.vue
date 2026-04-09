<template>
  <div class="admin-shell">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-logo">L</div>
        <div>
          <div class="brand-title">ServiceAI Cloud</div>
          <div class="brand-sub">Admin 儀表板</div>
        </div>
      </div>

      <nav class="nav">
        <router-link to="/app" class="nav-item">
          <span>{{ $t('nav.dashboard') }}</span>
        </router-link>

        <router-link to="/app/conversations" class="nav-item">
          <span>對話</span>
        </router-link>

        <router-link to="/app/customers" class="nav-item">
          <span>客戶</span>
        </router-link>

        <router-link to="/app/settings/line-bot" class="nav-item">
          <span>LINE Bot 設定</span>
        </router-link>

        <router-link to="/app/knowledge-base" class="nav-item">
          <span>知識庫</span>
        </router-link>

        <router-link to="/app/knowledge-test" class="nav-item">
             <span>知識命中測試</span>
        </router-link>


        <router-link to="/app/settings" class="nav-item">
          <span>設定</span>
        </router-link>

        <router-link to="/app/contact-leads" class="nav-item">
            <span>聯絡潛在客戶</span>
        </router-link>
        
        
       <a href="/files/manual.pdf" download class="nav-item">
               下載使用說明
        </a>
        
        
      </nav>
    </aside>

    <div class="main">
      <header class="topbar">
        <div>
          <h1 class="topbar-title">{{ pageTitle }}</h1>
          <p class="topbar-sub">LINE AI SaaS 後台管理系統</p>
        </div>

        <div class="topbar-actions">
          <div class="user-box">
            <div class="user-name">{{ userName }}</div>
            <div class="user-role">Tenant Admin</div>
          </div>
          <button class="logout-btn" @click="logout">登出</button>
        </div>
      </header>

      <main class="content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()

const pageTitle = computed(() => {
  return route.meta?.title || 'Dashboard'
})

const userName = computed(() => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user?.name || 'Admin'
  } catch (e) {
    return 'Admin'
  }
})

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
.admin-shell {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 260px 1fr;
  background: #f4f7fb;
}

.sidebar {
  background: #111827;
  color: #fff;
  padding: 22px 16px;
  display: flex;
  flex-direction: column;
  border-right: 1px solid rgba(255,255,255,0.06);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px 18px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  margin-bottom: 18px;
}

.brand-logo {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 18px;
}

.brand-title {
  font-weight: 700;
  font-size: 16px;
}

.brand-sub {
  font-size: 12px;
  color: rgba(255,255,255,0.65);
  margin-top: 2px;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  text-decoration: none;
  color: rgba(255,255,255,0.88);
  padding: 12px 14px;
  border-radius: 12px;
  transition: all 0.2s ease;
  display: block;
}

.nav-item:hover {
  background: rgba(255,255,255,0.08);
  color: #fff;
}

.nav-item.router-link-active {
  background: #2563eb;
  color: #fff;
  font-weight: 600;
}

.main {
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.topbar {
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  padding: 20px 28px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.topbar-title {
  margin: 0 0 6px;
  font-size: 24px;
}

.topbar-sub {
  margin: 0;
  color: #6b7280;
  font-size: 13px;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.user-box {
  text-align: right;
}

.user-name {
  font-weight: 700;
  color: #111827;
}

.user-role {
  font-size: 12px;
  color: #6b7280;
}

.logout-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  background: #111827;
  color: #fff;
  cursor: pointer;
}

.content {
  padding: 24px 28px;
}

@media (max-width: 960px) {
  .admin-shell {
    grid-template-columns: 1fr;
  }

  .sidebar {
    display: none;
  }

  .topbar {
    padding: 18px 20px;
  }

  .content {
    padding: 20px;
  }
}
</style>