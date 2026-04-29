<template>
  <div class="login-success-page">
    <div class="card">
      <h1>{{ title }}</h1>
      <p>{{ message }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const { locale } = useI18n()

const title = computed(() => locale.value === 'en' ? 'Signing in with LINE...' : 'LINE 登入中...')
const message = computed(() => locale.value === 'en' ? 'Preparing your account. Please wait.' : '正在準備你的帳號，請稍候。')

onMounted(async () => {
  const url = new URL(window.location.href)
  const token = url.searchParams.get('token')

  if (!token) {
    router.push('/login')
    return
  }

  localStorage.setItem('token', token)

  try {
    const res = await api.get('/me', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    if (res.data?.user) {
      localStorage.setItem('user', JSON.stringify(res.data.user))
    }
  } catch (e) {
  }

  router.push('/app')
})
</script>

<style scoped>
.login-success-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fb;
  padding: 24px;
}

.card {
  background: #fff;
  border-radius: 8px;
  padding: 32px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
</style>
