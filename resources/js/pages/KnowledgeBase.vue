<template>
  <div class="page-card">
    <div class="page-head">
      <div>
        <h2>Knowledge Base</h2>
        <p>管理 FAQ 與 AI 知識內容</p>
      </div>
      <button class="ghost-btn">新增知識</button>
    </div>

    <div class="empty-box">
      <h3>Knowledge Base 模組</h3>
      <p>下一步可接 FAQ CRUD、關鍵字、AI 啟用狀態。</p>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.page-card {
  background: #fff;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}
.page-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}
.page-head h2 {
  margin: 0 0 6px;
}
.page-head p {
  margin: 0;
  color: #6b7280;
}
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  background: #eef2f7;
  cursor: pointer;
}
.empty-box {
  padding: 40px 20px;
  text-align: center;
  color: #6b7280;
}
.empty-box h3 {
  color: #111827;
}
</style>