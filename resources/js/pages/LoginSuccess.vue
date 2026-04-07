<template>
  <div class="login-success-page">
    <div class="card">
      <h1>LINE 登入中...</h1>
      <p>正在建立登入狀態，請稍候。</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

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
    console.error(e)
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
}
.card {
  background: #fff;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
</style>