<template>

   

  <div class="landing-page">
    <header class="topbar">
      <div class="container topbar-inner">
        <div class="brand">
          <div class="brand-logo">L</div>
          <div class="brand-text">
            <div class="brand-title">LineAI Cloud</div>
            <div class="brand-sub">LINE AI Customer Service SaaS</div>
          </div>
        </div>

        <nav class="topnav">
          <a href="#features">功能</a>
          <a href="#use-cases">應用場景</a>
          <a href="#faq">FAQ</a>
          <a href="#contact">聯絡我們</a>
          <router-link to="/login" class="login-link">登入</router-link>

          <select :value="locale" @change="changeLocale($event.target.value)" class="locale-select">
                 <option value="zh_TW">繁中</option>
                 <option value="en">English</option>                 
          </select>
        </nav>
      </div>
    </header>

    <section class="hero">
      <div class="container hero-grid">
        <div class="hero-left">
          <div class="hero-badge">{{ $t('landing.badge') }}</div>
          <h1>{{ $t('landing.heroTitle1') }}<br />{{ $t('landing.heroTitle2') }}</h1>
          <p class="hero-desc">{{ $t('landing.heroDesc') }}</p>

          <div class="hero-actions">
            <router-link to="/register" class="primary-btn">
                {{ $t('landing.startFree') }}
            </router-link>

           <router-link to="/login" class="ghost-btn">
                {{ $t('landing.goLogin') }}
           </router-link>

          

          </div>

          <div class="hero-points">
            <div class="point-item">✓ LINE 官方帳號整合</div>
            <div class="point-item">✓ AI 自動回覆</div>
            <div class="point-item">✓ 真人客服接手</div>
            <div class="point-item">✓ 客戶資料與知識庫管理</div>
          </div>
        </div>

        <div class="hero-right">
          <div class="showcase-card">
            <div class="showcase-header">
              <span class="dot red"></span>
              <span class="dot yellow"></span>
              <span class="dot green"></span>
            </div>

            <div class="showcase-body">
              <aside class="showcase-sidebar">
                <div class="side-item active">Dashboard</div>
                <div class="side-item">對話</div>
                <div class="side-item">客戶</div>
                <div class="side-item">LINE Bot 設定</div>
                <div class="side-item">智識庫</div>                
              </aside>

              <div class="showcase-main">
                <div class="mini-stats">
                  <div class="mini-stat">
                    <div class="mini-label">Conversations</div>
                    <div class="mini-value">1,280</div>
                  </div>
                  <div class="mini-stat">
                    <div class="mini-label">Unread</div>
                    <div class="mini-value">24</div>
                  </div>
                  <div class="mini-stat">
                    <div class="mini-label">Customers</div>
                    <div class="mini-value">542</div>
                  </div>
                </div>

                <div class="chat-preview">
                  <div class="bubble left">請問你們幾點營業？</div>
                  <div class="bubble right">您好，我們的營業時間為週一到週五 09:00–18:00。</div>
                  <div class="bubble left">可以退貨嗎？</div>
                  <div class="bubble right">收到商品 7 天內可申請退貨，也可以協助您轉接真人客服。</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="trust-strip">
      <div class="container trust-grid">
        <div class="trust-item">
          <strong>AI 回覆</strong>
          <span>FAQ / Prompt / Knowledge 整合</span>
        </div>
        <div class="trust-item">
          <strong>CRM</strong>
          <span>客戶資料、標籤、備註、歷史互動</span>
        </div>
        <div class="trust-item">
          <strong>客服後台</strong>
          <span>未讀、接手、回覆、對話管理</span>
        </div>
        <div class="trust-item">
          <strong>SaaS 架構</strong>
          <span>Tenant、設定、擴充性與產品化</span>
        </div>
      </div>
    </section>

    <section id="features" class="section">
      <div class="container">
        <div class="section-head">
          <h2>核心功能</h2>
          <p>從客戶訊息進站，到 AI 與真人協作回覆，全流程整合。</p>
        </div>

        <div class="feature-grid">
          <div class="feature-card">
            <h3>AI 自動回覆</h3>
            <p>先命中知識庫，再交由 AI 補足情境與語氣，提升回覆速度與一致性。</p>
          </div>

          <div class="feature-card">
            <h3>Knowledge Base</h3>
            <p>集中維護 FAQ、退換貨政策、產品知識與 Prompt 規則，持續優化 AI 品質。</p>
          </div>

          <div class="feature-card">
            <h3>真人接手</h3>
            <p>遇到複雜問題時，一鍵切換人工模式，避免 AI 回覆不完整影響成交。</p>
          </div>

          <div class="feature-card">
            <h3>Conversations 後台</h3>
            <p>具備對話列表、未讀數、客戶資訊、客服回覆與即時操作能力。</p>
          </div>

          <div class="feature-card">
            <h3>Customer CRM</h3>
            <p>客戶資料、標籤、備註、歷史訊息與互動紀錄集中管理。</p>
          </div>

          <div class="feature-card">
            <h3>多語與擴充性</h3>
            <p>可延伸 i18n、Billing、WebSocket、AI 成本統計與 SaaS 商業模型。</p>
          </div>
        </div>
      </div>
    </section>

    <section id="use-cases" class="section alt">
      <div class="container">
        <div class="section-head">
          <h2>適用場景</h2>
          <p>不同行業都可以把 LINE 從聊天工具升級成營運系統。</p>
        </div>

        <div class="usecase-grid">
          <div class="usecase-card">
            <h3>電商 / 零售</h3>
            <p>自動回答出貨、付款、退貨、商品規格與客服常見問題。</p>
          </div>

          <div class="usecase-card">
            <h3>品牌客服</h3>
            <p>維持品牌口吻，建立標準化客服流程，縮短新人上手時間。</p>
          </div>

          <div class="usecase-card">
            <h3>門市 / 預約服務</h3>
            <p>處理預約、營業資訊、價格詢問與轉人工客服需求。</p>
          </div>

          <div class="usecase-card">
            <h3>B2B 服務公司</h3>
            <p>把客服流程數位化，並逐步擴充成可商業化的 SaaS 平台。</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <h2>流程很簡單</h2>
          <p>幾步就能開始運作。</p>
        </div>

        <div class="steps-grid">
          <div class="step-card">
            <div class="step-no">1</div>
            <h3>設定 LINE Bot</h3>
            <p>填入 Channel Secret、Access Token 與 Webhook URL。</p>
          </div>

          <div class="step-card">
            <div class="step-no">2</div>
            <h3>建立 Knowledge Base</h3>
            <p>把營業時間、物流、退換貨與產品資訊放進系統。</p>
          </div>

          <div class="step-card">
            <div class="step-no">3</div>
            <h3>開始接收訊息</h3>
            <p>客戶透過 LINE 送訊息進來，系統自動建立對話與客戶資料。</p>
          </div>

          <div class="step-card">
            <div class="step-no">4</div>
            <h3>AI 與真人協作</h3>
            <p>AI 回覆常見問題，複雜情境交由客服接手處理。</p>
          </div>
        </div>
      </div>
    </section>

    <section id="faq" class="section faq-section">
      <div class="container">
        <div class="section-head">
          <h2>常見問題</h2>
          <p>導入前常被問到的幾個問題。</p>
        </div>

        <div class="faq-list">
          <div class="faq-item">
            <h3>這是只能做 AI 聊天機器人嗎？</h3>
            <p>不是。它同時包含 AI、自動化、客服後台、Knowledge Base 與 CRM，是一整套客服營運系統。</p>
          </div>

          <div class="faq-item">
            <h3>如果 AI 回錯怎麼辦？</h3>
            <p>可以透過 Prompt 與知識庫持續優化，也可以一鍵切換真人客服接手。</p>
          </div>

          <div class="faq-item">
            <h3>能不能依產業客製？</h3>
            <p>可以。你可以依照電商、門市、預約、品牌客服等場景建立不同知識內容與流程。</p>
          </div>

          <div class="faq-item">
            <h3>可以做成 SaaS 產品嗎？</h3>
            <p>可以。系統本身就是以 Tenant 架構設計，方便後續擴充成可商業化的 SaaS 平台。</p>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="section contact-section">
      <div class="container">
        <div class="section-head">
          <h2>聯絡我們</h2>
          <p>想導入、客製或合作，直接留下資訊，我們會與你聯繫。</p>
        </div>

        <div class="contact-grid">
          <div class="contact-info-card">
            <h3>聯絡資訊</h3>
            <div class="contact-info-list">
              <div class="contact-info-item">
                <label>Email</label>
                <div>service@serviceaicloud.com</div>
              </div>
              <div class="contact-info-item">
                <label>電話</label>
                <div>+886-2-8226-9997</div>
              </div>
              <div class="contact-info-item">
                <label>服務時間</label>
                <div>週一至週五 09:00–18:00</div>
              </div>
              <div class="contact-info-item">
                <label>合作項目</label>
                <div>LINE AI 客服、CRM、SaaS 客製、系統整合</div>
              </div>
            </div>
          </div>

          <div class="contact-form-card">
            <h3>聯絡表單</h3>

            <form @submit.prevent="submitContact">
              <div class="form-grid">
                <div class="form-group">
                  <label>{{ $t('form.name') }}</label>                  
                  <input v-model="contactForm.name" type="text" placeholder="請輸入姓名" />
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input v-model="contactForm.email" type="email" placeholder="請輸入 Email" />
                </div>

                <div class="form-group">
                  <label>公司名稱</label>
                  <input v-model="contactForm.company" type="text" placeholder="請輸入公司名稱" />
                </div>

                <div class="form-group">
                  <label>電話</label>
                  <input v-model="contactForm.phone" type="text" placeholder="請輸入聯絡電話" />
                </div>

                <div class="form-group full">
                  <label>需求說明</label>
                  <textarea
                    v-model="contactForm.message"
                    rows="5"
                    placeholder="請描述你的需求，例如：想導入 LINE AI 客服、客製 SaaS、串接 CRM..."
                  />
                </div>
              </div>



             <div v-if="contactSuccess" class="success-box">
               {{ contactSuccess }}
             </div>

            <div v-if="contactError" class="error-box">
                 {{ contactError }}
           </div>

          <div class="contact-actions">
                 <button type="submit" class="primary-btn button-btn" :disabled="contactLoading">
                           {{ contactLoading ? '送出中...' : '送出聯絡資訊' }}
                 </button>
          </div>



            </form>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="container">
        <div class="cta-card">
          <h2>準備開始建立你的 LINE AI 客服平台？</h2>
          <p>從註冊、設定 Bot、建立知識庫，到進入客服後台，幾步就能開始。</p>

          <div class="hero-actions center">
            <router-link to="/register" class="primary-btn">
              立即開始
            </router-link>
            <router-link to="/login" class="ghost-btn">
              前往登入
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="container footer-main">
        <div class="footer-col brand-col">
          <div class="footer-brand">LineAI Cloud</div>
          <p class="footer-text">
            LINE AI Customer Service SaaS Platform
          </p>
          <p class="footer-desc">
            專注於 LINE 客服、AI 自動回覆、Knowledge Base、CRM 與 SaaS 平台化。
          </p>
        </div>

        <div class="footer-col">
          <h4>產品</h4>
          <a href="#features">核心功能</a>
          <a href="#use-cases">應用場景</a>
          <a href="#faq">FAQ</a>
        </div>

        <div class="footer-col">
          <h4>帳號</h4>
          <router-link to="/login">登入</router-link>
          <router-link to="/register">註冊</router-link>
        </div>

        <div class="footer-col">
          <h4>聯絡</h4>
          <a href="mailto:service@serviceaicloud.com">service@serviceaicloud.com</a>
          <a href="tel:+886212345678">+886-2-8226-9997</a>
          <a href="#contact">聯絡表單</a>
        </div>
      </div>

      <div class="container footer-bottom">
        <span>© 2026 ServiceAI Cloud. All rights reserved.</span>
        <div class="footer-bottom-links">
          <a href="#">Privacy</a>
          <a href="#">Terms</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'


