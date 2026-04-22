import { createRouter, createWebHistory } from 'vue-router'



import LandingPage from '../pages/LandingPage.vue'
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
import ContactLeads from '../pages/ContactLeads.vue'
import LoginSuccess from '../pages/LoginSuccess.vue'
import KnowledgeUpload from '../pages/KnowledgeUpload.vue'

import DifyAppPools from '../pages/DifyAppPools.vue'

import DifyBindingHelper from '../pages/DifyBindingHelper.vue'
import DifyAppPoolManager from '../pages/DifyAppPoolManager.vue'
import KnowledgeHitTest from '../pages/KnowledgeHitTest.vue'

import { getAuthUser, hasRole } from '../utils/auth'



const routes = [
   {
    path: '/',
    name: 'landing',
    component: LandingPage,
    meta: { title: 'LineAI Cloud' },
  },  
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true, title: 'Login' },
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
    path: '/app',
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
        meta: { title: '對話' },
      },
      {
        path: 'customers',
        name: 'customers',
        component: Customers,
        meta: { title: '客戶' },
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
        meta: { title: '知識基礎' },
      },
      {
        path: 'settings',
        name: 'settings',
        component: Settings,
        meta: { title: '設定' },
      },
      {
        path: 'knowledge-hit-test',
        name: 'knowledge-hit-test',
        component: KnowledgeHitTest,
        meta: { title: 'Dify 知識命中測試' },
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
        meta: { title: 'Contact Leads' },
    },
    {
         path: 'knowledge-upload',
         name: 'knowledge-upload',
         component: KnowledgeUpload,
         meta: { title: 'Knowledge Upload' },
    },
    {
        path: 'dify-app-pools',
        name: 'dify-app-pools',
        component: DifyAppPools,
        meta: { title: 'Dify App Pools' },
    },

{
  path: 'knowledge-upload',
  name: 'knowledge-upload',
  component: KnowledgeUpload,
  meta: { requiresAuth: true, roles: ['super_admin', 'admin', 'staff'] },
},
{
  path: 'dify-binding',
  name: 'dify-binding',
  component: DifyBindingHelper,
  meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
},
{
  path: 'dify-app-pools',
  name: 'dify-app-pools',
  component: DifyAppPoolManager,
  meta: { requiresAuth: true, roles: ['super_admin', 'admin'] },
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
    return next('/app/dashboard')
  }
  next()

})

export default router