<template>
  <div class="login-page">
    <div class="login-card">
      <h1>LineAI Cloud</h1>
      <p class="subtitle">登入後台</p>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="請輸入 Email" />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" placeholder="請輸入密碼" />
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? '登入中...' : '登入' }}
        </button>
      </form>

      <div class="demo-box">
        <div>Demo 帳號</div>
        <div>owner@example.com</div>
        <div>password</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: 'owner@example.com',
  password: 'password',
})

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/login', {
      email: form.email,
      password: form.password,
    })

    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user', JSON.stringify(res.data.user))

    router.push('/')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || '登入失敗，請稍後再試'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fb;
  padding: 24px;
}

.login-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

h1 {
  margin: 0 0 8px;
  font-size: 28px;
}

.subtitle {
  margin: 0 0 24px;
  color: #666;
}

.form-group {
  margin-bottom: 16px;
}

label {
  display: block;
  font-size: 14px;
  margin-bottom: 8px;
  color: #333;
}

input {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #dcdfe6;
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 14px;
}

button {
  width: 100%;
  border: 0;
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 15px;
  cursor: pointer;
  background: #111827;
  color: #fff;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  margin-bottom: 16px;
  color: #d93025;
  font-size: 14px;
}

.demo-box {
  margin-top: 20px;
  padding: 12px;
  border-radius: 10px;
  background: #f8fafc;
  color: #555;
  font-size: 13px;
  line-height: 1.7;
}
</style>