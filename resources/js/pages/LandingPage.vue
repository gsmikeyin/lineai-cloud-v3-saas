<template>
  <div class="landing-page">
    <header class="topbar">
      <div class="brand">
        <div class="brand-logo">L</div>
        <div class="brand-text">
          <div class="brand-title">LineAI Cloud</div>
          <div class="brand-sub">LINE AI SaaS Platform</div>
        </div>
      </div>

      <nav class="topnav">
        <a href="#features">功能</a>
        <a href="#how-it-works">流程</a>
        <a href="#pricing">方案</a>
        <router-link to="/login" class="login-link">登入</router-link>
      </nav>
    </header>

    <section class="hero">
      <div class="hero-left">
        <div class="hero-badge">LINE + AI + CRM + SaaS</div>
        <h1>把 LINE 客服升級成<br />可營運的 AI 客服平台</h1>
        <p class="hero-desc">
          整合 LINE 官方帳號、AI 自動回覆、知識庫、真人接手與 CRM，
          讓你的客服系統從聊天工具升級成真正可管理、可成長、可商業化的平台。
        </p>

        <div class="hero-actions">
          <router-link to="/register" class="primary-btn">
            立即開始
          </router-link>
          <router-link to="/login" class="ghost-btn">
            前往登入
          </router-link>
        </div>

        <div class="hero-points">
          <div>✓ AI 自動回覆</div>
          <div>✓ 真人接手</div>
          <div>✓ Knowledge Base</div>
          <div>✓ 多租戶 SaaS 架構</div>
        </div>
      </div>

      <div class="hero-right">
        <div class="mockup-card">
          <div class="mockup-header">
            <span class="dot red"></span>
            <span class="dot yellow"></span>
            <span class="dot green"></span>
          </div>

          <div class="mockup-body">
            <div class="mockup-sidebar">
              <div class="mockup-menu active">Dashboard</div>
              <div class="mockup-menu">Conversations</div>
              <div class="mockup-menu">Customers</div>
              <div class="mockup-menu">Knowledge Base</div>
              <div class="mockup-menu">LINE Bot 設定</div>
            </div>

            <div class="mockup-content">
              <div class="stat-row">
                <div class="stat-box">
                  <div class="label">Conversations</div>
                  <div class="value">1,280</div>
                </div>
                <div class="stat-box">
                  <div class="label">Unread</div>
                  <div class="value">24</div>
                </div>
              </div>

              <div class="chat-box">
                <div class="bubble left">請問你們幾點營業？</div>
                <div class="bubble right">您好，我們營業時間為週一到週五 09:00–18:00。</div>
                <div class="bubble left">可以退貨嗎？</div>
                <div class="bubble right">收到商品 7 天內可申請退貨，我也可以為您轉接客服。</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="section">
      <div class="section-head">
        <h2>核心功能</h2>
        <p>不是只有聊天，而是一整套可營運的 LINE AI 客服系統。</p>
      </div>

      <div class="feature-grid">
        <div class="feature-card">
          <h3>AI 自動回覆</h3>
          <p>先命中 FAQ，再由 AI 補充回覆，提升回覆速度與一致性。</p>
        </div>

        <div class="feature-card">
          <h3>真人接手</h3>
          <p>遇到複雜需求時可一鍵切換人工客服，不讓客戶卡住。</p>
        </div>

        <div class="feature-card">
          <h3>Knowledge Base</h3>
          <p>把營業時間、退換貨政策、商品知識集中管理，持續優化 AI 回答品質。</p>
        </div>

        <div class="feature-card">
          <h3>Customer CRM</h3>
          <p>查看客戶資料、互動紀錄、標籤、備註與歷史訊息。</p>
        </div>

        <div class="feature-card">
          <h3>後台聊天室</h3>
          <p>對話列表、未讀數、人工模式、指派客服，接近正式客服中心操作體驗。</p>
        </div>

        <div class="feature-card">
          <h3>SaaS 架構</h3>
          <p>支援多租戶、單 Bot 設定、權限管理與後續商業化擴充。</p>
        </div>
      </div>
    </section>

    <section id="how-it-works" class="section alt">
      <div class="section-head">
        <h2>運作流程</h2>
        <p>從 LINE 訊息進站，到 AI / 客服回覆，一條龍完成。</p>
      </div>

      <div class="steps">
        <div class="step-card">
          <div class="step-no">1</div>
          <h3>客戶傳訊到 LINE</h3>
          <p>LINE Webhook 進入系統，建立客戶、對話、訊息資料。</p>
        </div>

        <div class="step-card">
          <div class="step-no">2</div>
          <h3>知識庫優先命中</h3>
          <p>先用 FAQ / Policy / Product 關鍵字快速命中，提升準確率。</p>
        </div>

        <div class="step-card">
          <div class="step-no">3</div>
          <h3>AI 生成回覆</h3>
          <p>若無直接命中，就用 AI + Knowledge Context 生成專業回覆。</p>
        </div>

        <div class="step-card">
          <div class="step-no">4</div>
          <h3>必要時轉真人</h3>
          <p>客服可在後台即時接手，確保高價值客戶得到更完整服務。</p>
        </div>
      </div>
    </section>

    <section id="pricing" class="section">
      <div class="section-head">
        <h2>適合的使用情境</h2>
        <p>中小企業、電商、服務業、品牌商，都可快速導入。</p>
      </div>

      <div class="pricing-grid">
        <div class="pricing-card">
          <h3>Starter</h3>
          <p>適合剛開始導入 LINE AI 客服的團隊。</p>
          <ul>
            <li>單一 LINE Bot</li>
            <li>基本 Knowledge Base</li>
            <li>AI 自動回覆</li>
          </ul>
        </div>

        <div class="pricing-card featured">
          <h3>Growth</h3>
          <p>適合有客服人員與成長需求的企業。</p>
          <ul>
            <li>真人接手</li>
            <li>Customer CRM</li>
            <li>Dashboard 與統計</li>
          </ul>
        </div>

        <div class="pricing-card">
          <h3>Enterprise</h3>
          <p>適合需要深度整合與擴充能力的團隊。</p>
          <ul>
            <li>多語系</li>
            <li>進階權限與流程</li>
            <li>可擴充 SaaS 架構</li>
          </ul>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="cta-card">
        <h2>準備好把 LINE 客服升級成 AI SaaS 平台了嗎？</h2>
        <p>建立帳號後即可開始設定 LINE Bot、Knowledge Base 與客服後台。</p>

        <div class="hero-actions center">
          <router-link to="/register" class="primary-btn">
            免費開始
          </router-link>
          <router-link to="/login" class="ghost-btn">
            立即登入
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, #eff6ff, transparent 30%),
    radial-gradient(circle at top right, #ecfeff, transparent 30%),
    #ffffff;
  color: #111827;
}

