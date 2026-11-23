# NovaSMM Phase 15 — Production Final Report

วันที่ตรวจสอบ: 2026-11-23  
Repository: `C:/Users/tee/NovaSMM`  
Branch: `main`  
Domain: `uploading.com`  
Target host IP: `122.154.34.166`  
WordPress/IP เดิม: `34.174.96.8`

> ข้อมูลรับรองที่ผู้ใช้ส่งระหว่าง Phase 15 ไม่ถูกพิมพ์ซ้ำ ไม่ถูกบันทึกใน repository และไม่ปรากฏในรายงานนี้

---

## A. Repository Audit

| รายการ | ผลลัพธ์ |
|---|---|
| `git rev-parse --show-toplevel` | `C:/Users/tee/NovaSMM` |
| `git branch --show-current` | `main` |
| HEAD ก่อนเริ่ม | `62e35b13e0a077f6fec92b61f403eafcfb05625d` |
| `git fetch origin` + `git status -sb` | ตรงกับ `origin/main`, ไม่มี divergence |
| Untracked files | `composer.phar`, `package-lock.json` (ตั้งใจไม่ commit) |
| Secret scan working tree | 0 credential ใหม่ใน source ที่จะ commit |
| Stale copy ใน Temp | ไม่ได้ใช้งาน |

Source commit ของ Phase 15: `2f44097 feat: complete root domain production migration`

---

## B. WordPress Pre-Migration Audit

- DNS สาธารณะยังชี้ `uploading.com` และ `www.uploading.com` ไปที่ `34.174.96.8`
- หน้าเว็บที่ `34.174.96.8` เป็น WordPress จริง:
  - `<meta name="generator" content="WordPress 7.1.5" />`
  - พบ `wp-content`
  - พบ `wp-json`
- บน host เป้าหมาย `122.154.34.166` **ไม่มี WordPress** อยู่ในพื้นที่ที่เข้าถึงได้:
  - `domains/uploading.com/public_html` มีเพียง `index.html` (DirectAdmin default) และ `novasmm/`
  - `domains/uploading.com` มี `novasmm_app/`, `phase15_backups/`, `public_ftp/`, `public_html/`
  - ไม่พบ `wp-config.php`, `wp-content/`, database WordPress บน host นี้
- WordPress จึงอยู่คนละระบบกับ NovaSMM:
  - WordPress: SiteGround `34.174.96.8` (MySQL ภายใน SiteGround)
  - NovaSMM: host นี้ ใช้ SQLite `domains/uploading.com/novasmm_app/database/database.sqlite`
- ไม่นำ WordPress database ไปใช้แทน NovaSMM

---

## C. Backup & Recovery Verification

Backup ถูกสร้างบน host เป้าหมายนอก Public Root ที่:

`domains/uploading.com/phase15_backups/`

| ไฟล์ | ขนาด (bytes) | ผลตรวจ |
|---|---:|---|
| `20261123_novasmm_app.zip` | 42,639,876 | ไม่ว่าง |
| `20261123_public_html_before_root.zip` | 11,135,981 | ไม่ว่าง |

หลักฐานจาก `list-archive`:

- app archive มี `novasmm_app/.env`, `novasmm_app/database/database.sqlite`, `novasmm_app/public/index.php`, `novasmm_app/public/.htaccess`
- public archive มี `index.html`, `novasmm/index.php`, `novasmm/.htaccess`

SQLite recovery check:

```text
integrity_check=ok
lang vn default=0
lang th default=1
general={"primary_lang":"th"}
```

**ข้อจำกัด:** ไม่สามารถสำรอง WordPress source/uploads/database ได้ เพราะ WordPress อยู่ที่ SiteGround และไม่มีสิทธิ์เข้าถึง filesystem/database ของ SiteGround จากระบบนี้ มีเพียง public HTML snapshot ของหน้าแรกเท่านั้น

---

## D. Root Domain Architecture

การเตรียม root บน host เป้าหมายเสร็จแล้ว (ยังไม่เปลี่ยน DNS):

- ก๊อปปี้ public files จาก `public_html/novasmm` ขึ้นมา `public_html/`:
  - `.htaccess`, `assets/`, `build/`, `favicon.ico`, `index.php`, `plugins/`
