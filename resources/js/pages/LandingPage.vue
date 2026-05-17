<template>
  <div class="landing-page">
    <header class="topbar">
      <div class="container topbar-inner">
        <div class="brand">
          <div class="brand-logo">S</div>
          <div class="brand-text">
            <div class="brand-title">ServiceAI Cloud</div>
            <div class="brand-sub">{{ $t('landing.brandSub') }}</div>
          </div>
        </div>

        <nav class="topnav">
          <a href="#features">{{ $t('nav.features') }}</a>
          <a href="#use-cases">{{ $t('nav.useCases') }}</a>
          <a href="#pricing">{{ landingCopy.pricingTitle }}</a>
          <a href="#faq">{{ $t('nav.faq') }}</a>
          <a href="#contact">{{ $t('nav.contact') }}</a>
          <router-link to="/login" class="login-link">{{ $t('nav.login') }}</router-link>

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
          <h1>{{ landingCopy.heroTitle }}</h1>
          <p class="hero-desc">{{ landingCopy.heroDesc }}</p>

          <div class="hero-actions">
            <router-link to="/register" class="primary-btn">{{ landingCopy.primaryCta }}</router-link>
            <a href="#contact" class="ghost-btn">{{ landingCopy.secondaryCta }}</a>
          </div>

          <div class="hero-points">
            <div v-for="point in landingCopy.heroPoints" :key="point" class="point-item">
              {{ point }}
            </div>
          </div>

          <p class="hero-proof">{{ landingCopy.heroProof }}</p>
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
                <div class="side-item">{{ $t('landing.showcase.conversations') }}</div>
                <div class="side-item">{{ $t('landing.showcase.customers') }}</div>
                <div class="side-item">{{ $t('landing.showcase.lineBot') }}</div>
                  <div class="side-item">{{ landingCopy.showcaseKnowledge }}</div>
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
                  <div class="bubble left">{{ landingCopy.previewQuestion1 }}</div>
                  <div class="bubble right">{{ landingCopy.previewAnswer1 }}</div>
                  <div class="bubble left">{{ landingCopy.previewQuestion2 }}</div>
                  <div class="bubble right">{{ landingCopy.previewAnswer2 }}</div>
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
          <strong>{{ landingCopy.trust[0][0] }}</strong>
          <span>{{ landingCopy.trust[0][1] }}</span>
        </div>
        <div class="trust-item">
          <strong>{{ landingCopy.trust[1][0] }}</strong>
          <span>{{ landingCopy.trust[1][1] }}</span>
        </div>
        <div class="trust-item">
          <strong>{{ landingCopy.trust[2][0] }}</strong>
          <span>{{ landingCopy.trust[2][1] }}</span>
        </div>
        <div class="trust-item">
          <strong>{{ landingCopy.trust[3][0] }}</strong>
          <span>{{ landingCopy.trust[3][1] }}</span>
        </div>
      </div>
    </section>

    <section class="section pain-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.painTitle }}</h2>
          <p>{{ landingCopy.painDesc }}</p>
        </div>

        <div class="pain-grid">
          <div class="pain-card">
            <h3>{{ landingCopy.repeatedQuestionsTitle }}</h3>
            <ul>
              <li v-for="item in landingCopy.repeatedQuestions" :key="item">{{ item }}</li>
            </ul>
          </div>

          <div class="pain-card alert">
            <h3>{{ landingCopy.painResultTitle }}</h3>
            <ul>
              <li v-for="item in landingCopy.painResults" :key="item">{{ item }}</li>
            </ul>
          </div>
        </div>

        <p class="pain-close">{{ landingCopy.painClose }}</p>
      </div>
    </section>

    <section class="section flow-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.flowTitle }}</h2>
          <p>{{ landingCopy.flowDesc }}</p>
        </div>

        <div class="flow-chain">
          <div v-for="(step, index) in landingCopy.flowSteps" :key="step" class="flow-step">
            <span>{{ index + 1 }}</span>
            <strong>{{ step }}</strong>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.featuresTitle }}</h2>
          <p>{{ landingCopy.featuresDesc }}</p>
        </div>

        <div class="feature-grid">
          <div v-for="feature in landingCopy.features" :key="feature[0]" class="feature-card">
            <h3>{{ feature[0] }}</h3>
            <p>{{ feature[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section metrics-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.metricsTitle }}</h2>
        </div>

        <div class="metrics-grid">
          <div v-for="metric in landingCopy.metrics" :key="metric[0]" class="metric-card">
            <strong>{{ metric[0] }}</strong>
            <span>{{ metric[1] }}</span>
          </div>
        </div>
      </div>
    </section>

    <section id="use-cases" class="section alt">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.industriesTitle }}</h2>
          <p>{{ landingCopy.industriesDesc }}</p>
        </div>

        <div class="usecase-grid">
          <div v-for="useCase in landingCopy.industries" :key="useCase[0]" class="usecase-card">
            <h3>{{ useCase[0] }}</h3>
            <p>{{ useCase[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section testimonials-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.testimonialsTitle }}</h2>
          <p>{{ landingCopy.testimonialsDesc }}</p>
        </div>

        <div class="testimonial-grid">
          <div v-for="item in landingCopy.testimonials" :key="item[0]" class="testimonial-card">
            <h3>{{ item[0] }}</h3>
            <p>{{ item[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section id="pricing" class="section pricing-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.pricingTitle }}</h2>
        </div>

        <div class="pricing-grid">
          <div v-for="plan in landingCopy.pricing" :key="plan.name" class="pricing-card">
            <h3>{{ plan.name }}</h3>
            <p>{{ plan.desc }}</p>
            <ul>
              <li v-for="feature in plan.features" :key="feature">{{ feature }}</li>
            </ul>
            <strong>{{ plan.price }}</strong>
          </div>
        </div>
      </div>
    </section>

    <section id="faq" class="section faq-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.faqTitle }}</h2>
          <p>{{ landingCopy.faqDesc }}</p>
        </div>

        <div class="faq-list">
          <div v-for="faq in landingCopy.faqs" :key="faq[0]" class="faq-item">
            <h3>{{ faq[0] }}</h3>
            <p>{{ faq[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="section contact-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ landingCopy.contactTitle }}</h2>
          <p>{{ landingCopy.contactDesc }}</p>
        </div>

        <div class="contact-grid">
          <div class="contact-info-card">
            <h3>{{ landingCopy.contactInfoTitle }}</h3>
            <div class="contact-info-list">
              <div class="contact-info-item">
                <label>{{ landingCopy.companyLabel }}</label>
                <div>{{ landingCopy.companyName }}</div>
              </div>
              <div class="contact-info-item">
                <label>{{ landingCopy.phoneLabel }}</label>
                <div>{{ landingCopy.phone }}</div>
              </div>
              <div class="contact-info-item">
                <label>Email</label>
                <div>{{ landingCopy.email }}</div>
              </div>
              <div class="contact-info-item">
                <label>LINE</label>
                <div>{{ landingCopy.line }}</div>
              </div>
            </div>
          </div>

          <div class="contact-form-card">
            <h3>{{ landingCopy.contactFormTitle }}</h3>

            <form @submit.prevent="submitContact">
              <div class="form-grid">
                <div class="form-group">
                  <label>{{ $t('form.name') }}</label>
                  <input v-model="contactForm.name" type="text" :placeholder="$t('form.namePlaceholder')" />
                </div>

                <div class="form-group">
                  <label>{{ $t('form.email') }}</label>
                  <input v-model="contactForm.email" type="email" :placeholder="$t('form.emailPlaceholder')" />
                </div>

                <div class="form-group">
                  <label>{{ $t('form.company') }}</label>
                  <input v-model="contactForm.company" type="text" :placeholder="$t('form.companyPlaceholder')" />
                </div>

                <div class="form-group">
                  <label>{{ $t('form.phone') }}</label>
                  <input v-model="contactForm.phone" type="text" :placeholder="$t('form.phonePlaceholder')" />
                </div>

                <div class="form-group full">
                  <label>{{ $t('form.message') }}</label>
                  <textarea v-model="contactForm.message" rows="5" :placeholder="$t('form.messagePlaceholder')" />
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
                  {{ contactLoading ? $t('form.submitting') : $t('form.submitContact') }}
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
          <h2>{{ landingCopy.ctaTitle }}</h2>
          <p>{{ landingCopy.ctaDesc }}</p>

          <div class="hero-actions center">
            <router-link to="/register" class="primary-btn">{{ landingCopy.primaryCta }}</router-link>
            <a href="#contact" class="ghost-btn">{{ landingCopy.secondaryCta }}</a>
          </div>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="container footer-main">
        <div class="footer-col brand-col">
          <div class="footer-brand">{{ landingCopy.companyName }}</div>
          <p class="footer-text">{{ $t('landing.footerText') }}</p>
          <p class="footer-desc">{{ $t('landing.footerDesc') }}</p>
        </div>

        <div class="footer-col">
          <h4>{{ $t('landing.product') }}</h4>
          <a href="#features">{{ $t('nav.features') }}</a>
          <a href="#use-cases">{{ $t('nav.useCases') }}</a>
          <a href="#faq">{{ $t('nav.faq') }}</a>
        </div>

        <div class="footer-col">
          <h4>{{ $t('landing.account') }}</h4>
          <router-link to="/login">{{ $t('nav.login') }}</router-link>
          <router-link to="/register">{{ $t('nav.register') }}</router-link>
        </div>

        <div class="footer-col">
          <h4>{{ $t('nav.contact') }}</h4>
          <a :href="`tel:${landingCopy.phone}`">{{ landingCopy.phone }}</a>
          <a :href="`mailto:${landingCopy.email}`">{{ landingCopy.email }}</a>
          <a href="https://line.me/R/ti/p/@156ypxij" target="_blank" rel="noopener">{{ landingCopy.line }}</a>
          <a href="#contact">{{ landingCopy.contactFormTitle }}</a>
        </div>
      </div>

      <div class="container footer-bottom">
        <span>© 2026 {{ landingCopy.companyName }}. All rights reserved.</span>
        <div class="footer-bottom-links">
          <a href="#">{{ $t('common.privacy') }}</a>
          <a href="#">{{ $t('common.terms') }}</a>
          <a href="/up">{{ landingCopy.systemStatus }}</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../api'

const { t, locale } = useI18n()

const landingCopies = {
  zh_TW: {
    heroTitle: 'AI 客服幫你 24 小時回覆 LINE 客戶',
    heroDesc: '整合 LINE 官方帳號、AI 知識庫、CRM 與真人接手，讓企業減少重複客服工作、加快回覆速度、提升成交率。',
    primaryCta: '立即免費試用',
    secondaryCta: '預約 Demo',
    heroPoints: ['5 分鐘完成設定', 'AI 自動學習知識庫', '真人客服隨時接手', '多租戶 SaaS 架構'],
    heroProof: '已有餐廳、教育、零售與服務業導入 AI 客服流程',
    contactTitle: '聯絡我們',
    contactDesc: '留下公司與需求資訊，我們會協助你評估適合的 AI 客服導入方式。',
    contactInfoTitle: '聯絡資訊',
    contactFormTitle: '聯絡表單',
    companyLabel: '公司名稱',
    companyName: '長城數位科技有限公司',
    phoneLabel: '聯絡電話',
    phone: '886-2-82269997',
    email: 'service@greatsoft.com.tw',
    line: 'LINE: @156ypxij',
    systemStatus: '系統狀態',
    showcaseKnowledge: '知識庫',
    previewQuestion1: '請問今天營業到幾點？',
    previewAnswer1: '您好，我們今天營業到 18:00，也可以直接協助您預約。',
    previewQuestion2: '可以幫我查訂單進度嗎？',
    previewAnswer2: '可以，請提供訂單編號，我會協助查詢出貨狀態。',
    trust: [['AI 自動回覆', '依企業知識快速回答'], ['CRM', '完整保存客戶紀錄'], ['真人接手', 'AI 不確定時立即轉人工'], ['SaaS 架構', '支援多租戶與權限']],
    painTitle: '客服訊息越來越多，但人力沒有增加？',
    painDesc: '每天都在重複回答相同問題，回覆慢、訓練難，下班後也容易漏接商機。',
    repeatedQuestionsTitle: '每天都在重複回答',
    repeatedQuestions: ['營業時間是幾點？', '怎麼預約？', '價格多少？', '可以刷卡嗎？', '有停車位嗎？', '出貨到哪了？'],
    painResultTitle: '結果通常是',
    painResults: ['客服回覆慢', '新人訓練困難', '下班後沒人回覆', '重複問題浪費時間'],
    painClose: 'ServiceAI Cloud 幫你把這些工作交給 AI。',
    flowTitle: 'AI 如何運作',
    flowDesc: '24 小時不中斷，自動學習企業知識。',
    flowSteps: ['客戶 LINE 發問', 'AI 判斷問題內容', '知識庫搜尋答案', '自動產生回覆', '真人客服接手', 'CRM 紀錄保存'],
    featuresTitle: '一套系統完成 AI 客服流程',
    featuresDesc: '從 LINE 對話、AI 回覆、真人接手到 CRM 紀錄，集中在同一個後台完成。',
    features: [
      ['AI 自動回覆', '依照企業知識庫內容，自動回答客戶問題。'],
      ['知識庫', '上傳 PDF、Word、FAQ，快速建立 AI 知識庫。'],
      ['真人客服接手', 'AI 不確定時，自動轉真人處理。'],
      ['CRM 客戶管理', '完整保存客戶對話與歷史紀錄。'],
      ['多租戶 SaaS', '支援企業獨立空間與權限管理。'],
    ],
    metricsTitle: '導入後可改善',
    metrics: [['70%', '重複客服問題自動化'], ['50%', '減少客服人力時間'], ['24hr', '全天候 AI 回覆'], ['5 秒內', '平均回應速度']],
    industriesTitle: '適用產業',
    industriesDesc: '不同產業都能用 AI 先回答高頻問題，再交由真人處理複雜情境。',
    industries: [
      ['餐廳', 'AI 回答：訂位、菜單、停車、外送。'],
      ['診所', 'AI 回答：掛號、門診時間、疫苗資訊。'],
      ['電商', 'AI 回答：訂單查詢、出貨進度、商品問題。'],
      ['教育', 'AI 回答：課程、報名、上課資訊。'],
    ],
    pricingTitle: '價格方案',
    pricing: [
      { name: '免費', desc: '免費開始體驗', features: ['1 LINE', 'CRM', '知識庫', '真人接手', '上傳一個 5 MB 檔案做訓練', '每日訊息數 250 則'], price: 'NT$0/月' },
      { name: '基礎', desc: '適合小型店家', features: ['1 LINE', 'CRM', '知識庫', '真人接手', '上傳 2 個 10 MB 檔案做訓練', '每日訊息數 1000 則'], price: 'NT$199/月' },
      { name: '專業', desc: '適合成長企業', features: ['1 LINE', 'CRM', '知識庫', '真人接手', '上傳 5 個 10 MB 檔案做訓練', '每日訊息數 5000 則'], price: 'NT$399/月' },
      { name: '企業版', desc: '客製部署', features: ['多租戶架構', '進階權限', '客製流程', '知識庫訓練', '系統建置', 'AI MODEL'], price: '聯絡我們' },
    ],
    faqTitle: 'FAQ',
    faqDesc: '導入 AI 客服前常見問題。',
    faqs: [
      ['AI 如何學習資料？', '上傳 PDF、Word、FAQ 即可建立知識庫。'],
      ['可以接 LINE 官方帳號嗎？', '可以。'],
      ['AI 不知道答案怎麼辦？', '自動轉真人。'],
      ['可以多人客服嗎？', '支援。'],
      ['支援多公司嗎？', '支援 SaaS 多租戶。'],
    ],
    ctaTitle: '開始建立你的 AI 客服流程',
    ctaDesc: '從 LINE 串接、知識庫建立到客服後台，幾步就能開始。',
  },
  en: {
    heroTitle: 'AI support that replies to LINE customers 24/7',
    heroDesc: 'Connect LINE Official Account, AI knowledge base, CRM, and human handoff so your team can reduce repeated work, reply faster, and convert more leads.',
    primaryCta: 'Start Free',
    secondaryCta: 'Book a Demo',
    heroPoints: ['Set up in 5 minutes', 'AI learns from your knowledge base', 'Human handoff anytime', 'Multi-tenant SaaS architecture'],
    heroProof: 'Restaurants, education, retail, and service teams are adopting AI support workflows.',
    contactTitle: 'Contact Us',
    contactDesc: 'Leave your company and requirement details. We will help evaluate the right AI support setup.',
    contactInfoTitle: 'Contact Information',
    contactFormTitle: 'Contact Form',
    companyLabel: 'Company',
    companyName: 'Greatsoft Digital Technology Co., Ltd.',
    phoneLabel: 'Phone',
    phone: '886-2-82269997',
    email: 'service@greatsoft.com.tw',
    line: 'LINE: @156ypxij',
    systemStatus: 'System Status',
    showcaseKnowledge: 'Knowledge Base',
    previewQuestion1: 'What time are you open today?',
    previewAnswer1: 'We are open until 18:00 today. I can also help you book a time.',
    previewQuestion2: 'Can you check my order status?',
    previewAnswer2: 'Yes. Please provide your order number and I will check the shipment status.',
    trust: [['AI Replies', 'Answer from business knowledge'], ['CRM', 'Keep customer history'], ['Human Handoff', 'Escalate when AI is unsure'], ['SaaS', 'Multi-tenant permissions']],
    painTitle: 'More support messages, but no more staff?',
    painDesc: 'Teams repeat the same answers every day, respond slowly, and miss leads after hours.',
    repeatedQuestionsTitle: 'Repeated questions',
    repeatedQuestions: ['Business hours?', 'How do I book?', 'How much is it?', 'Can I pay by card?', 'Is parking available?', 'Where is my shipment?'],
    painResultTitle: 'The result',
    painResults: ['Slow replies', 'Harder onboarding', 'No after-hours support', 'Time wasted on repeated questions'],
    painClose: 'ServiceAI Cloud lets AI handle this workload.',
    flowTitle: 'How AI Works',
    flowDesc: 'Always-on support that learns from your business knowledge.',
    flowSteps: ['Customer asks on LINE', 'AI classifies intent', 'Search knowledge base', 'Generate reply', 'Human handoff', 'Save CRM record'],
    featuresTitle: 'One Platform for AI Customer Support',
    featuresDesc: 'Manage LINE conversations, AI replies, human handoff, and CRM records from one console.',
    features: [
      ['AI Auto-Replies', 'Answer customer questions from your business knowledge base.'],
      ['Knowledge Base', 'Upload PDF, Word, and FAQ files to build AI knowledge quickly.'],
      ['Human Handoff', 'Escalate to a human agent when AI is uncertain.'],
      ['CRM Customer Management', 'Keep customer conversations and history in one place.'],
      ['Multi-Tenant SaaS', 'Support separate workspaces and permission management.'],
    ],
    metricsTitle: 'Expected Impact',
    metrics: [['70%', 'Repeated questions automated'], ['50%', 'Less manual support time'], ['24hr', 'Always-on AI replies'], ['Under 5s', 'Average response speed']],
    industriesTitle: 'Use Cases',
    industriesDesc: 'AI answers frequent questions first, then hands complex cases to your team.',
    industries: [
      ['Restaurants', 'Bookings, menus, parking, delivery.'],
      ['Clinics', 'Registration, opening hours, vaccine information.'],
      ['E-commerce', 'Order lookup, shipping progress, product questions.'],
      ['Education', 'Courses, registration, class information.'],
    ],
    pricingTitle: 'Pricing',
    pricing: [
      { name: 'Free', desc: 'Start for free', features: ['1 LINE', 'CRM', 'Knowledge Base', 'Human handoff', 'Upload one 5 MB file for training', '500 messages per day'], price: 'NT$0/mo' },
      { name: 'Basic', desc: 'For small shops', features: ['1 LINE', 'CRM', 'Knowledge Base', 'Human handoff', 'Upload two 10 MB files for training', '5000 messages per day'], price: 'NT$199/mo' },
      { name: 'Pro', desc: 'For growing teams', features: ['1 LINE', 'CRM', 'Knowledge Base', 'Human handoff', 'Upload five 10 MB files for training', '20000 messages per day'], price: 'NT$399/mo' },
      { name: 'Enterprise', desc: 'Custom deployment', features: ['Multi-tenant setup', 'Advanced permissions', 'Custom workflow', 'Knowledge base training', 'System implementation', 'AI MODEL'], price: 'Contact us' },
    ],
    faqTitle: 'FAQ',
    faqDesc: 'Common questions before adopting AI customer support.',
    faqs: [
      ['How does AI learn?', 'Upload PDF, Word, and FAQ files to build the knowledge base.'],
      ['Can it connect to LINE Official Account?', 'Yes, LINE Messaging API integration is supported.'],
      ['What if AI does not know the answer?', 'It can hand off to a human support agent.'],
      ['Can multiple agents use it?', 'Yes, multiple agents and permission management are supported.'],
      ['Does it support multiple companies?', 'Yes, it supports SaaS multi-tenant architecture.'],
    ],
    ctaTitle: 'Start Building Your AI Support Workflow',
    ctaDesc: 'Connect LINE, build the knowledge base, and start using the support console in a few steps.',
  },
}

const testimonialDefaults = {
  zh_TW: {
    testimonialsTitle: '客戶見證',
    testimonialsDesc: '測試案例可先作為導入效益參考，避免頁面空白。',
    testimonials: [['某餐飲品牌', '導入後客服工作量下降 65%。'], ['某教育機構', 'AI 自動回答率達 78%。']],
  },
  en: {
    testimonialsTitle: 'Customer Results',
    testimonialsDesc: 'Pilot examples for implementation reference.',
    testimonials: [['Restaurant brand', 'Support workload decreased by 65% after rollout.'], ['Education provider', 'AI auto-answer rate reached 78%.']],
  },
}

const landingCopy = computed(() => {
  const selected = landingCopies[locale.value] || landingCopies.zh_TW
  const defaults = testimonialDefaults[locale.value] || testimonialDefaults.zh_TW
  return { ...defaults, ...selected }
})

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
    Object.keys(contactForm).forEach((key) => {
      contactForm[key] = ''
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
  background: rgba(255, 255, 255, 0.82);
  border-bottom: 1px solid rgba(229, 231, 235, 0.8);
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
  border-radius: 8px;
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
  border-radius: 8px;
  background: #111827;
  color: #fff !important;
}

.locale-select {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 8px 10px;
  background: #fff;
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

.hero h1 {
  margin: 0 0 18px;
  font-size: 54px;
  line-height: 1.08;
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
  border-radius: 8px;
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

.point-item::before {
  content: "✓ ";
  color: #16a34a;
  font-weight: 800;
}

.showcase-card {
  background: #fff;
  border-radius: 8px;
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
  border-radius: 8px;
  margin-bottom: 8px;
  color: rgba(255, 255, 255, 0.78);
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
  border-radius: 8px;
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
  border-radius: 8px;
  padding: 16px;
  min-height: 220px;
}

.bubble {
  max-width: 85%;
  border-radius: 8px;
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

.trust-grid,
.feature-grid,
.usecase-grid,
.steps-grid,
.pain-grid,
.metrics-grid,
.testimonial-grid,
.pricing-grid {
  display: grid;
  gap: 18px;
}

.trust-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.feature-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.usecase-grid,
.steps-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.pain-grid,
.testimonial-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.metrics-grid,
.pricing-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.trust-item,
.feature-card,
.usecase-card,
.step-card,
.faq-item,
.pain-card,
.metric-card,
.testimonial-card,
.pricing-card,
.contact-info-card,
.contact-form-card {
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 8px;
  padding: 22px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.trust-item strong {
  display: block;
  margin-bottom: 8px;
}

.pain-card ul,
.pricing-card ul {
  margin: 0;
  padding-left: 18px;
  color: #4b5563;
  line-height: 1.9;
}

.pain-card.alert {
  border-color: #fecaca;
  background: #fffafa;
}

.pain-close {
  margin: 18px 0 0;
  text-align: center;
  font-weight: 700;
  color: #111827;
}

.flow-chain {
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 12px;
}

.flow-step {
  background: #111827;
  color: #fff;
  border-radius: 8px;
  padding: 18px 14px;
  min-height: 96px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  justify-content: center;
}

.flow-step span {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}

.metric-card strong {
  display: block;
  font-size: 34px;
  color: #2563eb;
  margin-bottom: 8px;
}

.metric-card span,
.testimonial-card p,
.pricing-card p {
  color: #4b5563;
  line-height: 1.8;
}

.pricing-card strong {
  display: block;
  margin-top: 18px;
  font-size: 22px;
  color: #111827;
}

.trust-item span,
.feature-card p,
.usecase-card p,
.step-card p,
.faq-item p {
  color: #4b5563;
  line-height: 1.8;
}

.section {
  padding: 76px 0;
}

.section.alt,
.contact-section {
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

.feature-card h3,
.usecase-card h3,
.step-card h3,
.faq-item h3 {
  margin-top: 0;
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

.contact-grid {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: 20px;
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
  border-radius: 8px;
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

.error-box {
  margin-top: 16px;
  color: #dc2626;
}

.cta-section {
  padding: 10px 0 80px;
}

.cta-card {
  background: linear-gradient(135deg, #111827, #1f2937);
  color: #fff;
  border-radius: 8px;
  padding: 44px 28px;
  text-align: center;
}

.cta-card p {
  color: rgba(255, 255, 255, 0.78);
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
  .pain-grid,
  .flow-chain,
  .metrics-grid,
  .testimonial-grid,
  .pricing-grid,
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
}
</style>
