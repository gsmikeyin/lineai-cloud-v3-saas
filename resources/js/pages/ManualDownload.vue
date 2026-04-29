<template>
  <div class="manual-page">
    <section class="manual-panel">
      <div class="panel-content">
        <p class="eyebrow">ServiceAI Cloud</p>
        <h2>{{ $t('adminPages.manual.title') }}</h2>
        <p class="description">{{ $t('adminPages.manual.desc') }}</p>

        <div class="actions">
          <a class="primary-btn" href="/files/manual.pdf" download>
            {{ $t('adminPages.manual.download') }}
          </a>
          <a class="ghost-btn" href="/files/manual.pdf" target="_blank" rel="noopener">
            {{ $t('adminPages.manual.preview') }}
          </a>
        </div>
      </div>

      <div class="file-box">
        <div class="file-icon">PDF</div>
        <div>
          <div class="file-name">manual.pdf</div>
          <div class="file-meta">{{ $t('adminPages.manual.fileMeta') }}</div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.manual-page { max-width: 920px; }
.manual-panel { display:grid; grid-template-columns:minmax(0,1fr) 260px; gap:28px; align-items:center; background:#fff; border-radius:8px; padding:28px; box-shadow:0 10px 30px rgba(15,23,42,.06); }
.eyebrow { margin:0 0 8px; color:#2563eb; font-size:13px; font-weight:700; }
h2 { margin:0; font-size:28px; }
.description { max-width:560px; margin:12px 0 0; color:#4b5563; line-height:1.7; }
.actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:22px; }
.primary-btn,.ghost-btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:0 16px; border-radius:8px; text-decoration:none; font-weight:700; }
.primary-btn { background:#111827; color:#fff; }
.ghost-btn { background:#eef2f7; color:#111827; }
.file-box { display:flex; align-items:center; gap:14px; border:1px solid #e5e7eb; border-radius:8px; padding:18px; background:#f8fafc; }
.file-icon { width:54px; height:54px; display:grid; place-items:center; border-radius:8px; background:#fee2e2; color:#b91c1c; font-weight:800; font-size:14px; }
.file-name { color:#111827; font-weight:800; }
.file-meta { margin-top:4px; color:#6b7280; font-size:13px; }
@media (max-width:760px) { .manual-panel { grid-template-columns:1fr; } }
</style>
