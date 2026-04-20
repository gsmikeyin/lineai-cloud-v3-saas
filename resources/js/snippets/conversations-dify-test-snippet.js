async function sendDifyReply() {
  if (!currentConversation.value || !replyText.value.trim()) return

  try {
    const res = await api.post(
      `/conversations/${currentConversation.value.id}/dify-reply`,
      { message: replyText.value.trim() }
    )

    alert(res.data?.data?.answer || '已呼叫 Dify')
  } catch (error) {
    console.error(error.response?.data || error)
    alert(error.response?.data?.message || 'Dify 測試失敗')
  }
}