- ปรับ `public_html/index.php`:
  - เปลี่ยน `__DIR__.'/../../novasmm_app/` → `__DIR__.'/../novasmm_app/` ทั้ง 3 จุด
- ปรับ `public_html/.htaccess`:
  - `RewriteBase /novasmm` → `RewriteBase /`
  - เพิ่ม `DirectoryIndex index.php index.html` เพราะ `index.html` เดิมยังอยู่และถูก Apache เลือกก่อน
- ปรับ `novasmm_app/.env`:
  - `APP_URL=https://uploading.com/novasmm` → `APP_URL=https://uploading.com`
  - `APP_KEY` ยังอยู่เหมือนเดิม
  - `APP_DEBUG=false`, `DB_CONNECTION=sqlite`, `DB_DATABASE` ชี้ SQLite เดิม
- Laravel app ยังอยู่นอก Public Root (`domains/uploading.com/novasmm_app`)
- ไม่เปิดเผย `.env`, `vendor/`, storage ส่วนตัวผ่าน HTTP (ดู section J)

ผลทดสอบด้วย `--resolve uploading.com:443:122.154.34.166`: root `/` ตอบ 200 และเป็น NovaSMM (ไม่ใช่ DirectAdmin page)

---

## E. DNS Migration

DNS ก่อน/ขณะเขียนรายงาน:

```text
uploading.com.          A     34.174.96.8
www.uploading.com.      A     34.174.96.8
uploading.com.          NS    ns1.siteground.net
uploading.com.          NS    ns2.siteground.net
uploading.com.          MX    10/20/30 mx*.antispam.mailspamprotection.com
uploading.com.          TXT   v=spf1 +a +mx +ip4:34.174.30.12 include:uploading.com.spf.auto.dnssmarthost.net ~all
uploading.com.          TXT   google-site-verification=... (ไม่แสดงค่าฉบับเต็ม)
AAAA/CNAME              ไม่มี record ที่ใช้งาน
```

เป้าหมายตามข้อมูล host:

```text
NS1 ns3.sogoodhost.com (122.154.34.166)
NS2 ns4.sogoodhost.com (122.154.34.166)
```

**ผล: ยังไม่เปลี่ยน DNS**  
เหตุผล:

- authoritative NS อยู่ที่ SiteGround (`ns1/ns2.siteground.net`)
- hosting account ที่มี DNS control เป็นของ DirectAdmin บน `122.154.34.166` แต่การเปลี่ยน zone ที่นั่นจะไม่มีผลจนกว่า registrar จะเปลี่ยน NS/A record
- ไม่มี SiteGround DNS/registrar access
- ตาม gate ของ Phase 15 ห้ามเปลี่ยน DNS โดยไม่มีสิทธิ์/อนุญาตที่ชัดเจนจากผู้ดูแล DNS

---

## F. SSL Verification

| Endpoint | Subject | Issuer | หมายเหตุ |
|---|---|---|---|
| `34.174.96.8` (WordPress ปัจจุบัน) | `CN=*.uploading.com` | Let's Encrypt `YR1` | ตรงกับ domain |
| `122.154.34.166` (host เป้าหมาย) | `CN=mdc-122-154-34-166.sticbox.com` | Let's Encrypt `YE2` | **ไม่ตรง uploading.com** |

- HTTPS บน host เป้าหมายทำงานได้ แต่ certificate name ไม่ตรง domain
- ต้อง provision/install certificate สำหรับ `uploading.com` บน host เป้าหมายหลัง DNS ชี้มาแล้ว
- HTTP → HTTPS redirect ยังไม่ถูกบังคับ (`http://uploading.com/` ตอบ 200 ไม่ redirect เมื่อ resolve ไป host เป้าหมาย)

---

## G. Laravel Production Fix

ปัญหาเดิม:

```text
InvalidArgumentException: Unable to locate a class or view for component [favicon].
```

สาเหตุ: `resources/views/layouts/error.blade.php` เรียก `<x-favicon/>` แต่ไม่มี component file

การแก้:

