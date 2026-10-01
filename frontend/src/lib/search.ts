import {
  getProjectsFromWP,
  getServicesFromWP,
  getPosts,
  getTools,
} from './wordpress';

export type SearchCategory = 'all' | 'projects' | 'services' | 'articles' | 'tools';

export interface SearchResultItem {
  id: string | number;
  title: string;
  subtitle?: string;
  description: string;
  category: 'projects' | 'services' | 'articles' | 'tools';
  categoryLabel: string;
  badge?: string;
  url: string;
  image?: string;
  date?: string;
}

export interface SearchResponse {
  query: string;
  total: number;
  counts: {
    all: number;
    projects: number;
    services: number;
    articles: number;
    tools: number;
  };
  results: SearchResultItem[];
  categorized: {
    services: SearchResultItem[];
    projects: SearchResultItem[];
    articles: SearchResultItem[];
    tools: SearchResultItem[];
  };
}

export async function performGlobalSearch(rawQuery: string): Promise<SearchResponse> {
  const query = (rawQuery || '').trim().toLowerCase();

  if (!query) {
    return {
      query: '',
      total: 0,
      counts: { all: 0, projects: 0, services: 0, articles: 0, tools: 0 },
      results: [],
      categorized: {
        services: [],
        projects: [],
        articles: [],
        tools: [],
      },
    };
  }

  const queryTerms = query.split(/\s+/).filter(Boolean);

  const matchesText = (text: string) => {
    if (!text) return false;
    const lower = text.toLowerCase();
    return queryTerms.some((term) => lower.includes(term));
  };

  const results: SearchResultItem[] = [];

  // 1. ค้นหาจาก บริการ (Services from WordPress)
  try {
    const services = await getServicesFromWP();
    for (const s of services) {
      const title = s.title?.rendered || '';
      const titleTh = s.service_meta?.title_th || '';
      const subtitle = s.service_meta?.subtitle || '';
      const desc = s.content?.rendered?.replace(/<[^>]*>?/gm, '').trim() || '';
      const tag = s.service_meta?.tag || 'บริการ';
      const tech = (s.service_meta?.tech_stack || []).join(' ');
      const highlights = (s.service_meta?.highlights || []).join(' ');

      const haystack = `${title} ${titleTh} ${subtitle} ${desc} ${tag} ${tech} ${highlights}`;
      if (matchesText(haystack)) {
        results.push({
          id: s.slug,
          title: titleTh || title,
          subtitle: subtitle || undefined,
          description: desc,
          category: 'services',
          categoryLabel: 'บริการ',
          badge: tag,
          url: `/services#${s.slug}`,
          image: s.featured_image_url,
        });
      }
    }
  } catch (e) {
    // ignore
  }

  // 2. ค้นหาจาก ผลงาน (Projects from WordPress)
  try {
    const projects = await getProjectsFromWP();
    for (const p of projects) {
      const title = p.title?.rendered || '';
      const client = p.project_meta?.client || '';
      const category = p.project_meta?.category_tag || 'ผลงาน';
      const desc = p.content?.rendered?.replace(/<[^>]*>?/gm, '').trim() || '';
      const tech = (p.project_meta?.tech_stack || []).join(' ');
      const metrics = (p.project_meta?.metrics || []).join(' ');

      const haystack = `${title} ${client} ${category} ${desc} ${tech} ${metrics}`;
      if (matchesText(haystack)) {
        results.push({
          id: p.slug,
          title: title,
          subtitle: client ? `${client} · ${category}` : category,
          description: desc,
          category: 'projects',
          categoryLabel: 'ผลงาน',
          badge: category,
          url: `/projects/${p.slug}`,
          image: p.featured_image_url,
          date: p.project_meta?.year,
        });
      }
    }
  } catch (e) {
    // ignore
  }

  // 3. ค้นหาจาก เครื่องมือ (Tools from WordPress)
  try {
    const allTools = await getTools({ perPage: 50 });
    for (const t of allTools) {
      const toolName = t.title?.rendered || t.slug;
      const toolDesc = t.excerpt?.rendered || '';
      const toolCat = t.tool_meta?.category_name || '';
      const toolFormat = t.tool_meta?.format || '';
      const haystack = `${toolName} ${toolDesc} ${toolCat} ${toolFormat}`;
      if (matchesText(haystack)) {
        const cleanDesc = toolDesc.replace(/<[^>]*>?/gm, '').trim();
        results.push({
          id: t.slug,
          title: toolName,
          subtitle: toolFormat ? `รูปแบบ: ${toolFormat}` : undefined,
          description: cleanDesc || 'เครื่องมือช่วยทำงานสำเร็จรูปสำหรับองค์กรและคนออฟฟิศ',
          category: 'tools',
          categoryLabel: 'เครื่องมือ',
          badge: toolCat || 'เครื่องมือ',
          url: `/tools/${t.slug}`,
          image: (t as any).featured_image_url,
        });
      }
    }
  } catch (e) {
    // ignore
  }

  // 4. ค้นหาจาก บทความ (Articles / Blog from WordPress)
  try {
    const allPosts = await getPosts({ perPage: 50 });
    for (const post of allPosts) {
      const postTitle = post.title?.rendered || '';
      const postExcerpt = post.excerpt?.rendered || '';
      const haystack = `${postTitle} ${postExcerpt}`;
      if (matchesText(haystack)) {
        const cleanExcerpt = postExcerpt.replace(/<[^>]*>?/gm, '').trim();
        results.push({
          id: post.slug,
          title: postTitle,
          subtitle: post.date ? new Date(post.date).toLocaleDateString('th-TH') : undefined,
          description: cleanExcerpt || 'บทความวิชาการและสาระความรู้ด้านเทคโนโลยี',
          category: 'articles',
          categoryLabel: 'บทความ',
          badge: 'บทความ',
          url: `/blog/${post.slug}`,
          image: (post as any).featured_image_url,
          date: post.date,
        });
      }
    }
  } catch (e) {
    // ignore
  }

  const categorized = {
    services: results.filter((r) => r.category === 'services'),
    projects: results.filter((r) => r.category === 'projects'),
    articles: results.filter((r) => r.category === 'articles'),
    tools: results.filter((r) => r.category === 'tools'),
  };

  const counts = {
    all: results.length,
    services: categorized.services.length,
    projects: categorized.projects.length,
    articles: categorized.articles.length,
    tools: categorized.tools.length,
  };

  return {
    query,
    total: results.length,
    counts,
    results,
    categorized,
  };
}
