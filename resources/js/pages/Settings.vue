<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>{{ $t('adminPages.settings.title') }}</h2>
        <p>{{ $t('adminPages.settings.desc') }}</p>
      </div>
    </div>

    <div class="setting-grid">
      <div class="setting-item">
        <label>{{ $t('adminPages.settings.systemName') }}</label>
        <input value="ServiceAI Cloud" readonly />
      </div>

      <div class="setting-item">
        <label>{{ $t('adminPages.settings.tenantMode') }}</label>
        <input value="Single Tenant / Single LINE Bot" readonly />
      </div>

      <div class="setting-item">
        <label>{{ $t('adminPages.settings.timezone') }}</label>
        <input value="Asia/Taipei" readonly />
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-card { background:#fff; border-radius:8px; padding:24px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.page-head { margin-bottom:20px; }
.page-head h2 { margin:0 0 6px; }
.page-head p { margin:0; color:#6b7280; }
.setting-grid { display:grid; gap:16px; }
.setting-item label { display:block; margin-bottom:8px; font-size:14px; font-weight:600; }
.setting-item input { width:100%; box-sizing:border-box; border:1px solid #d7dce5; border-radius:8px; padding:12px 14px; background:#f9fafb; }
</style>