.topbar {
  max-width: 1200px;
  margin: 0 auto;
  padding: 22px 24px;
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
  max-width: 1200px;
  margin: 0 auto;
  padding: 48px 24px 70px;
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
  font-size: 52px;
  line-height: 1.1;
}

.hero-desc {
  font-size: 17px;
  line-height: 1.8;
  color: #4b5563;
  max-width: 680px;
}

.hero-actions {
  display: flex;
  gap: 12px;
  margin-top: 26px;
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
  color: #374151;
}

.mockup-card {
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
  overflow: hidden;
  border: 1px solid #eef2f7;
}

.mockup-header {
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

.mockup-body {
  display: grid;
  grid-template-columns: 180px 1fr;
  min-height: 360px;
}

.mockup-sidebar {
  background: #111827;
  color: #fff;
  padding: 16px;
}

.mockup-menu {
  padding: 10px 12px;
  border-radius: 10px;
  margin-bottom: 8px;
  color: rgba(255,255,255,0.78);
}

.mockup-menu.active {
  background: #2563eb;
  color: #fff;
}

.mockup-content {
  padding: 18px;
  background: #f8fafc;
}

.stat-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.stat-box {
  background: #fff;
  border-radius: 14px;
  padding: 14px;
}

.stat-box .label {
  font-size: 12px;
  color: #6b7280;
}

.stat-box .value {
  margin-top: 8px;
  font-size: 28px;
  font-weight: 800;
}

.chat-box {
  margin-top: 16px;
  background: #fff;
  border-radius: 16px;
  padding: 16px;
  min-height: 210px;
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

.section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 70px 24px;
}

.section.alt {
  background: #f8fafc;
  border-radius: 28px;
}

.section-head {
  text-align: center;
  margin-bottom: 28px;
}

.section-head h2 {
  font-size: 34px;
  margin: 0 0 12px;
}

.section-head p {
  margin: 0;
  color: #6b7280;
}

.feature-grid,
.pricing-grid,
.steps {
  display: grid;
  gap: 18px;
}

.feature-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.pricing-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.steps {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.feature-card,
.pricing-card,
.step-card {
  background: #fff;
  border: 1px solid #eef2f7;
  border-radius: 18px;
  padding: 22px;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
}

.featured {
  border-color: #93c5fd;
  box-shadow: 0 20px 40px rgba(37, 99, 235, 0.12);
}

.feature-card h3,
.pricing-card h3,
.step-card h3 {
  margin-top: 0;
}

.feature-card p,
.pricing-card p,
.step-card p,
.pricing-card li {
  color: #4b5563;
  line-height: 1.8;
}

.pricing-card ul {
  padding-left: 18px;
  margin-bottom: 0;
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

.cta-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 10px 24px 80px;
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

@media (max-width: 1100px) {
  .hero,
  .feature-grid,
  .pricing-grid,
  .steps {
    grid-template-columns: 1fr;
  }

  .hero h1 {
    font-size: 40px;
  }
}

@media (max-width: 760px) {
  .topbar {
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

  .hero-points {
    grid-template-columns: 1fr;
  }

  .mockup-body {
    grid-template-columns: 1fr;
  }

  .mockup-sidebar {
    display: none;
  }
}
</style>