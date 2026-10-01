import type {
  WPPost,
  WPPage,
  WPCategory,
  WPTool,
  SiteSettings,
  WPTeamMember,
  WPService,
  WPProject,
  TeamMember,
} from '../types/wordpress';

const WP_API_URL =
  import.meta.env.PUBLIC_WORDPRESS_API_URL ||
  'http://localhost/offriend-backend/wp-json/wp/v2';

const CACHE_TTL = import.meta.env.DEV ? 0 : 30 * 1000; // In DEV: 0 (immediate reflection of WP edits), In PROD: 30s

/**
 * Check if the WordPress REST API is reachable
 */
export async function checkWordPressStatus(): Promise<{
  isConnected: boolean;
  message: string;
  endpoint: string;
}> {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(`${WP_API_URL}/posts?per_page=1`, {
      signal: controller.signal,
    });
    clearTimeout(timeoutId);

    if (res.ok) {
      return {
        isConnected: true,
        message: 'เชื่อมต่อ WordPress REST API สำเร็จ',
        endpoint: WP_API_URL,
      };
    } else {
      return {
        isConnected: false,
        message: `WordPress ตอบกลับด้วยสถานะ HTTP ${res.status}`,
        endpoint: WP_API_URL,
      };
    }
  } catch (error) {
    return {
      isConnected: false,
      message: 'ยังไม่สามารถเชื่อมต่อ WordPress REST API ได้',
      endpoint: WP_API_URL,
    };
  }
}

// ==========================================
// 1. POSTS & BLOG
// ==========================================

let cachedPosts: WPPost[] | null = null;
let lastPostsFetch = 0;

/**
 * Fetch published posts from WordPress REST API
 */
export async function getPosts(params?: {
  page?: number;
  perPage?: number;
  categoryId?: number;
}): Promise<WPPost[]> {
  const page = params?.page || 1;
  const perPage = params?.perPage || 10;
  const now = Date.now();

  if (cachedPosts && now - lastPostsFetch < CACHE_TTL && !params?.categoryId && page === 1) {
    return cachedPosts.slice(0, perPage);
  }

  let url = `${WP_API_URL}/posts?_embed=true&page=${page}&per_page=${perPage}&status=publish`;
  if (params?.categoryId) {
    url += `&categories=${params.categoryId}`;
  }

  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (!res.ok) return [];

    const posts: WPPost[] = await res.json();
    if (Array.isArray(posts)) {
      if (!params?.categoryId && page === 1) {
        cachedPosts = posts;
        lastPostsFetch = now;
      }
      return posts.slice(0, perPage);
    }
    return [];
  } catch (error) {
    return [];
  }
}

/**
 * Fetch a single post by slug from WordPress
 */
export async function getPostBySlug(slug: string): Promise<WPPost | null> {
  const url = `${WP_API_URL}/posts?_embed=true&slug=${encodeURIComponent(slug)}&status=publish`;
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (!res.ok) return null;
    const posts: WPPost[] = await res.json();
    if (Array.isArray(posts) && posts.length > 0) return posts[0];
    return null;
  } catch (error) {
    return null;
  }
}

/**
 * Fetch all categories from WordPress
 */
export async function getCategories(): Promise<WPCategory[]> {
  const url = `${WP_API_URL}/categories?hide_empty=true`;
  try {
    const res = await fetch(url);
    if (!res.ok) return [];
    return await res.json();
  } catch (error) {
    return [];
  }
}

/**
 * Fetch all pages from WordPress
 */
export async function getPages(): Promise<WPPage[]> {
  const url = `${WP_API_URL}/pages?_embed=true&status=publish`;
  try {
    const res = await fetch(url);
    if (!res.ok) return [];
    return await res.json();
  } catch (error) {
    return [];
  }
}

// ==========================================
// 2. TOOLS & TEMPLATES (CPT: tools)
// ==========================================

let cachedTools: WPTool[] | null = null;
let lastToolsFetch = 0;

/**
 * Fetch all tools/templates from WordPress REST API (custom post type 'tools')
 */