- เพิ่ม `resources/views/components/favicon.blade.php`
- ยังคง favicon functionality โดยใช้ `asset(setting('favicon', '/assets/images/brand-logos/nova-icon.svg'))`
- ไม่ลบ `<x-favicon/>`
- deploy component ไปที่ `domains/uploading.com/novasmm_app/resources/views/components/favicon.blade.php` แล้วตรวจเนื้อหาจาก remote สำเร็จ

ผลทดสอบ local:

```text
php artisan view:clear   PASS
php artisan view:cache   PASS
php artisan route:cache  FAIL — duplicate route name [api.v2]
php artisan config:cache PASS
```

หลังทดสอบ รัน clear ทั้ง view/route/config เพื่อไม่ทิ้ง cache ผิดปกติไว้

`route:cache` ล้มเหลวจากปัญหาชื่อ route ซ้ำที่มีอยู่แล้ว (`api/v2`) ไม่ได้เกิดจาก favicon fix และไม่ได้แก้ไขเพื่อเลี่ยงการเปลี่ยน route เดิม

---

## H. Authentication QA

Guest (resolve ไป host เป้าหมาย):

| URL | ผล |
|---|---|
| `/` | 200 |
| `/login` | 200 |
| `/register` | 200 |
| `/password/reset` | 200 |
| `/pages/services` | 200 |
| `/pages/api-docs` | 200 |

Protected route:

| URL | ผล |
|---|---|
| `/admin` | 302 → `https://uploading.com/login` |
| `/admin/dashboard` | 404 (route จริงคือ `/admin`) |

**ไม่ได้ทดสอบ user/admin ที่ login แล้ว** เพราะไม่มีบัญชีทดสอบที่ได้รับอนุญาต และห้ามสร้าง/แก้ admin account

---

## I. Dashboard QA

- ตรวจ route list พบ admin routes ครบ (`admin`, `admin/services`, `admin/users`, `admin/orders`, `admin/settings/*`, ฯลฯ)
- ตรวจ unauthenticated `/admin` → redirect ไป login ตามคาด
- หน้า dashboard ที่ login แล้วไม่ได้ทดสอบจริง (ดูข้อ H)

---

## J. Security Audit

ทดสอบ URL ที่ไม่ควรเปิดเผย (resolve ไป host เป้าหมาย):

| URL | HTTP | หมายเหตุ |
|---|---:|---|
| `/.env` | 403 | ปฏิเสธ |
| `/composer.json` | 404 | ไม่มีใน public root |
| `/composer.lock` | 404 | ไม่มีใน public root |
| `/database/database.sqlite` | 404 | app อยู่นอก public root |
| `/storage/logs/laravel.log` | 404 | ปฏิเสธ |
| `/vendor` | 404 | อยู่นอก public root |
| `/bootstrap/cache` | 404 | อยู่นอก public root |
| `/assets/` | 403 | ไม่แสดง directory listing |
| `/build/` | 403 | ไม่แสดง directory listing |
| `/plugins/` | 403 | ไม่แสดง directory listing |

- `APP_DEBUG=false` ยังอยู่
- ไม่มี debug information ในหน้าทดสอบ
- ไม่ได้ทำ destructive attack test

---

## K. UI & Localization QA

-  Thai keys = 532, Vietnamese residual = 0, Thai JSON สองไฟล์ตรงกัน
-  Root pages ตรวจด้วย PHP Unicode scan:
  - home / login / register / services / api-docs → `CLEAN`
-  Theme tokens ยังเป็น `#f4f8fd`, `#ffffff`, `#dbe7f3`, `#2563eb`, `#0ea5e9`, `#1e3a5f`, `#64748b`
-  ไม่พบสีม่วง/สี dark เก่าใน assets
-  ไม่เปลี่ยนสกุล VND หรือราคาเดิม

---

## L. Production Smoke Tests

ทั้งหมดทดสอบด้วยการ resolve ชื่อโดเมนไปยัง `122.154.34.166` ก่อน DNS cutover:

