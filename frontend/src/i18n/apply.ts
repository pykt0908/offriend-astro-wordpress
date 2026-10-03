import { messages } from './messages';

const ATTRIBUTES = ['placeholder', 'aria-label', 'title', 'alt'] as const;

function translateExact(value: string): string {
  const trimmed = value.trim();
  const itemCount = trimmed.match(/^(\d+)\s+รายการ$/);
  if (itemCount) {
    const leading = value.match(/^\s*/)?.[0] ?? '';
    const trailing = value.match(/\s*$/)?.[0] ?? '';
    return `${leading}${itemCount[1]} items${trailing}`;
  }
  if (trimmed.startsWith('เวลาทำการ:')) {
    const rest = trimmed.slice('เวลาทำการ:'.length).trim();
    const hours = messages[rest] || rest;
    const leading = value.match(/^\s*/)?.[0] ?? '';
    const trailing = value.match(/\s*$/)?.[0] ?? '';
    return `${leading}Hours: ${hours}${trailing}`;
  }
  const mapped = messages[trimmed];
  if (!mapped || mapped === trimmed) return value;
  const leading = value.match(/^\s*/)?.[0] ?? '';
  const trailing = value.match(/\s*$/)?.[0] ?? '';
  return `${leading}${mapped}${trailing}`;
}

export function isEnglishLocale(): boolean {
  return document.documentElement.lang === 'en' || document.documentElement.classList.contains('translating');
}

export function applyTranslations(root: ParentNode = document.body): void {
  if (!isEnglishLocale()) return;

  const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
  const textNodes: Text[] = [];
  let current: Node | null = walker.nextNode();
  while (current) {
    textNodes.push(current as Text);
    current = walker.nextNode();
  }

  for (const node of textNodes) {
    const parent = node.parentElement;
    if (!parent || parent.closest('script, style')) continue;
    const next = translateExact(node.nodeValue || '');
    if (next !== node.nodeValue) node.nodeValue = next;
  }

  const elements = root instanceof Element ? root.querySelectorAll('*') : [];
  elements.forEach((element) => {
    for (const attr of ATTRIBUTES) {
      const value = element.getAttribute(attr);
      if (!value) continue;
      const next = translateExact(value);
      if (next !== value) element.setAttribute(attr, next);
    }
  });

  const title = translateExact(document.title);
  if (title !== document.title) document.title = title.trim();
}