export async function getTools(options?: { perPage?: number }): Promise<WPTool[]> {
  const perPage = options?.perPage || 100;
  const now = Date.now();

  if (cachedTools && now - lastToolsFetch < CACHE_TTL) {
    return cachedTools.slice(0, perPage);
  }

  const url = `${WP_API_URL}/tools?_embed=true&status=publish&per_page=${perPage}`;
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data: WPTool[] = await res.json();
      if (Array.isArray(data)) {
        cachedTools = data;
        lastToolsFetch = now;
        return data.slice(0, perPage);
      }
    }
    return [];
  } catch (error) {
    return [];
  }
}

/**
 * Fetch a single tool by slug from WordPress
 */
export async function getToolBySlug(slug: string): Promise<WPTool | null> {
  const now = Date.now();
  if (cachedTools && now - lastToolsFetch < CACHE_TTL) {
    const found = cachedTools.find((t) => t.slug === slug);
    if (found) return found;
  }

  const url = `${WP_API_URL}/tools?slug=${encodeURIComponent(slug)}&_embed=true&status=publish`;
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data: WPTool[] = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        return data[0];
      }
    }
    return null;
  } catch (error) {
    return null;
  }
}

/**
 * Fetch all tool slugs for SSG routing
 */
export async function getAllToolSlugs(): Promise<string[]> {
  const tools = await getTools({ perPage: 100 });
  return tools.map((t) => t.slug);
}

// ==========================================
// 3. GLOBAL SITE SETTINGS (OFFRIEND CORE)
// ==========================================

export const DEFAULT_SITE_SETTINGS: SiteSettings = {
  phone: '088-807-9770',
  working_hours: 'จ-ศ 9:00-17:00 น.',
  line_id: '@offriend',
  line_url: 'https://line.me',
  brand_slogan: 'เพื่อนคู่คิดคนออฟฟิศ',
  company_name_th: 'บริษัท ออฟเฟรนด์ จำกัด',
  company_name_en: 'OFFRIEND COMPANY LIMITED',
  company_desc:
    'ผู้นำด้านการพัฒนาซอฟต์แวร์ระดับองค์กร สถาปัตยกรรมคลาวด์ และ Headless CMS มุ่งมั่นส่งมอบโซลูชันวิศวกรรมเทคโนโลยีที่มั่นคง ปลอดภัย และสร้างคุณค่าทางธุรกิจอย่างยั่งยืน',
  headquarters_address:
    'อาคารไอทีจีเนียส เซ็นเตอร์ เลขที่ 123/45 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร 10110',
  contact_email: 'contact@offriend.co.th',
  contact_phone: '088-807-9770',
  copyright_text: 'สงวนลิขสิทธิ์ตามกฎหมาย',
  facebook_url: 'https://facebook.com',
  youtube_url: 'https://youtube.com',
  linkedin_url: 'https://linkedin.com',
  banners: [],
};


let cachedSettings: SiteSettings | null = null;
let lastSettingsFetch = 0;

/**
 * Fetch global site settings from WordPress REST API (/wp-json/offriend/v1/settings)
 */
export async function getSiteSettings(): Promise<SiteSettings> {
  const now = Date.now();
  if (cachedSettings && now - lastSettingsFetch < CACHE_TTL) {
    return cachedSettings;
  }

  const url = WP_API_URL.replace(/\/wp\/v2\/?$/, '/offriend/v1/settings');
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data: SiteSettings = await res.json();
      if (data && typeof data === 'object') {
        cachedSettings = { ...DEFAULT_SITE_SETTINGS, ...data };
        lastSettingsFetch = now;
        return cachedSettings;
      }
    }
    return DEFAULT_SITE_SETTINGS;
  } catch (err) {
    return DEFAULT_SITE_SETTINGS;
  }
}

// ==========================================
// 4. TEAM MEMBERS (บุคลากร & วิทยากร)
// ==========================================

let cachedTeam: TeamMember[] | null = null;
let lastTeamFetch = 0;

