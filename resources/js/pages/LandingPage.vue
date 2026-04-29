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
          <div class="hero-badge">{{ $t('landing.badge') }}</div>
          <h1>{{ $t('landing.heroTitle1') }}<br />{{ $t('landing.heroTitle2') }}</h1>
          <p class="hero-desc">{{ $t('landing.heroDesc') }}</p>

          <div class="hero-actions">
            <router-link to="/register" class="primary-btn">{{ $t('landing.startFree') }}</router-link>
            <router-link to="/login" class="ghost-btn">{{ $t('landing.goLogin') }}</router-link>
          </div>

          <div class="hero-points">
            <div v-for="point in $tm('landing.points')" :key="point" class="point-item">
              {{ point }}
            </div>
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
                <div class="side-item">{{ $t('landing.showcase.conversations') }}</div>
                <div class="side-item">{{ $t('landing.showcase.customers') }}</div>
                <div class="side-item">{{ $t('landing.showcase.lineBot') }}</div>
                <div class="side-item">{{ $t('landing.showcase.knowledge') }}</div>
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
                  <div class="bubble left">{{ $t('landing.showcase.customerQuestion1') }}</div>
                  <div class="bubble right">{{ $t('landing.showcase.agentAnswer1') }}</div>
                  <div class="bubble left">{{ $t('landing.showcase.customerQuestion2') }}</div>
                  <div class="bubble right">{{ $t('landing.showcase.agentAnswer2') }}</div>
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
          <strong>{{ $t('landing.trust.aiTitle') }}</strong>
          <span>{{ $t('landing.trust.aiDesc') }}</span>
        </div>
        <div class="trust-item">
          <strong>{{ $t('landing.trust.crmTitle') }}</strong>
          <span>{{ $t('landing.trust.crmDesc') }}</span>
        </div>
        <div class="trust-item">
          <strong>{{ $t('landing.trust.supportTitle') }}</strong>
          <span>{{ $t('landing.trust.supportDesc') }}</span>
        </div>
        <div class="trust-item">
          <strong>{{ $t('landing.trust.saasTitle') }}</strong>
          <span>{{ $t('landing.trust.saasDesc') }}</span>
        </div>
      </div>
    </section>

    <section id="features" class="section">
      <div class="container">
        <div class="section-head">
          <h2>{{ $t('landing.featuresTitle') }}</h2>
          <p>{{ $t('landing.featuresDesc') }}</p>
        </div>

        <div class="feature-grid">
          <div v-for="feature in $tm('landing.features')" :key="feature[0]" class="feature-card">
            <h3>{{ feature[0] }}</h3>
            <p>{{ feature[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section id="use-cases" class="section alt">
      <div class="container">
        <div class="section-head">
          <h2>{{ $t('landing.useCasesTitle') }}</h2>
          <p>{{ $t('landing.useCasesDesc') }}</p>
        </div>

        <div class="usecase-grid">
          <div v-for="useCase in $tm('landing.useCases')" :key="useCase[0]" class="usecase-card">
            <h3>{{ useCase[0] }}</h3>
            <p>{{ useCase[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <h2>{{ $t('landing.processTitle') }}</h2>
          <p>{{ $t('landing.processDesc') }}</p>
        </div>

        <div class="steps-grid">
          <div v-for="(step, index) in $tm('landing.steps')" :key="step[0]" class="step-card">
            <div class="step-no">{{ index + 1 }}</div>
            <h3>{{ step[0] }}</h3>
            <p>{{ step[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section id="faq" class="section faq-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ $t('landing.faqTitle') }}</h2>
          <p>{{ $t('landing.faqDesc') }}</p>
        </div>

        <div class="faq-list">
          <div v-for="faq in $tm('landing.faqs')" :key="faq[0]" class="faq-item">
            <h3>{{ faq[0] }}</h3>
            <p>{{ faq[1] }}</p>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="section contact-section">
      <div class="container">
        <div class="section-head">
          <h2>{{ $t('landing.contactTitle') }}</h2>
          <p>{{ $t('landing.contactDesc') }}</p>
        </div>

        <div class="contact-grid">
          <div class="contact-info-card">
            <h3>{{ $t('landing.contactInfoTitle') }}</h3>
            <div class="contact-info-list">
              <div class="contact-info-item">
                <label>Email</label>
                <div>service@serviceaicloud.com</div>
              </div>
              <div class="contact-info-item">
                <label>{{ $t('landing.serviceHours') }}</label>
                <div>{{ $t('landing.serviceHoursValue') }}</div>
              </div>
              <div class="contact-info-item">
                <label>{{ $t('landing.cooperation') }}</label>
                <div>{{ $t('landing.cooperationValue') }}</div>
              </div>
            </div>
          </div>

          <div class="contact-form-card">
            <h3>{{ $t('landing.contactFormTitle') }}</h3>

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
          <h2>{{ $t('landing.ctaTitle') }}</h2>
          <p>{{ $t('landing.ctaDesc') }}</p>

          <div class="hero-actions center">
            <router-link to="/register" class="primary-btn">{{ $t('landing.startFree') }}</router-link>
            <router-link to="/login" class="ghost-btn">{{ $t('landing.goLogin') }}</router-link>
          </div>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="container footer-main">
        <div class="footer-col brand-col">
          <div class="footer-brand">ServiceAI Cloud</div>
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
          <a href="mailto:service@serviceaicloud.com">service@serviceaicloud.com</a>
          <a href="#contact">{{ $t('landing.contactFormTitle') }}</a>
        </div>
      </div>

      <div class="container footer-bottom">
        <span>© 2026 ServiceAI Cloud. All rights reserved.</span>
        <div class="footer-bottom-links">
          <a href="#">{{ $t('common.privacy') }}</a>
          <a href="#">{{ $t('common.terms') }}</a>
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
.steps-grid {
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

.trust-item,
.feature-card,
.usecase-card,
.step-card,
.faq-item,
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
