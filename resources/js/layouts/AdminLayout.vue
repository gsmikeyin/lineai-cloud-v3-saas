<template>
  <div class="admin-shell">
    <aside class="sidebar">
      <div class="brand">
        <h1>LineAI Cloud</h1>
        <p>SaaS Admin</p>
      </div>

      <nav class="menu">
        <RouterLink to="/" class="menu-item">Dashboard</RouterLink>
        <RouterLink to="/customers" class="menu-item">Customers</RouterLink>
        <RouterLink to="/conversations" class="menu-item">Conversations</RouterLink>
        <RouterLink to="/knowledge" class="menu-item">Knowledge</RouterLink>
        <RouterLink to="/campaigns" class="menu-item">Campaigns</RouterLink>
        <RouterLink to="/settings" class="menu-item">Settings</RouterLink>
      </nav>
    </aside>

    <div class="main">
      <header class="topbar">
        <div>
          <strong>{{ pageTitle }}</strong>
        </div>

        <div class="topbar-right">
          <span class="user-name">{{ userName }}</span>
          <button class="logout-btn" @click="logout">Logout</button>
        </div>
      </header>

      <main class="page-content">
        <slot />
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

const titleMap = {
  '/': 'Dashboard',
  '/customers': 'Customers',
  '/conversations': 'Conversations',
  '/knowledge': 'Knowledge',
  '/campaigns': 'Campaigns',
  '/settings': 'Settings',
}

const pageTitle = computed(() => titleMap[route.path] || 'Admin')

const userName = computed(() => {
  const raw = localStorage.getItem('user')
  if (!raw) return 'Admin'
  try {
    const user = JSON.parse(raw)
    return user.name || 'Admin'
  } catch {
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
  display: grid;
  grid-template-columns: 260px 1fr;
  min-height: 100vh;
  background: #f4f7fb;
}

.sidebar {
  background: #111827;
  color: #fff;
  padding: 24px 18px;
}

.brand {
  margin-bottom: 28px;
}

.brand h1 {
  margin: 0;
  font-size: 22px;
}

.brand p {
  margin: 6px 0 0;
  color: #9ca3af;
  font-size: 13px;
}

.menu {
  display: grid;
  gap: 8px;
}

.menu-item {
  display: block;
  padding: 12px 14px;
  border-radius: 10px;
  color: #d1d5db;
  text-decoration: none;
}

.menu-item.router-link-active {
  background: #1f2937;
  color: #fff;
  font-weight: 600;
}

.main {
  display: grid;
  grid-template-rows: 72px 1fr;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fff;
  padding: 0 24px;
  border-bottom: 1px solid #e5e7eb;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-name {
  color: #374151;
  font-size: 14px;
}

.logout-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  background: #111827;
  color: #fff;
  cursor: pointer;
}

.page-content {
  padding: 24px;
}

@media (max-width: 960px) {
  .admin-shell {
    grid-template-columns: 1fr;
  }

  .sidebar {
    display: none;
  }
}
</style>