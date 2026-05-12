<template>
  <div class="admin-shell">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-logo">S</div>
        <div>
          <div class="brand-title">ServiceAI Cloud</div>
          <div class="brand-sub">Admin Console</div>
        </div>
      </div>

      <nav class="nav">
        <router-link to="/app" class="nav-item">
          <span>{{ $t('nav.dashboard') }}</span>
        </router-link>

        <router-link v-if="isOwner()" to="/app/conversations" class="nav-item">
          <span>{{ $t('nav.conversations') }}</span>
        </router-link>

        <router-link v-if="isOwner()" to="/app/customers" class="nav-item">
          <span>{{ $t('nav.customers') }}</span>
        </router-link>

        <router-link to="/app/settings/line-bot" class="nav-item">
          <span>{{ $t('nav.lineBot') }}</span>
        </router-link>

        <router-link v-if="isOwner()" to="/app/knowledge-upload" class="nav-item">
          <span>{{ $t('nav.knowledgeUpload') }}</span>
        </router-link>

        <router-link to="/app/knowledge-hit-test" class="nav-item">
          <span>{{ $t('nav.knowledgeHitTest') }}</span>
        </router-link>

        <router-link v-if="isAdmin()" to="/app/settings" class="nav-item">
          <span>{{ $t('nav.settings') }}</span>
        </router-link>

        <router-link v-if="isAdmin()" to="/app/contact-leads" class="nav-item">
          <span>{{ $t('nav.contactLeads') }}</span>
        </router-link>

        <router-link v-if="isAdmin()" to="/app/dify-app-pools" class="nav-item">
          <span>{{ $t('nav.difyAppPools') }}</span>
        </router-link>

        <router-link v-if="isAdmin()" to="/app/analytics/page-views" class="nav-item">
          <span>{{ $t('nav.pageViews') }}</span>
        </router-link>

        <router-link v-if="isAdmin()" to="/app/analytics/auth" class="nav-item">
          <span>{{ $t('nav.authAnalytics') }}</span>
        </router-link>

        <router-link v-if="isSuperAdmin()" to="/app/admin/user-roles" class="nav-item">
          <span>{{ $t('nav.userRoles') }}</span>
        </router-link>

        <router-link to="/app/manual" class="nav-item">
          <span>{{ $t('nav.manual') }}</span>
        </router-link>
      </nav>
    </aside>

    <div class="main">
      <header class="topbar">
        <div>
          <h1 class="topbar-title">{{ pageTitle }}</h1>
          <p class="topbar-sub">{{ $t('admin.subtitle') }}</p>
        </div>

        <div class="topbar-actions">
          <label class="locale-control">
            <span>{{ $t('admin.language') }}</span>
            <select :value="locale" @change="changeLocale($event.target.value)">
              <option value="zh_TW">繁中</option>
              <option value="en">English</option>
            </select>
          </label>

          <div class="user-box">
            <div class="user-name">{{ userName }}</div>
            <div class="user-role">{{ $t('admin.role') }}</div>
          </div>
          <button class="logout-btn" type="button" @click="logout">{{ $t('nav.logout') }}</button>
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
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'
import { isAdmin, isOwner, isSuperAdmin } from '../utils/auth'

const route = useRoute()
const router = useRouter()
const { locale, t } = useI18n()

const pageTitle = computed(() => {
  if (route.meta?.titleKey) {
    return t(route.meta.titleKey)
  }

  return route.meta?.title || t('nav.dashboard')
})

const userName = computed(() => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user?.name || 'Admin'
  } catch (e) {
    return 'Admin'
  }
})

async function changeLocale(value) {
  locale.value = value
  localStorage.setItem('locale', value)

  try {
    await api.put('/me/locale', { locale: value })
  } catch (e) {
  }
}

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
  border-right: 1px solid rgba(255, 255, 255, 0.06);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 10px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 18px;
}

.brand-logo {
  width: 42px;
  height: 42px;
  border-radius: 8px;
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
  color: rgba(255, 255, 255, 0.65);
  margin-top: 2px;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  text-decoration: none;
  color: rgba(255, 255, 255, 0.88);
  padding: 12px 14px;
  border-radius: 8px;
  transition: all 0.2s ease;
  display: block;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
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
  gap: 16px;
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

.locale-control {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  font-size: 13px;
}

.locale-control select {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 8px 10px;
  background: #fff;
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
  border-radius: 8px;
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
    align-items: flex-start;
    flex-direction: column;
  }

  .topbar-actions {
    width: 100%;
    align-items: flex-start;
    flex-direction: column;
  }

  .content {
    padding: 20px;
  }
}
</style>