const { t, locale } = useI18n()

const contactSuccess = ref('')
const contactError = ref('')
const contactLoading = ref(false)

const contactForm = reactive({
  name: '',
  email: '',
  company: '',
  phone: '',
  message: '',
})



async function changeLocale(value) {
  locale.value = value
  localStorage.setItem('locale', value)

  const token = localStorage.getItem('token')
  if (token) {
    try {
      await api.put('/me/locale', { locale: value })
    } catch (e) {}
  }
}


async function submitContact() {
  contactSuccess.value = ''
  contactError.value = ''
  contactLoading.value = true

  try {
    const res = await api.post('/contact', {
      name: contactForm.name,
      email: contactForm.email,
      company: contactForm.company,
      phone: contactForm.phone,
      message: contactForm.message,
    })
    
    
    contactSuccess.value = res.data.message || t('landing.sent')
    Object.keys(contactForm).forEach((k) => {
      contactForm[k] = ''
    })

  } catch (error) {
    const data = error.response?.data
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      contactError.value = data.errors[firstKey][0]
    } else {
      contactError.value = data?.message || 'Error'
    }
  } finally {
    contactLoading.value = false
  }
}
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  color: #111827;
  background:
    radial-gradient(circle at top left, #eff6ff, transparent 26%),
    radial-gradient(circle at top right, #ecfeff, transparent 24%),
    #ffffff;
}

.container {
  width: min(1200px, calc(100% - 48px));
  margin: 0 auto;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 20;
  backdrop-filter: blur(10px);
  background: rgba(255,255,255,0.82);
  border-bottom: 1px solid rgba(229,231,235,0.8);
}

.topbar-inner {
  min-height: 78px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-logo {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #2563eb;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}

.brand-title {
  font-weight: 800;
}

.brand-sub {
  font-size: 12px;
  color: #6b7280;
}

.topnav {
  display: flex;
  align-items: center;
  gap: 20px;
}

.topnav a {
  text-decoration: none;
  color: #374151;
}

.login-link {
  padding: 10px 14px;
  border-radius: 10px;
  background: #111827;
  color: #fff !important;
}

.hero {
  padding: 48px 0 70px;
}

.hero-grid {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: 36px;
  align-items: center;
}

.hero-badge {
  display: inline-flex;
  background: #dbeafe;
  color: #1d4ed8;
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 16px;
}

.hero h1 {
  margin: 0 0 18px;
  font-size: 54px;
  line-height: 1.08;
  letter-spacing: -0.02em;
}

.hero-desc {
  font-size: 17px;
  line-height: 1.85;
  color: #4b5563;
  max-width: 700px;
}

.hero-actions {
  display: flex;
  gap: 12px;
  margin-top: 28px;
  flex-wrap: wrap;
}

.hero-actions.center {
  justify-content: center;
}

.primary-btn,
.ghost-btn {
  text-decoration: none;
  border-radius: 12px;
  padding: 13px 18px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.button-btn {
  border: 0;
  cursor: pointer;
}

.primary-btn {
  background: #2563eb;
  color: #fff;
}

.ghost-btn {
  background: #eef2f7;
  color: #111827;
}

.hero-points {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 28px;
}

.point-item {
  color: #374151;
}

.showcase-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
  overflow: hidden;
  border: 1px solid #eef2f7;
}

.showcase-header {
  padding: 14px 16px;
  border-bottom: 1px solid #eef2f7;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  display: inline-block;
  margin-right: 6px;
}
.red { background: #ef4444; }
.yellow { background: #f59e0b; }
.green { background: #10b981; }

.showcase-body {
  display: grid;
  grid-template-columns: 180px 1fr;
  min-height: 390px;
}

.showcase-sidebar {
  background: #111827;
  color: #fff;
  padding: 16px;
}

.side-item {
  padding: 10px 12px;
  border-radius: 10px;
  margin-bottom: 8px;
  color: rgba(255,255,255,0.78);
}

.side-item.active {
  background: #2563eb;
  color: #fff;
}

.showcase-main {
  padding: 18px;
  background: #f8fafc;
}

.mini-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.mini-stat {
  background: #fff;
  border-radius: 14px;
  padding: 14px;
}

.mini-label {
  font-size: 12px;
  color: #6b7280;
}

.mini-value {
  margin-top: 8px;
  font-size: 26px;
  font-weight: 800;
}

.chat-preview {
  margin-top: 16px;
  background: #fff;
  border-radius: 16px;
  padding: 16px;
  min-height: 220px;
}

.bubble {
  max-width: 85%;
  border-radius: 14px;
  padding: 10px 12px;
  margin-bottom: 10px;
  line-height: 1.6;
}

.bubble.left {
  background: #f3f4f6;
}

.bubble.right {
  background: #dbeafe;
  margin-left: auto;
}

.trust-strip {
  padding-bottom: 10px;
}

.trust-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.trust-item {
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.trust-item strong {
  display: block;
  margin-bottom: 8px;
}

.trust-item span {
  color: #6b7280;
  line-height: 1.7;
  font-size: 14px;
}

.section {
  padding: 76px 0;
}

.section.alt {
  background: #f8fafc;
}

.section-head {
  text-align: center;
  margin-bottom: 30px;
}

.section-head h2 {
  font-size: 36px;
  margin: 0 0 12px;
}

.section-head p {
  margin: 0;
  color: #6b7280;
}

.feature-grid,
.usecase-grid,
.steps-grid {
  display: grid;
  gap: 18px;
}

.feature-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.usecase-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.steps-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.feature-card,
.usecase-card,
.step-card,
.faq-item {
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.feature-card h3,
.usecase-card h3,
.step-card h3,
.faq-item h3 {
  margin-top: 0;
}

.feature-card p,
.usecase-card p,
.step-card p,
.faq-item p {
  color: #4b5563;
  line-height: 1.8;
}

.step-no {
  width: 38px;
  height: 38px;
  border-radius: 999px;
  background: #111827;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  margin-bottom: 14px;
}

.faq-list {
  display: grid;
  gap: 14px;
}

.contact-section {
  background: #f8fafc;
}

.contact-grid {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: 20px;
}

.contact-info-card,
.contact-form-card {
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.contact-info-card h3,
.contact-form-card h3 {
  margin-top: 0;
}

.contact-info-list {
  display: grid;
  gap: 16px;
}

.contact-info-item label {
  display: block;
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 6px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full {
  grid-column: span 2;
}

.form-group label {
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
}

.form-group input,
.form-group textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1px solid #d7dce5;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 14px;
  background: #fff;
}

.contact-actions {
  margin-top: 18px;
}

.success-box {
  margin-top: 16px;
  color: #15803d;
}

.cta-section {
  padding: 10px 0 80px;
}

.cta-card {
  background: linear-gradient(135deg, #111827, #1f2937);
  color: #fff;
  border-radius: 24px;
  padding: 44px 28px;
  text-align: center;
}

.cta-card p {
  color: rgba(255,255,255,0.78);
  line-height: 1.8;
}

.footer {
  border-top: 1px solid #eef2f7;
  background: #fff;
}

.footer-main {
  padding: 34px 0 22px;
  display: grid;
  grid-template-columns: 1.3fr 1fr 1fr 1fr;
  gap: 20px;
}

.footer-col {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-col h4 {
  margin: 0 0 6px;
}

.footer-col a {
  text-decoration: none;
  color: #374151;
}

.footer-brand {
  font-weight: 800;
  font-size: 18px;
}

.footer-text,
.footer-desc {
  color: #6b7280;
  line-height: 1.8;
}

.footer-bottom {
  padding: 18px 0 24px;
  border-top: 1px solid #eef2f7;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  color: #6b7280;
  font-size: 14px;
}

.footer-bottom-links {
  display: flex;
  gap: 14px;
}

.footer-bottom-links a {
  text-decoration: none;
  color: #6b7280;
}

@media (max-width: 1100px) {
  .hero-grid,
  .feature-grid,
  .usecase-grid,
  .steps-grid,
  .trust-grid,
  .contact-grid,
  .footer-main {
    grid-template-columns: 1fr;
  }

  .hero h1 {
    font-size: 42px;
  }
}

@media (max-width: 760px) {
  .container {
    width: min(1200px, calc(100% - 32px));
  }

  .topbar-inner {
    min-height: auto;
    padding: 14px 0;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }

  .topnav {
    flex-wrap: wrap;
    gap: 12px;
  }

  .hero {
    padding-top: 24px;
  }

  .hero h1 {
    font-size: 34px;
  }

  .hero-points,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.full {
    grid-column: span 1;
  }

  .showcase-body {
    grid-template-columns: 1fr;
  }

  .showcase-sidebar {
    display: none;
  }

  .mini-stats {
    grid-template-columns: 1fr;
  }

  .footer-bottom {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .error-box {
  margin-top: 16px;
  color: #dc2626;
}
  
}
</style>