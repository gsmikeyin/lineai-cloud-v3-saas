import { createRouter, createWebHistory } from 'vue-router'

import LandingPage from '../pages/LandingPage.vue'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import Conversations from '../pages/Conversations.vue'
import Customers from '../pages/Customers.vue'
import LineBotSettings from '../pages/LineBotSettings.vue'
import KnowledgeBase from '../pages/KnowledgeBase.vue'
import Settings from '../pages/Settings.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import Register from '../pages/Register.vue'
import CustomerDetail from '../pages/CustomerDetail.vue'
import ForgotPassword from '../pages/ForgotPassword.vue'
import ResetPassword from '../pages/ResetPassword.vue'
import VerifyEmail from '../pages/VerifyEmail.vue'
import ContactLeads from '../pages/ContactLeads.vue'
import LoginSuccess from '../pages/LoginSuccess.vue'
import KnowledgeUpload from '../pages/KnowledgeUpload.vue'
import ManualDownload from '../pages/ManualDownload.vue'
import DifyAppPoolManager from '../pages/DifyAppPoolManager.vue'
import KnowledgeHitTest from '../pages/KnowledgeHitTest.vue'

import { getAuthUser, hasRole } from '../utils/auth'

const routes = [
  {
    path: '/',
    name: 'landing',
    component: LandingPage,
    meta: { title: 'ServiceAI Cloud' },
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true, titleKey: 'nav.login' },
  },
  {
    path: '/login/success',
    name: 'login-success',
    component: LoginSuccess,
    meta: { title: 'LINE Login Success' },
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guestOnly: true, titleKey: 'nav.register' },
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
    path: '/app',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: Dashboard,
        meta: { titleKey: 'nav.dashboard' },
      },
      {
        path: 'conversations',
        name: 'conversations',
        component: Conversations,
        meta: { titleKey: 'nav.conversations' },
      },
      {
        path: 'customers',
        name: 'customers',
        component: Customers,
        meta: { titleKey: 'nav.customers' },
      },
      {
        path: 'settings/line-bot',
        name: 'line-bot-settings',
        component: LineBotSettings,
        meta: { titleKey: 'nav.lineBot' },
      },
      {
        path: 'knowledge-base',
        name: 'knowledge-base',
        component: KnowledgeBase,
        meta: { titleKey: 'nav.knowledgeUpload' },
      },
      {
        path: 'settings',
        name: 'settings',
        component: Settings,
        meta: { titleKey: 'nav.settings' },
      },
      {
        path: 'knowledge-hit-test',
        name: 'knowledge-hit-test',
        component: KnowledgeHitTest,
        meta: { titleKey: 'nav.knowledgeHitTest' },
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
      {
        path: 'contact-leads',
        name: 'contact-leads',
        component: ContactLeads,
        meta: { titleKey: 'nav.contactLeads' },
      },
      {
        path: 'knowledge-upload',
        name: 'knowledge-upload',
        component: KnowledgeUpload,
        meta: { requiresAuth: true, roles: ['super_admin', 'admin', 'owner'], titleKey: 'nav.knowledgeUpload' },
      },
      {
        path: 'dify-app-pools',
        name: 'dify-app-pools',
        component: DifyAppPoolManager,
        meta: { requiresAuth: true, roles: ['super_admin', 'admin'], titleKey: 'nav.difyAppPools' },
      },
      {
        path: 'manual',
        name: 'manual',
        component: ManualDownload,
        meta: { titleKey: 'nav.manual' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const user = getAuthUser()

  if (to.meta.requiresAuth && !user) {
    return next('/login')
  }

  if (to.meta.roles && !hasRole(to.meta.roles)) {
    return next('/app')
  }

  next()
})

export default router
