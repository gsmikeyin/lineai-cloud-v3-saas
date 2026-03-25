import { createRouter, createWebHistory } from 'vue-router'

import Login from '../pages/login.vue'
import Dashboard from '../pages/Dashboard.vue'
import Conversations from '../pages/Conversations.vue'
import Customers from '../pages/Customers.vue'
import LineBotSettings from '../pages/LineBotSettings.vue'
import KnowledgeBase from '../pages/KnowledgeBase.vue'
import Settings from '../pages/Settings.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import KnowledgeMatcherTest from '../pages/KnowledgeMatcherTest.vue'
import Register from '../pages/Register.vue'
import CustomerDetail from '../pages/CustomerDetail.vue'
import ForgotPassword from '../pages/ForgotPassword.vue'
import ResetPassword from '../pages/ResetPassword.vue'
import VerifyEmail from '../pages/VerifyEmail.vue'



const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true, title: 'Login' },
  },
  {
      path: '/register',
      name: 'register',
      component: Register,
      meta: { guestOnly: true, title: 'Register' },
  },
  {
  path: '/forgot-password',
  name: 'forgot-password',
  component: ForgotPassword,
  meta: { guestOnly: true, title: 'Forgot Password' },
},
{
  path: '/reset-password',
  name: 'reset-password',
  component: ResetPassword,
  meta: { guestOnly: true, title: 'Reset Password' },
},
   {
    path: '/',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' },
      },
      {
        path: 'conversations',
        name: 'conversations',
        component: Conversations,
        meta: { title: 'Conversations' },
      },
      {
        path: 'customers',
        name: 'customers',
        component: Customers,
        meta: { title: 'Customers' },
      },
      {
        path: 'settings/line-bot',
        name: 'line-bot-settings',
        component: LineBotSettings,
        meta: { title: 'LINE Bot 設定' },
      },
      {
        path: 'knowledge-base',
        name: 'knowledge-base',
        component: KnowledgeBase,
        meta: { title: 'Knowledge Base' },
      },
      {
        path: 'settings',
        name: 'settings',
        component: Settings,
        meta: { title: 'Settings' },
      },
      {
        path: 'knowledge-test',
        name: 'knowledge-test',
        component: KnowledgeMatcherTest,
        meta: { title: '知識命中測試' },
      }, 
      {
        path: 'customers/:id',
        name: 'customer-detail',
        component: CustomerDetail,
        meta: { title: 'Customer Detail' },
      },
      {
        path: 'verify-email',
        name: 'verify-email',
        component: VerifyEmail,
        meta: { title: 'Verify Email' },
     },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.guestOnly && token) {
    return next('/')
  }

  next()
})

export default router