function mapWpTeamToMember(wpItem: WPTeamMember): TeamMember {
  const meta = wpItem.team_meta || {};
  return {
    id: wpItem.slug,
    slug: wpItem.slug,
    prefix: meta.prefix || '',
    name: wpItem.title?.rendered || '',
    nameEn: meta.name_en || '',
    role: meta.role_title || '',
    department: 'วิทยากรและผู้เชี่ยวชาญไอที',
    avatar: wpItem.featured_image_url || `/images/team/${wpItem.slug}.png`,
    educationLine: meta.education_line || '',
    resumePdf: meta.resume_pdf || '#',
    objective:
      meta.objective ||
      wpItem.content?.rendered?.replace(/<[^>]*>?/gm, '').trim() ||
      '',
    experiences:
      Array.isArray(meta.experiences) && meta.experiences.length > 0
        ? meta.experiences
        : [],
    socialLinks: {
      email: 'contact@offriend.co.th',
      phone: '088-807-9770',
    },
  };
}

/**
 * Fetch team members from WordPress REST API (/wp-json/wp/v2/team)
 */
export async function getTeamMembers(): Promise<TeamMember[]> {
  const now = Date.now();
  if (cachedTeam && now - lastTeamFetch < CACHE_TTL) {
    return cachedTeam;
  }

  const url = `${WP_API_URL}/team?status=publish&per_page=100&_embed=true`;
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data: WPTeamMember[] = await res.json();
      if (Array.isArray(data)) {
        const mapped = data.map(mapWpTeamToMember);
        cachedTeam = mapped;
        lastTeamFetch = now;
        return mapped;
      }
    }
    return [];
  } catch (err) {
    return [];
  }
}

/**
 * Fetch a single team member by slug from WordPress
 */
export async function getTeamMemberBySlug(slug: string): Promise<TeamMember | null> {
  const members = await getTeamMembers();
  return members.find((m) => m.slug === slug) || null;
}

// ==========================================
// 5. SERVICES (บริการทั้งหมด)
// ==========================================

let cachedServices: WPService[] | null = null;
let lastServicesFetch = 0;

/**
 * Fetch services from WordPress REST API (/wp-json/wp/v2/services)
 */
export async function getServicesFromWP(): Promise<WPService[]> {
  const now = Date.now();
  if (cachedServices && now - lastServicesFetch < CACHE_TTL) {
    return cachedServices;
  }

  const url = `${WP_API_URL}/services?status=publish&per_page=100&_embed=true`;
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data: WPService[] = await res.json();
      if (Array.isArray(data)) {
        cachedServices = data;
        lastServicesFetch = now;
        return data;
      }
    }
    return [];
  } catch (err) {
    return [];
  }
}

/**
 * Fetch a single service by slug from WordPress
 */
export async function getServiceBySlug(slug: string): Promise<WPService | null> {
  const services = await getServicesFromWP();
  return services.find((s) => s.slug === slug) || null;
}

// ==========================================
// 6. PROJECTS (ผลงานและโครงการจริง)
// ==========================================

let cachedProjects: WPProject[] | null = null;
let lastProjectsFetch = 0;

/**
 * Fetch projects from WordPress REST API (/wp-json/wp/v2/projects)
 */
export async function getProjectsFromWP(): Promise<WPProject[]> {
  const now = Date.now();
  if (cachedProjects && now - lastProjectsFetch < CACHE_TTL) {
    return cachedProjects;
  }

  const url = `${WP_API_URL}/projects?status=publish&per_page=100&_embed=true`;
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    const res = await fetch(url, { signal: controller.signal });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data: WPProject[] = await res.json();
      if (Array.isArray(data)) {
        cachedProjects = data;
        lastProjectsFetch = now;
        return data;
      }
    }
    return [];
  } catch (err) {
    return [];
  }
}

/**
 * Fetch a single project by slug from WordPress
 */
export async function getProjectBySlug(slug: string): Promise<WPProject | null> {
  const projects = await getProjectsFromWP();
  return projects.find((p) => p.slug === slug) || null;
}
