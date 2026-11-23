# NovaSMM Phase 15.13 — DNS Cutover Verification & SSL Go-Live Report

## สรุปบริหาร

**สถานะสุดท้าย: NO-GO**

เหตุผลหลัก (blocking):

1. **DNS ยังไม่เปลี่ยนตามที่คาดหวัง**  
   `uploading.com` และ `www.uploading.com` ยัง resolve ไป `34.174.96.8` (SiteGround/WordPress เดิม) ทุก resolver ที่ตรวจ ไม่พบ A record `122.154.34.166` เลย
2. **SSL บน host เป้าหมายยังไม่ตรง domain**  
   `122.154.34.166` ยังใช้ certificate `CN=mdc-122-154-34-166.sticbox.com` ไม่ครอบคลุม `uploading.com` / `www.uploading.com`; HTTPS verify แบบปกติล้มเหลว (`curl exit=60`)
3. **HTTP → HTTPS redirect บน host เป้าหมายยังไม่เปิด**  
   `http://uploading.com/` เมื่อ resolve ไป `122.154.34.166` ตอบ `200` ไม่ redirect ไป HTTPS
4. **ไม่มีบัญชีที่ได้รับอนุญาตสำหรับทดสอบ Login/Admin จริง**  
   ตรวจได้เฉพาะ guest behavior (เช่น `/admin` → `/login`) ไม่ได้ยืนยัน authenticated dashboard

งานที่ทำได้และผ่านแล้ว:

- ตรวจ DNS/MX/TXT/NS และ propagation แบบ read-only ไม่ได้แก้ DNS อัตโนมัติ
- ตรวจ SSL ของทั้ง IP เดิมและ host เป้าหมาย
- ตรวจ root/www smoke test ทั้ง public DNS และ target host ด้วย `--resolve`
- แก้ duplicate route name `api.v2` และพบ/แก้ duplicate อีกจุด (`admin.providers.forms`) จน `php artisan route:cache` ผ่าน
- deploy ไฟล์ route ที่แก้แล้วไปยัง host เป้าหมายและตรวจ SHA256 ตรงกับ local
- ตรวจความปลอดภัย / SQLite integrity / Thai UI regression
- commit + push source fix แล้ว

---

## A. Git Baseline

| รายการ | ค่า |
|---|---|
| Repository | `C:\Users\tee\NovaSMM` |
| Branch | `main` |
| Remote | `https://github.com/aeskeaae-sys/NovaSMM.git` |
| Commits ก่อนเรงาน | `0231d92`, `2f44097` |
| HEAD หลังงานนี้ | `1a123265ba1510203765eaaaf5a0780cadc1ee94` |
| origin/main หลัง push | `1a123265ba1510203765eaaaf5a0780cadc1ee94` |
| Working tree | มีเพียง untracked `composer.phar`, `package-lock.json` (ตั้งใจไม่ stage) |
| Force push | ไม่ได้ทำ |
| ZIP ใหม่ | ไม่ได้สร้าง |

Commit ใหม่:

```text
1a12326 fix: resolve duplicate route names for route cache
```

---

## B. DNS Verification

### B.1 A Records — Expected vs Actual

Expected:

```text
uploading.com      A = 122.154.34.166
www.uploading.com  A = 122.154.34.166
```

Actual จาก public resolvers:

| Resolver | uploading.com | www.uploading.com |
|---|---|---|
| Google `8.8.8.8` | `34.174.96.8` | `34.174.96.8` |
| Cloudflare `1.1.1.1` | `34.174.96.8` | `34.174.96.8` |
| OpenDNS `208.67.222.222` | `34.174.96.8` | `34.174.96.8` |

**ผล: FAIL** — ทุก resolver ตรงกันว่ายังเป็น IP เดิม ไม่ใช่ IP เป้าหมาย

TTL ที่เห็น: `3600`

### B.2 MX — ยังคงเดิม

| Preference | Exchange |
|---:|---|
| 10 | `mx10.antispam.mailspamprotection.com` |
| 20 | `mx20.antispam.mailspamprotection.com` |
| 30 | `mx30.antispam.mailspamprotection.com` |

**ผล: PASS** (ไม่ถูกกระทบ)

### B.3 TXT — ยังคงเดิม

| ประเภท | ค่า |
|---|---|
| SPF | `v=spf1 +a +mx +ip4:34.174.30.12 include:uploading.com.spf.auto.dnssmarthost.net ~all` |
| Verification | `google-site-verification=...` **(redacted — ไม่แสดงค่าฉบับเต็ม)** |

**ผล: PASS** (ไม่ถูกกระทบ)

### B.4 NS — ยังคงเดิม