| Path | uploading.com | www.uploading.com |
|---|---:|---:|
| `/` | 200 | 200 |
| `/login` | 200 | 200 |
| `/register` | 200 | 200 |
| `/pages/services` | 200 | 200 |
| `/pages/api-docs` | 200 | 200 |
| `/build/manifest.json` | 200 | - |
| `/assets/css/nova-theme.css` | 200 | - |

ไม่มี 500, ไม่มี PHP fatal, ไม่มี missing asset ที่กระทบหน้าทดสอบ

---

## M. Git Commit & Push

```text
git commit -m "feat: complete root domain production migration"
[main 2f44097] feat: complete root domain production migration
 1 file changed, 1 insertion(+)
 create mode 100644 resources/views/components/favicon.blade.php

git push origin HEAD
To https://github.com/aeskeaae-sys/NovaSMM.git
   62e35b1..2f44097  HEAD -> main

HEAD=2f440970aa0d41a5460e0ee55cccd9d773b98a30
origin/main=2f440970aa0d41a5460e0ee55cccd9d773b98a30
```

- ไม่ force push
- ไม่ commit `.env`, credentials, database dump, backup, log, `composer.phar`, `package-lock.json`
- Secret scan ใน staged diff = 0

---

## N. Remaining Risks

1. **DNS ยังอยู่ที่ SiteGround** → `uploading.com` ยังเปิด WordPress เดิมสำหรับ公众
2. **WordPress backup เต็มรูปแบบยังไม่มี** → ต้องสำรองที่ SiteGround ก่อนตัด DNS หากต้องการกู้คืน
3. **SSL บน host เป้าหมายไม่ตรง domain** → ต้อง issue/install cert สำหรับ `uploading.com` หลัง DNS ชี้มา
4. **HTTP → HTTPS ยังไม่บังคับ**
5. **`php artisan route:cache` ยังไม่ผ่าน** จาก duplicate route name `api.v2`
6. **ยังไม่มี authenticated user/admin QA** เพราะไม่มีบัญชีทดสอบ
7. ไฟล์ `index.html` เดิมยังอยู่ใน public root แต่ `DirectoryIndex` เลือก `index.php` ก่อนแล้ว

---

## O. Rollback Instructions

หากหลัง DNS cutover พบปัญหา:

1. ที่ SiteGround เปลี่ยน A record กลับ:
   - `uploading.com` → `34.174.96.8`
   - `www.uploading.com` → `34.174.96.8`
   - หรือเปลี่ยน NS กลับไป `ns1.siteground.net`, `ns2.siteground.net`
2. รอ DNS propagation แล้วตรวจ `Resolve-DnsName uploading.com -Type A`
3. หากต้อง rollback ไฟล์บน host เป้าหมาย:
   - extract `phase15_backups/20261123_public_html_before_root.zip` กลับไป `domains/uploading.com/`
   - extract `phase15_backups/20261123_novasmm_app.zip` หากต้องคืน app config/database
   - ตรวจ `APP_KEY` จาก backup ก่อนใช้งาน
4. ห้ามลบ WordPress database/uploads ที่ SiteGround
5. หลัง rollback ตรวจ `https://uploading.com/` ว่าเป็น WordPress เดิม

---

## P. Final Go / No-Go Decision

| ประเด็น | การตัดสินใจ |
|---|---|
| เตรียม root architecture บน host `122.154.34.166` | **GO** — ตรวจด้วย `--resolve` ผ่านแล้ว |
| เก็บ backup ของ NovaSMM host | **GO** — archive 2 ไฟล์ ≠ 0, DB integrity ok |
| Commit/Push source fix | **GO** — `2f44097` อยู่บน `origin/main` แล้ว |
| เปลี่ยน DNS สาธารณะของ `uploading.com` | **NO-GO** — ไม่มี SiteGround DNS access, ไม่มีการยืนยัน backup WordPress เต็มรูปแบบ |
| ประกาศ Production cutover สำเร็จ | **NO-GO** — ยังติด DNS/SSL gates |

**สรุป Phase 15: BLOCKED เฉพาะขั้นตอน DNS cutover / WordPress replacement สาธารณะ**  
งานฝั่ง repository, backup host เป้าหมาย, root preparation, Laravel fix, smoke test ด้วย IP ตรง และ Git push เสร็จสมบูรณ์แล้ว
