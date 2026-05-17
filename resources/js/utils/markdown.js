export function renderMarkdown(value) {
  const blocks = []
  let text = escapeHtml(String(value ?? '')).replace(/\r\n?/g, '\n')

  text = text.replace(/```([\s\S]*?)```/g, (_, code) => {
    const token = `@@CODE_BLOCK_${blocks.length}@@`
    blocks.push(`<pre><code>${code.trim()}</code></pre>`)
    return `\n${token}\n`
  })

  const lines = text.split('\n')
  const html = []
  let paragraph = []
  let listItems = []
  let listTag = 'ul'
  let listStart = null

  const flushParagraph = () => {
    if (!paragraph.length) return
    html.push(`<p>${formatInline(paragraph.join('<br>'))}</p>`)
    paragraph = []
  }

  const flushList = () => {
    if (!listItems.length) return
    const startAttr = listTag === 'ol' && listStart ? ` start="${listStart}"` : ''
    html.push(`<${listTag}${startAttr}>${listItems.map((item) => {
      const valueAttr = listTag === 'ol' && item.value ? ` value="${item.value}"` : ''
      return `<li${valueAttr}>${formatInline(item.text)}</li>`
    }).join('')}</${listTag}>`)
    listItems = []
    listTag = 'ul'
    listStart = null
  }

  for (const line of lines) {
    const trimmed = line.trim()

    if (!trimmed) {
      flushParagraph()
      continue
    }

    if (/^@@CODE_BLOCK_\d+@@$/.test(trimmed)) {
      flushParagraph()
      flushList()
      html.push(trimmed)
      continue
    }

    const heading = trimmed.match(/^(#{1,3})\s+(.+)$/)
    if (heading) {
      flushParagraph()
      flushList()
      const level = heading[1].length + 2
      html.push(`<h${level}>${formatInline(heading[2])}</h${level}>`)
      continue
    }

    const listItem = trimmed.match(/^([-*]|(\d+)\.)\s+(.+)$/)
    if (listItem) {
      flushParagraph()
      const nextListTag = /^\d+\.$/.test(listItem[1]) ? 'ol' : 'ul'
      if (listItems.length && listTag !== nextListTag) {
        flushList()
      }
      listTag = nextListTag
      if (nextListTag === 'ol' && listStart === null) {
        listStart = Number(listItem[2])
      }
      listItems.push({
        text: listItem[3],
        value: listItem[2] ? Number(listItem[2]) : null,
      })
      continue
    }

    flushList()
    paragraph.push(trimmed)
  }

  flushParagraph()
  flushList()

  return html.join('').replace(/@@CODE_BLOCK_(\d+)@@/g, (_, index) => blocks[Number(index)] || '')
}

function formatInline(value) {
  return value
    .replace(/`([^`]+)`/g, '<code>$1</code>')
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, (_, label, href) => {
      const safeHref = sanitizeUrl(href)
      return safeHref ? `<a href="${safeHref}" target="_blank" rel="noopener noreferrer">${label}</a>` : label
    })
    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
    .replace(/\*([^*]+)\*/g, '<em>$1</em>')
}

function escapeHtml(value) {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;')
}

function sanitizeUrl(value) {
  const decoded = value.replace(/&amp;/g, '&').trim()
  if (!/^(https?:|mailto:)/i.test(decoded)) return ''
  return escapeHtml(decoded)
}