```text
ns1.siteground.net
ns2.siteground.net
```

**ผล: ยังอยู่ SiteGround** จึงยืนยันว่าไม่มีการเปลี่ยน registrar/zone ไปยัง host เป้าหมาย

### B.5 Propagation Conclusion

- ไม่ใช่ partial propagation — ทุก resolver เห็นค่าเดิมเหมือนกัน
- ไม่ได้แก้ DNS จาก Repository หรือ DirectAdmin ตามข้อกำหนด
- ต้องให้ผู้ดูแล SiteGround เปลี่ยน A record (`@` และ `www`) เป็น `122.154.34.166` แล้วรอ propagation ก่อนพิจารณา GO อีกครั้ง

---

## C. SSL Verification

ทดสอบด้วย SNI `uploading.com` และพอร์ต 443 ของทั้งสอง IP

### C.1 IP เดิม / Public DNS — `34.174.96.8`

| รายการ | ค่า |
|---|---|
| Subject | `CN=*.uploading.com` |
| SAN | `DNS=*.uploading.com`, `DNS=uploading.com` |
| Issuer | `CN=YR1, O=Let's Encrypt, C=US` |
| NotBefore | `2026-11-22T22:09:42+07:00` |
| NotAfter | `2027-02-20T22:09:41+07:00` |
| Chain | `ChainBuild=True`, `ChainStatus=` (ไม่มี error) |
| Protocol | `TLS 1.3` |
| Verify ปกติ | `curl` exit `0`, HTTP `200` |

หมายเหตุ: wildcard `*.uploading.com` ครอบคลุม `www.uploading.com` ได้ แต่ certificate นี้เป็นของ WordPress เดิมบน SiteGround ไม่ใช่ host เป้าหมาย

### C.2 Host เป้าหมาย — `122.154.34.166`

| รายการ | ค่า |
|---|---|
| Subject | `CN=mdc-122-154-34-166.sticbox.com` |
| SAN | `DNS=mdc-122-154-34-166.sticbox.com` |
| Issuer | `CN=YE2, O=Let's Encrypt, C=US` |
| NotBefore | `2026-10-20T10:53:36+07:00` |
| NotAfter | `2027-01-18T10:53:35+07:00` |
| Chain | `ChainBuild=True`, `ChainStatus=` (chain ใช้ได้) |
| Protocol | `TLS 1.3` |
| Verify ปกติ | **FAIL** — `curl exit=60`, HTTP `000` |
| Verify ด้วย `-k` | HTTP `200` (แต่ไม่ยืนยัน hostname) |

**ผล: FAIL สำหรับ go-live** — ต้อง issue/install certificate ใหม่ที่ครอบคลุม `uploading.com` และ `www.uploading.com` บน host เป้าหมาย ห้ามใช้ cert `sticbox.com` แทน

---

## D. Root / www QA

### D.1 ตาม Public DNS จริง (ยังไป WordPress เดิม)

| URL | ผล |
|---|---|
| `https://uploading.com/` | `200`, body มี `<meta name="generator" content="WordPress 7.1.5" />` |
| `https://uploading.com/login` | `302` |
| `https://uploading.com/pages/services` | `301` |
| `https://uploading.com/pages/api-docs` | `301` |
| `https://www.uploading.com/*` | ส่วนใหญ่ `301` ไปยัง host เดิม |

**ผล: FAIL สำหรับ NovaSMM** — public ยังไม่ได้เสิร์ฟแอปเป้าหมาย

### D.2 Host เป้าหมายด้วย `--resolve` ไป `122.154.34.166`

| Path | uploading.com | www.uploading.com |
|---|---|---|
| `/` | `200` / `36075` bytes | `200` / `36155` bytes |
| `/login` | `200` / `10991` bytes | `200` / `11035` bytes |
| `/register` | `200` / `11903` bytes | `200` / `11951` bytes |
| `/pages/services` | `200` / `141167` bytes | `200` / `141303` bytes |
| `/pages/api-docs` | `200` / `166639` bytes | `200` / `166783` bytes |
| `/build/manifest.json` | `200` / `953` bytes | `200` / `953` bytes |
| `/assets/css/nova-theme.css` | `200` / `17910` bytes | `200` / `17910` bytes |

**ผล: PASS** (เฉพาะ host เป้าหมาย / ยังไม่ใช่ public)

### D.3 HTTP → HTTPS

| Target ของ resolve | ผล |
|---|---|
| `34.174.96.8` (WordPress เดิม) | `301 → https://uploading.com/` |
| `122.154.34.166` (NovaSMM เป้าหมาย) | `200` **ไม่ redirect** |

