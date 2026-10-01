# Offriend Web — Headless WordPress + Astro Technology Company Website

โปรเจกต์เว็บไซต์บริษัท **Offriend Company Limited** พัฒนาตามข้อกำหนดใน [`spec/spec.md`](file:///c:/Users/pykt/Documents/offriend-web/spec/spec.md) ด้วยสถาปัตยกรรม **Headless CMS**:
- **Frontend**: Astro 7 + Tailwind CSS v4 + TypeScript + React Interactive Components
- **Typography**: ติดตั้งฟอนต์ประจำแบรนด์ **LINE Seed Sans TH** (Thin, Regular, Bold, ExtraBold, Heavy)
- **Branding**: โลโก้ Offriend อย่างเป็นทางการ (`spec/images/logos/no-bg/1.7TP.png` และ emblem `1.5TP.png`)
- **CMS**: WordPress Core (`backend/`) เชื่อมโยงกับ XAMPP Apache และ MySQL ฐานข้อมูล `offriend_db`
- **Data Flow**: เชื่อมต่อบทความและเนื้อหาแบบเรียลไทม์ผ่าน **WordPress REST API** (`/wp-json/wp/v2/`)

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```text
offriend-web/
├── frontend/                          # ฝั่งหน้าบ้าน (Astro Frontend)
│   ├── public/
│   │   ├── fonts/lineseed/            # ฟอนต์ LINE Seed Sans TH (.woff2)
│   │   └── images/logos/              # โลโก้ Offriend ทั้งหมด (no-bg, light/dark)
│   ├── src/
│   │   ├── components/                # UI Components
│   │   │   ├── Header.astro           # Header + Navigation + Offriend Logo + Mobile Menu
│   │   │   ├── Footer.astro           # Footer + Tech Badges + Contact details
│   │   │   ├── PostCard.astro         # การ์ดแสดงผลบทความจาก WordPress
│   │   │   ├── ConnectionBanner.astro # การ์ดสถานะการเชื่อมต่อ WordPress REST API
│   │   │   └── ProjectEstimator.tsx   # [React] Interactive Project Scope & Cost Estimator
│   │   ├── layouts/
│   │   │   └── BaseLayout.astro       # โครงสร้าง Layout หลัก + Font Preload + SEO Meta
│   │   ├── lib/
│   │   │   └── wordpress.ts           # REST API Client ดึงข้อมูลบทความ/หมวดหมู่
│   │   ├── pages/
│   │   │   ├── index.astro            # หน้าแรก (Hero, Services, Process, Estimator, News)
│   │   │   ├── services.astro         # หน้าบริการและโซลูชันทั้งหมด
│   │   │   ├── projects.astro         # หน้ากรณีศึกษาและผลงาน (Case Studies)
│   │   │   ├── about.astro            # หน้าเกี่ยวกับบริษัท พันธกิจ วิสัยทัศน์ และ Headless Tech
│   │   │   ├── contact.astro          # หน้าแบบฟอร์มติดต่อ ข้อมูลสำนักงาน และแผนที่
│   │   │   └── blog/
│   │   │       ├── index.astro        # หน้ารวมบทความทั้งหมด (Blog Archive)
│   │   │       └── [slug].astro       # หน้ารายละเอียดบทความ (Dynamic SSG Route)
│   │   ├── styles/
│   │   │   └── global.css             # Tailwind v4 + LINE Seed Sans TH + Glassmorphism
│   │   └── types/
│   │       └── wordpress.ts           # TypeScript interfaces สำหรับ WordPress REST API
│   ├── .env                           # คอนฟิก URL เชื่อมต่อไปยัง WordPress
│   ├── astro.config.mjs               # คอนฟิก Astro + Tailwind + React
│   └── package.json
│
├── backend/                           # ฝั่งหลังบ้าน (WordPress)
│   ├── wp-admin/                      # WordPress Admin
│   ├── wp-content/                    # ธีม, ปลั๊กอิน, uploads
│   ├── wp-config.php                  # ตั้งค่าเชื่อมต่อ MySQL XAMPP เรียบร้อยแล้ว
│   └── ...
│
├── spec/                              # เอกสารข้อกำหนดและ Assets
│   ├── fonts/                         # ฟอนต์ต้นฉบับ (LINE Seed Sans TH)
│   ├── images/logos/                  # โลโก้ต้นฉบับ
│   └── spec.md                        # ข้อกำหนดของโปรเจกต์
│
├── package.json                       # สคริปต์สั่งรันจาก root
└── README.md                          # เอกสารคู่มือโปรเจกต์
```

---

## 🗄️ ข้อมูลฐานข้อมูลและการเชื่อมโยง (Database & Backend)

- **Database Name**: `offriend_db`
- **Database User**: `root`
- **Database Password**: *(ว่างไว้)*
- **Host**: `localhost`
- **Junction Link**: โฟลเดอร์ `backend/` เชื่อมโยงเข้ากับ `C:\xampp\htdocs\offriend-backend` อัตโนมัติ ทำให้ Apache ของ XAMPP ให้บริการได้ทันทีที่:
  - **URL**: `http://localhost/offriend-backend/`
  - **REST API Endpoint**: `http://localhost/offriend-backend/wp-json/wp/v2/`

---

## 🚀 วิธีการรันโปรเจกต์

### 1. ตรวจสอบ XAMPP
เปิด **XAMPP Control Panel** และตรวจสอบให้แน่ใจว่า **Apache** และ **MySQL** อยู่ในสถานะ **Running**

### 2. รัน Astro Dev Server
เปิด Terminal ที่โฟลเดอร์โปรเจกต์นี้ แล้วรัน:
```bash
npm run dev
```

เปิดดูหน้าเว็บฝั่งหน้าบ้านได้ที่:  
🌐 **[http://localhost:4321](http://localhost:4321)**

### 3. เข้าจัดการบทความใน WordPress
เข้าสู่ระบบหลังบ้านเพื่อเพิ่มบทความหรือจัดการสื่อ:  
👉 **[http://localhost/offriend-backend/wp-admin/](http://localhost/offriend-backend/wp-admin/)**

---

## 📄 เส้นทางหน้าเว็บที่พร้อมใช้งาน (Available Routes)

| หน้าเว็บ | URL | รายละเอียด |
| :--- | :--- | :--- |
| **หน้าแรก** | `/` | Hero, บริการ, ขั้นตอนการทำงาน, ตัวคำนวณงบประมาณ (React), บทความล่าสุด |
| **บริการ** | `/services` | รายละเอียดบริการ Enterprise Web, Mobile Apps, Cloud & DevOps, Headless CMS |
| **ผลงาน** | `/projects` | กรณีศึกษาและผลงานเด่น (Case Studies) พร้อมผลลัพธ์เชิงธุรกิจ |
| **เกี่ยวกับเรา** | `/about` | เรื่องราวของบริษัท พันธกิจ วิสัยทัศน์ และข้อได้เปรียบของสถาปัตยกรรม |
| **ติดต่อเรา** | `/contact` | แบบฟอร์มติดต่อสอบถาม ข้อมูลที่ตั้ง เวลาทำการ และลิงก์ด่วนเข้า CMS |
| **บทความ** | `/blog` | คลังข่าวสารและบทความทั้งหมด ดึงสดจาก WordPress REST API |
| **อ่านบทความ** | `/blog/[slug]` | หน้ารายละเอียดบทความพร้อมรูปภาพปก ผู้เขียน วันที่ และเนื้อหา HTML |
