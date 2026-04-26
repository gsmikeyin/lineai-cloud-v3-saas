<template>
  <div class="register-page">
    <div class="register-card">
      <h1>LineAI Cloud</h1>
      <p class="subtitle">建立你的 SaaS 帳號</p>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label>公司名稱</label>
          <input v-model="form.company_name" type="text" placeholder="請輸入公司名稱" />
        </div>

        <div class="form-group">
          <label>管理者姓名</label>
          <input v-model="form.name" type="text" placeholder="請輸入姓名" />
        </div>

        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="請輸入 Email" />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" placeholder="至少 8 碼" />
        </div>

        <div class="form-group">
          <label>確認密碼</label>
          <input v-model="form.password_confirmation" type="password" placeholder="再次輸入密碼" />
        </div>

        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? '註冊中...' : '註冊並登入' }}
        </button>
      </form>

      <div class="bottom-link">
        已有帳號？
        <router-link to="/login">前往登入</router-link>
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
  company_name: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

async function handleRegister() {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/register', form)


    localStorage.removeItem('token')
    localStorage.removeItem('user')


    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user', JSON.stringify(res.data.user))

    api.defaults.headers.common.Authorization = `Bearer ${res.data.token}`
    

    //window.location.href = '/app'
    router.push('/app')
    
  } catch (error) {
    
    const data = error.response?.data

    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      errorMessage.value = data.errors[firstKey][0]
    } else {
      errorMessage.value = data?.message || '註冊失敗，請稍後再試'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f7fb;
  padding: 24px;
}

.register-card {
  width: 100%;
  max-width: 460px;
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

.bottom-link {
  margin-top: 18px;
  font-size: 14px;
  color: #666;
  text-align: center;
}
</style>