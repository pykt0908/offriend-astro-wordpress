# Company Website — Project Specification

## 1. Project Overview

เว็บไซต์บริษัทสำหรับนำเสนอแบรนด์ บริการ โซลูชัน ผลงาน ข่าวสาร และช่องทางติดต่อ โดยมีรูปแบบเว็บไซต์ Technology Company ที่ทันสมัย สะอาด และเป็นมืออาชีพ

แนวทางด้าน Information Architecture อ้างอิงเว็บไซต์บริษัท/สถาบันไอทีที่มีการแบ่งเนื้อหาเป็นบริการ ผลงาน บทความ และ CTA อย่างชัดเจน แต่ต้องออกแบบ Visual Identity ใหม่ทั้งหมดให้สอดคล้องกับแบรนด์และโลโก้ของบริษัท

เว็บไซต์ใช้แนวคิด Headless CMS:

- Frontend: Astro
- CMS: WordPress
- API: WordPress REST API หรือ WPGraphQL
- Styling: Tailwind CSS
- Language: TypeScript
- Interactive UI: Astro Components เป็นหลัก และใช้ React เฉพาะส่วนที่มี interaction ซับซ้อน

---

# 2. Project Goals

## Primary Goals

1. สร้างเว็บไซต์บริษัทที่มีภาพลักษณ์เป็น Technology / Digital Company
2. นำเสนอข้อมูลบริษัทและบริการอย่างเป็นระบบ
3. ให้ทีมงานสามารถจัดการ Content ผ่าน WordPress ได้
4. Frontend ต้องโหลดเร็วและรองรับ SEO
5. Responsive ตั้งแต่ Mobile ถึง Desktop
6. สามารถเพิ่มหน้าและ Content Type ในอนาคตได้
7. Frontend ไม่ผูกติดกับ WordPress มากเกินไป
8. รองรับการเปลี่ยน CMS ในอนาคต
9. โครงสร้าง Code ต้องเหมาะสำหรับการพัฒนาต่อระยะยาว

---

# 3. Technology Stack

## Frontend

- Astro
- TypeScript
- Tailwind CSS
- Astro Components
- React เฉพาะ Interactive Components ที่จำเป็น

## CMS

- WordPress
- WordPress REST API หรือ WPGraphQL
- Custom Post Types
- Custom Fields
- WordPress Media Library

## Deployment

Frontend:

- Vercel
- Cloudflare Pages
- Netlify

เลือกใช้เพียงหนึ่งระบบ

CMS:

- VPS
- Managed WordPress Hosting
- Existing Hosting

---

# 4. Architecture

```text
                         INTERNET
                            |
                            v
                    +---------------+
                    |      CDN      |
                    +-------+-------+
                            |
                            v
                    +---------------+
                    |     Astro     |
                    |   Frontend    |
                    +-------+-------+
                            |
                    API / Build / Webhook
                            |
                            v
                    +---------------+
                    |   WordPress   |
                    | Headless CMS  |
                    +-------+-------+
                            |
                            v
                         MySQL