**ผล: FAIL บน host เป้าหมาย** — ต้องเปิด forced HTTPS หลัง SSL ถูกต้อง

---

## E. Laravel Route Fix

### E.1 ปัญหาที่พบ

`php artisan route:cache` เคย fail สองจุด:

1. `api/v2` — duplicate name `api.v2`
2. `admin/services/load-forms/{type}` — duplicate name `admin.providers.forms` (โผล่หลังแก้จุดแรก)

### E.2 การตรวจสอบก่อนแก้

| รายการ | ผล |
|---|---|
| Route definitions | `routes/api.php:117-118`, `routes/web.php:202-203` |
| Controller | ใช้ controller เดียวกันทั้ง GET/POST (`Api\V2Controller@process`, `Admin\ServiceController@forms`) |
| Middleware | อยู่ใน group เดิม ไม่ได้เปลี่ยน |
| API consumers | ไม่พบ `route('api.v2')` ในโค้ด; docs ใช้ literal URL `https://{{ domain() }}/api/v2` และระบุวิธี `POST` |
| POST form consumer | `resources/views/admin/services/forms/api.blade.php` ใช้ `route('admin.providers.forms', ['type' => 'api'])` → คงชื่อเดิมไว้ |
| GET consumers | ใช้ literal `/admin/services/load-forms/...` → ปลอดภัยต่อการ rename เฉพาะ GET |

### E.3 สิ่งที่แก้ (minimal change)

```diff
- Route::get('/v2', ...)->name('api.v2');
+ Route::get('/v2', ...)->name('api.v2.get');
  Route::post('/v2', ...)->name('api.v2');

- Route::get('/load-forms/{type}', ...)->name('admin.providers.forms');
+ Route::get('/load-forms/{type}', ...)->name('admin.providers.forms.get');
  Route::post('/load-forms/{type}', ...)->name('admin.providers.forms');
```

- URI, method, middleware, controller, และพฤติกรรมไม่เปลี่ยน
- ไม่ได้ลบ endpoint ใด
- `POST api/v2` ยังเป็นชื่อ `api.v2` ตามที่ docs ใช้
- `POST admin/services/load-forms/{type}` ยังเป็นชื่อ `admin.providers.forms` ตามที่ blade ใช้

### E.4 ผลทดสอบ

```text
duplicate names=0
GET|HEAD api/v2                     => api.v2.get
POST    api/v2                     => api.v2
GET|HEAD admin/services/load-forms/{type} => admin.providers.forms.get
POST    admin/services/load-forms/{type} => admin.providers.forms

php artisan route:clear  PASS
php artisan route:cache  PASS
php artisan route:clear  PASS
php -l routes/api.php    PASS
php -l routes/web.php    PASS
```

### E.5 Deployment ไป host เป้าหมาย

- Backup remote route files ก่อน overwrite แล้ว (temp, ไม่ได้ commit)
- Upload ด้วย DirectAdmin API `overwrite=true` ไปยัง:
  - `domains/uploading.com/novasmm_app/routes/api.php`
  - `domains/uploading.com/novasmm_app/routes/web.php`
- ตรวจ SHA256 หลัง download กลับ: **ตรงกับ local ทั้ง 2 ไฟล์**
- `bootstrap/cache/` บน remote ไม่มี `routes-v7.php` (ไม่มี stale route cache)

### E.6 Smoke หลัง deploy

| Endpoint | ผล |
|---|---|
| `GET /api/v2` | `400 application/json` (validation error — endpoint ยังอยู่) |
| `POST /api/v2` | `400 application/json` (validation error — endpoint ยังอยู่) |
| `GET /admin/services/load-forms/api` | `302 → /login` (route ยังอยู่, ถูก middleware กัน) |
| `POST /admin/services/load-forms/api` | `419` (CSRF — ปกติสำหรับ web route ที่ไม่มี token) |

---

## F. Authentication / Admin QA

| รายการ | ผล |
|---|---|
| `/admin` | `302 → https://uploading.com/login` |
| `/login` | `200` |
| `/register` | `200` |
| Login จริงด้วยบัญชีที่ได้รับอนุญาต | **ไม่ได้ทดสอบ** — ไม่มีบัญชีทดสอบที่ได้รับอนุญาตในงานนี้ |
| Admin dashboard หลัง login | **ไม่ได้ทดสอบ** |

**ผล: INCOMPLETE** — ต้องได้รับบัญชีที่ได้รับอนุญาตก่อนยืนยัน GO

---

## G. Database / Production Security

### G.1 SQLite

