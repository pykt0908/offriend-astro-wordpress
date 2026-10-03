export interface WPRenderedField {
  rendered: string;
}

export interface WPCategory {
  id: number;
  count: number;
  description: string;
  link: string;
  name: string;
  slug: string;
  taxonomy: string;
}

export interface WPTag {
  id: number;
  count: number;
  description: string;
  link: string;
  name: string;
  slug: string;
  taxonomy: string;
}

export interface WPAuthor {
  id: number;
  name: string;
  url: string;
  description: string;
  link: string;
  avatar_urls?: {
    [key: string]: string;
  };
}

export interface WPMediaItem {
  id: number;
  source_url: string;
  alt_text: string;
  title: WPRenderedField;
  media_details?: {
    width: number;
    height: number;
    sizes: {
      [key: string]: {
        source_url: string;
        width: number;
        height: number;
      };
    };
  };
}

export interface WPPost {
  id: number;
  date: string;
  date_gmt: string;
  modified: string;
  slug: string;
  status: string;
  type: string;
  link: string;
  title: WPRenderedField;
  content: WPRenderedField;
  excerpt: WPRenderedField;
  author: number;
  featured_media: number;
  comment_status: string;
  ping_status: string;
  sticky: boolean;
  categories: number[];
  tags: number[];
  _embedded?: {
    author?: WPAuthor[];
    'wp:featuredmedia'?: WPMediaItem[];
    'wp:term'?: Array<Array<WPCategory | WPTag>>;
  };
}

export interface WPPage {
  id: number;
  date: string;
  slug: string;
  status: string;
  title: WPRenderedField;
  content: WPRenderedField;
  excerpt: WPRenderedField;
  featured_media: number;
  _embedded?: {
    'wp:featuredmedia'?: WPMediaItem[];
  };
}

export interface WPToolFormat {
  id: number;
  name: string;
  slug: string;
  count: number;
  description?: string;
  sort_order?: number;
}

export interface WPToolCategory {
  id: number;
  name: string;
  slug: string;
  count: number;
  description?: string;
  icon?: string;
  color?: string;
  sort_order?: number;
}

export interface WPTool {
  id: number;
  date: string;
  slug: string;
  status: string;
  type: string;
  title: WPRenderedField;
  content: WPRenderedField;
  excerpt: WPRenderedField;
  featured_media?: number;
  featured_image_url?: string;
  tool_meta?: {
    format?: string;
    version?: string;
    badge?: string;
    downloads?: string;
    file_size?: string;
    download_link?: string;
    download_url?: string;
    manual_url?: string;
    manual_link?: string;
    guide_url?: string;
    faqs_raw?: string;
    faqs?: Array<{
      question: string;
      answer: string;
    }>;
    demo_url?: string;
    category?: string;
    category_name?: string;
    category_slug?: string;
    category_slugs?: string[];
    format_slug?: string;
    format_slugs?: string[];
  };
  _embedded?: {
    'wp:featuredmedia'?: WPMediaItem[];
    'wp:term'?: Array<Array<WPCategory | WPTag>>;
  };
}

export interface SiteSettings {
  phone: string;
  working_hours: string;
  line_id: string;
  line_url: string;
  brand_slogan: string;
  company_name_th: string;
  company_name_en: string;
  company_desc: string;
  headquarters_address: string;
  contact_email: string;
  contact_phone: string;
  copyright_text: string;
  facebook_url: string;
  youtube_url: string;
  linkedin_url: string;
  banners?: Array<{
    kicker: string;
    title: string;
    subtitle: string;
    button_text: string;
    button_url: string;
    image_url: string;
  }>;
}

export interface TeamMember {
  id: string;
  slug: string;
  prefix?: string;
  name: string;
  nameEn: string;
  role: string;
  department?: string;
  avatar: string;
  educationLine?: string;
  resumePdf?: string;
  objective?: string;
  experiences?: string[];
  socialLinks?: {
    linkedin?: string;
    github?: string;
    email?: string;
    phone?: string;
  };
}


export interface WPTeamMember {
  id: number;
  date: string;
  slug: string;
  status: string;
  type: string;
  title: WPRenderedField;
  content: WPRenderedField;
  excerpt?: WPRenderedField;
  featured_media?: number;
  featured_image_url?: string;
  team_meta?: {
    prefix?: string;
    name_en?: string;
    role_title?: string;
    education_line?: string;
    resume_pdf?: string;
    objective?: string;
    experiences?: string[];
  };
  _embedded?: {
    'wp:featuredmedia'?: WPMediaItem[];
  };
}

export interface WPService {
  id: number;
  date: string;
  slug: string;
  status: string;
  type: string;
  title: WPRenderedField;
  content: WPRenderedField;
  excerpt?: WPRenderedField;
  featured_media?: number;
  featured_image_url?: string;
  service_meta?: {
    title_th?: string;
    subtitle?: string;
    tag?: string;
    tech_stack?: string[];
    highlights?: string[];
  };
  _embedded?: {
    'wp:featuredmedia'?: WPMediaItem[];
  };
}

export interface WPProject {
  id: number;
  date: string;
  slug: string;
  status: string;
  type: string;
  title: WPRenderedField;
  content: WPRenderedField;
  excerpt?: WPRenderedField;
  featured_media?: number;
  featured_image_url?: string;
  project_meta?: {
    client?: string;
    category_tag?: string;
    year?: string;
    tech_stack?: string[];
    metrics?: string[];
  };
  _embedded?: {
    'wp:featuredmedia'?: WPMediaItem[];
    'wp:term'?: Array<Array<WPCategory | WPTag>>;
  };
}