| รายการ | ค่า |
|---|---|
| Remote DB | `domains/uploading.com/novasmm_app/database/database.sqlite` |
| Download ไป temp | `phase15_13_remote_db.sqlite` (249,856 bytes) |
| `PRAGMA integrity_check` | `ok` |
| `languages.default` column | มี (schema ใช้ `default`, ไม่ใช่ `is_default`) |
| `th` default | `1` |
| `vn` default | `0` |
| `configs.general` | `{"primary_lang":"th"}` |
| Web download `/database/database.sqlite` | `404` |

**ผล: PASS**

### G.2 Sensitive Files / Debug

| Path | ผล |
|---|---|
| `/.env` | `403` |
| `/composer.json` | `404` |
| `/composer.lock` | `404` |
| `/storage/logs/laravel.log` | `404` |
| `/routes/api.php` | `404` |
| `/vendor` | `404` |
| `/bootstrap/cache` | `404` |
| `/assets/` | `403` |
| `/build/` | `403` |
| `/plugins/` | `403` |
| Remote `.env` `APP_ENV` | `production` |
| Remote `.env` `APP_DEBUG` | `false` |

**ผล: PASS** — ไม่พบ credentials ในรายงานนี้ ค่าที่ละเอียดอ่อนถูก redact/ไม่ได้พิมพ์

### G.3 Thai UI Regression (host เป้าหมาย)

สแกน HTML จาก `/`, `/login`, `/register`, `/pages/services`, `/pages/api-docs` ด้วย resolve ไป host เป้าหมาย:

```text
home=CLEAN
login=CLEAN
register=CLEAN
services=CLEAN
api=CLEAN
```

**ผล: PASS**

---

## H. Final Decision

### **NO-GO**

ยังไม่เปลี่ยน public DNS → ยังไม่ไป host เป้าหมาย และยังไม่มี SSL ที่ถูกต้องสำหรับ domain บน host เป้าหมาย

Critical blockers ที่ต้องปิดก่อน GO อีกครั้ง:

| # | Blocker | ต้องทำ |
|---:|---|---|
| 1 | A record ยังเป็น `34.174.96.8` | เปลี่ยน A `@` และ `www` เป็น `122.154.34.166` ที่ SiteGround แล้วรอ propagation |
| 2 | SSL target ไม่ตรง domain | ออก/ติดตั้ง certificate สำหรับ `uploading.com` + `www.uploading.com` บน `122.154.34.166` |
| 3 | ไม่มี HTTP→HTTPS redirect บน target | เปิด forced HTTPS หลัง SSL ถูกต้อง |
| 4 | ไม่มีบัญชีทดสอบที่ได้รับอนุญาต | ต้องได้รับบัญชีเพื่อทดสอบ Login/Admin จริง |

สิ่งที่ **ผ่านแล้ว** และไม่ต้องทำซ้ำเว้นแต่มีการเปลี่ยนแปลง:

- Route fix + `route:cache` PASS + deploy hash ตรง
- SQLite integrity/lang config
- Security sensitive paths
- Root/www smoke test ด้วย `--resolve`
- Thai UI regression

---

## I. หลักฐาน/คำสั่งที่ใช้ (สรุป)

```powershell
Resolve-DnsName uploading.com -Type A -Server 8.8.8.8
Resolve-DnsName www.uploading.com -Type A -Server 1.1.1.1
Resolve-DnsName uploading.com -Type MX
Resolve-DnsName uploading.com -Type TXT
Resolve-DnsName uploading.com -Type NS

curl.exe -k -s -o NUL -w '%{http_code} %{size_download}' --resolve 'uploading.com:443:122.154.34.166' 'https://uploading.com/'
curl.exe -s -o NUL -w '%{http_code}' --resolve 'uploading.com:80:122.154.34.166' 'http://uploading.com/'

C:\xampp\php\php.exe artisan route:list --json
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan route:cache
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe -l routes/api.php
C:\xampp\php\php.exe -l routes/web.php
```

Secret-like matches during staged diff review: `0`

---

## J. Next Action

1. แจ้งผู้ดูแล SiteGround ให้เปลี่ยน A records (`@`, `www`) เป็น `122.154.34.166` โดยคง MX/TXT ไว้
2. รอ propagation แล้วตรวจซ้ำด้วย public resolvers
3. ออก/ติดตั้ง SSL สำหรับ `uploading.com` + `www.uploading.com` บน host เป้าหมาย และเปิด HTTPS redirect
4. รับบัญชีทดสอบที่ได้รับอนุญาต แล้วทดสอบ Login/Admin จริง
5. ทำ Phase 15.13 re-verification และเปลี่ยน decision เป็น GO ได้เฉพาะเมื่อทุกข้อผ่าน

**รายงานนี้ไม่มีการเปิดเผยข้อมูลรับรองใดๆ**
