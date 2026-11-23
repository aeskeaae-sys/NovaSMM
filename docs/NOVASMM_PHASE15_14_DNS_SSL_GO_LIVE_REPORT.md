# NovaSMM Phase 15.14 — DNS Cutover, SSL Installation & HTTPS Go-Live Re-Verification

## สรุปบริหาร

**สถานะสุดท้าย: NO-GO**

ยังไม่ประกาศ Production Go-Live เพราะเงื่อนไขสำคัญยังไม่ผ่าน:

1. **DNS ยังไม่ cutover** — ทั้ง root และ www ยัง resolve ไป `34.174.96.8` ทุก resolver (Google / Cloudflare / Quad9) ไม่พบ `122.154.34.166`
2. **SSL บน host เป้าหมายยังไม่ตรง domain** — ยังเป็น `CN=mdc-122-154-34-166.sticbox.com`; `curl` แบบเปิด verification ปกติ exit `60`
3. **HTTP → HTTPS redirect บน host เป้าหมายยังไม่มี** — `http://uploading.com/` และ `http://www.uploading.com/` เมื่อ resolve ไป `122.154.34.166` ตอบ `200` ไม่มี `Location`
4. **Login/Admin จริง**: `NOT TESTED — NO AUTHORIZED TEST ACCOUNT`

สิ่งที่ผ่านแล้วและยืนยันซ้ำได้:

- Git baseline ตรง `c1bdb30`, ไม่มี source change จาก Phase 15.13
- MX/TXT/NS ยังคงเดิม ไม่ถูกกระทบ
- แอปบน host เป้าหมายยังทำงาน (ตรวจผ่าน HTTP แบบ resolve เพื่อดูแอปเท่านั้น ไม่ใช้เป็นหลักฐาน SSL)
- SQLite integrity `ok`, `APP_DEBUG=false`, sensitive paths ยังปิดดี
- `php artisan route:cache` ผ่าน; route fix จาก 15.13 ยังอยู่ทั้ง local และ remote

---

## A. Baseline และ Git Audit

| รายการ | ค่า |
|---|---|
| Repository | `C:\Users\tee\NovaSMM` |
| Branch | `main` |
| Report ก่อนหน้า | `docs/NOVASMM_PHASE15_13_DNS_SSL_GO_LIVE_REPORT.md` |
| HEAD ก่อนเรงาน | `c1bdb300f5223f1526f0ea8b6f5dac07238472fd` |
| `origin/main` ก่อนเรงาน | `c1bdb300f5223f1526f0ea8b6f5dac07238472fd` |
| ความสัมพันธ์ local/origin | `0 0` (ตรงกัน) |
| Working tree ก่อนเรงาน | มีเพียง untracked `composer.phar`, `package-lock.json` |
| Reset/discard | ไม่ได้ทำ |
| Force push | ไม่ได้ทำ |
| ZIP ใหม่ | ไม่ได้สร้าง |
| Source changes ในเฟสนี้ | ไม่มี — มีเพียงรายงานฉบับนี้ |

**ผล: PASS**

---

## B. DNS Verification

Timestamp ของการตรวจ: `2026-11-23T19:34:59.7051406+07:00`

Expected:

```text
uploading.com      A → 122.154.34.166
www.uploading.com  A → 122.154.34.166
```

### B.1 ผล A / AAAA / CNAME ต่อ resolver

| Resolver | Hostname | Record | ค่า | TTL |
|---|---|---|---|---:|
| Google `8.8.8.8` | `uploading.com` | A | `34.174.96.8` | 20716 |
| Google `8.8.8.8` | `www.uploading.com` | A | `34.174.96.8` | 21600 |
| Cloudflare `1.1.1.1` | `uploading.com` | A | `34.174.96.8` | 86400 |
| Cloudflare `1.1.1.1` | `www.uploading.com` | A | `34.174.96.8` | 86400 |
| Quad9 `9.9.9.9` | `uploading.com` | A | `34.174.96.8` | 38305 |
| Quad9 `9.9.9.9` | `www.uploading.com` | A | `34.174.96.8` | 41052 |
| ทุก resolver | ทั้งสอง hostname | AAAA | ไม่มี | — |
| ทุก resolver | ทั้งสอง hostname | CNAME | ไม่มี | — |

**ผล A record: FAIL** — ไม่มี resolver ใดเห็น `122.154.34.166`

### B.2 MX — ยังคงเดิม

| Preference | Exchange | TTL |
|---:|---|---:|
| 10 | `mx10.antispam.mailspamprotection.com` | 21600 |
| 20 | `mx20.antispam.mailspamprotection.com` | 21600 |
| 30 | `mx30.antispam.mailspamprotection.com` | 21600 |

**ผล: PASS — ไม่เปลี่ยนแปลง**

### B.3 TXT — ยังคงเดิม

| ประเภท | ค่า | TTL |
|---|---|---:|
| SPF | `v=spf1 +a +mx +ip4:34.174.30.12 include:uploading.com.spf.auto.dnssmarthost.net ~all` | 14400 |
| Site verification | `google-site-verification=...` **(redacted — ไม่แสดงค่าฉบับเต็ม)** | 14400 |

**ผล: PASS — ไม่เปลี่ยนแปลง / ไม่มี secret ใดถูกเปิดเผย**

### B.4 NS — ยังอยู่ SiteGround

```text
ns1.siteground.net
ns2.siteground.net
```

TTL: `21600`

### B.5 Propagation Conclusion

- ผลตรงกันทุก resolver → **ไม่ใช่ partial propagation**
- ยังพบ IP เก่า `34.174.96.8` → ตามข้อกำหนดต้องคงสถานะ **NO-GO**
- ไม่ได้แก้ DNS จาก Repository หรือ host ตามข้อจำกัด

### B.6 Re-verification

Timestamp ของการตรวจซ้ำ: `2026-11-23T23:00:25.6626987+07:00`

| Resolver | `uploading.com` | TTL | `www.uploading.com` | TTL |
|---|---|---:|---|---:|
| Google `8.8.8.8` | `A 34.174.96.8` | 21484 | `A 34.174.96.8` | 21431 |
| Cloudflare `1.1.1.1` | `A 34.174.96.8` | 86400 | `A 34.174.96.8` | 86400 |
| Quad9 `9.9.9.9` | `A 34.174.96.8` | 25979 | `A 34.174.96.8` | 28726 |

- `AAAA NONE`, `CNAME NONE` สำหรับทั้งสอง hostname ทุก resolver
- MX/SPF/site verification/NS ยังคงเดิม (ค่า site verification ยัง redacted)
- ผลตรวจซ้ำตรงกับรอบแรก → ยัง **FAIL** และยัง **NO-GO**

---

## C. SSL Verification

การตรวจ `curl` ด้านล่าง **เปิด certificate verification ตามปกติ** (ไม่ใช้ `-k` / ไม่ปิด verify)  
การอ่าน subject/SAN ใช้การดึง certificate มาแสดงข้อมูลเท่านั้น และไม่ได้ใช้เป็นหลักฐานว่า verify ผ่าน

### C.1 `curl` แบบ verify ปกติ

| เป้าหมาย | ผล curl | `ssl_verify_result` | Exit code |
|---|---|---:|---:|
| `https://uploading.com/` | `200`, remote `34.174.96.8` | `0` | `0` |
| `https://www.uploading.com/` | `301`, remote `34.174.96.8` | `0` | `0` |
| `https://uploading.com/` resolve → `122.154.34.166` | `000` | — | `60` |
| `https://www.uploading.com/` resolve → `122.154.34.166` | `000` | — | `60` |

**ผล: FAIL สำหรับ host เป้าหมาย** — exit `60` คือ certificate verification/hostname mismatch

Re-verification ที่ `2026-11-23T23:00:25+07:00` ให้ผลเหมือนเดิม: public `200|0|34.174.96.8 exit=0`, public `www` `301|0|34.174.96.8 exit=0`, target ทั้งสอง hostname `000|0| exit=60`

### C.2 Certificate บน public IP เดิม `34.174.96.8`

| รายการ | ค่า |
|---|---|
| Subject | `CN=*.uploading.com` |
| SAN | `DNS=*.uploading.com`, `DNS=uploading.com` |
| Issuer | `CN=YR1, O=Let's Encrypt, C=US` |
| NotBefore | `2026-11-22T22:09:42+07:00` |
| NotAfter | `2027-02-20T22:09:41+07:00` |
| Chain | `ChainBuild=True`, `ChainStatus=` (ไม่มี error) |
| Protocol | TLS 1.3 |
| หมายเหตุ | ใช้ได้กับ domain แต่เป็น certificate ของ WordPress เดิมบน SiteGround ไม่ใช่ host เป้าหมาย |

### C.3 Certificate บน host เป้าหมาย `122.154.34.166`

| รายการ | ค่า |
|---|---|
| Subject | `CN=mdc-122-154-34-166.sticbox.com` |
| SAN | `DNS=mdc-122-154-34-166.sticbox.com` |
| Issuer | `CN=YE2, O=Let's Encrypt, C=US` |
| NotBefore | `2026-10-20T10:53:36+07:00` |
| NotAfter | `2027-01-18T10:53:35+07:00` |
| Chain | `ChainBuild=True`, `ChainStatus=` (chain ใช้ได้) |
| Protocol | TLS 1.3 |
| ครอบคลุม `uploading.com` | **ไม่** |
| ครอบคลุม `www.uploading.com` | **ไม่** |
| Certificate mismatch | **มี** |

**ผล: FAIL** — ต้องออก certificate ใหม่สำหรับ `uploading.com` และ `www.uploading.com` ผ่าน SiteGround หรือระบบจัดการ SSL ของ host เป้าหมาย ห้ามใช้ certificate `sticbox.com` แทน

### C.4 หลักฐาน ACME จากระบบ Host (read-only)

| การตรวจ | ผล |
|---|---|
| รายการ domain TLS | ACME เปิดอยู่, แต่ยังไม่มี cert สำหรับ domain, `dnsNames missing=6` |
| Dry-run provision certificate | HTTP 200, `dnsNamesFailedChallenge` มี `uploading.com` และ `www.uploading.com` (รวม alias อื่น) |
| Rate limit | `ratelimitReached=false` |
| สรุป | ACME ยังออก cert ไม่ได้เพราะ DNS ยังไม่ชี้มาที่ host นี้ |

---

## D. HTTP → HTTPS Redirect

ตรวจทั้ง status code และ `Location` header (ไม่ใช้ `-k`)

| เป้าหมาย | Status | Location | ผล |
|---|---|---|---|
| `http://uploading.com/` (public DNS) | `301 Moved Permanently` | `https://uploading.com/` | PASS |
| `http://www.uploading.com/` (public DNS) | `301 Moved Permanently` | `https://www.uploading.com/` | PASS |
| `http://uploading.com/` resolve → `122.154.34.166` | `200 OK` | ไม่มี | **FAIL** |
| `http://www.uploading.com/` resolve → `122.154.34.166` | `200 OK` | ไม่มี | **FAIL** |

- ไม่พบ redirect loop บน public (HTTPS ปลายทางตอบ `200`, exit `0`)
- hostname ปลายทางถูกต้องสำหรับ public เดิม
- บน host เป้าหมายยังไม่มี forced HTTPS

### D.1 การตรวจก่อนแก้ redirect

- ตรวจ `domains/uploading.com/public_html/.htaccess` แล้ว: มี `RewriteEngine On`, `RewriteBase /`, `DirectoryIndex index.php index.html`, กฎ authorization/front-controller เท่านั้น **ยังไม่มีกฎ HTTPS redirect**
- ตรวจ virtual host ผ่าน API ที่ได้รับอนุญาตแล้ว: ไม่มี Config ที่ออก SSL ของ domain นี้ให้ใช้; ยังไม่มี cert ที่ติดตั้งไว้
- **ไม่ได้แก้ `.htaccess` หรือ vhost ในรอบนี้** เพราะ DNS/SSL ยังไม่พร้อม; การบังคับ HTTPS ตอนนี้จะพาผู้ใช้ไปยัง certificate ที่ mismatch

**ผล: FAIL (รอ DNS + SSL ถูกต้องก่อนตั้งค่า)**

---

## E. Production Smoke Tests

### E.1 ตาม Public DNS จริง (ยังไป WordPress เดิม)

| URL | ผล |
|---|---|
| `https://uploading.com/` | `200` — ยังเป็น WordPress เดิม |
| `https://www.uploading.com/` | `301` ไปยัง canonical เดิม |
| `https://uploading.com/login` | `302` |
| `https://uploading.com/pages/services` | `301` |

**ผล: FAIL สำหรับ NovaSMM go-live** — public ยังไม่ได้เสิร์ฟแอปเป้าหมาย

### E.2 Host เป้าหมายด้วย `--resolve` ไป `122.154.34.166` ผ่าน HTTP

ใช้ HTTP เพื่อตรวจตัวแอปเท่านั้น **ไม่ใช้เป็นหลักฐานว่า HTTPS/SSL ผ่าน**

| Path | uploading.com | www.uploading.com |
|---|---|---|
| `/` | `200` / 36075 bytes | `200` / 36155 bytes |
| `/login` | `200` / 10991 bytes | `200` / 11035 bytes |
| `/register` | `200` / 11903 bytes | `200` / 11951 bytes |
| `/pages/services` | `200` / 141167 bytes | `200` / 141303 bytes |
| `/pages/api-docs` | `200` / 166639 bytes | `200` / 166783 bytes |
| `/build/manifest.json` | `200` / 953 bytes | `200` / 953 bytes |
| `/assets/css/nova-theme.css` | `200` / 17910 bytes | `200` / 17910 bytes |
| `/api/v2` (GET) | `400 application/json` | — |
| `/api/v2` (POST) | `400 application/json` | — |

**ผล: PASS เฉพาะแอปบน host เป้าหมาย**

### E.3 Thai UI Regression

สแกน HTML ของ `/`, `/login`, `/register`, `/pages/services`, `/pages/api-docs` จาก host เป้าหมาย:

```text
home=CLEAN
login=CLEAN
register=CLEAN
services=CLEAN
api=CLEAN
```

**ผล: PASS**

### E.4 Login / Admin จริง

**NOT TESTED — NO AUTHORIZED TEST ACCOUNT**

ตรวจได้เฉพาะ guest behavior (`/admin` → `/login`) แต่ไม่มีบัญชีทดสอบที่ได้รับอนุญาต จึงไม่กล่าวอ้างว่า Login/Admin ผ่าน

---

## F. Database, Security และ Route Health

### F.1 Database

| รายการ | ค่า |
|---|---|
| ไฟล์ | `domains/uploading.com/novasmm_app/database/database.sqlite` |
| ขนาดที่ download มาตรวจ | 249,856 bytes |
| `PRAGMA integrity_check` | `ok` |
| `languages.default` สำหรับ `th` | `1` |
| `languages.default` สำหรับ `vn` | `0` |
| `configs.general` | `{"primary_lang":"th"}` |

**ผล: PASS**

### F.2 Sensitive Files / Debug

| Path | ผล |
|---|---|
| `/.env` | `403` |
| `/database/database.sqlite` | `404` |
| `/storage/logs/laravel.log` | `404` |
| `/composer.json` | `404` |
| `/composer.lock` | `404` |
| `/bootstrap/cache` | `404` |
| `/vendor` | `404` |
| `/routes/api.php` | `404` |
| `/assets/` | `403` |
| `/build/` | `403` |
| `/plugins/` | `403` |
| Remote `APP_ENV` | `production` |
| Remote `APP_DEBUG` | `false` |

**ผล: PASS**

### F.3 Security Headers

| Target | Headers ที่ตรวจพบ |
|---|---|
| Public `https://uploading.com/` | `Referrer-Policy: no-referrer-when-downgrade` |
| Public `http://uploading.com/` | `301` + `Location: https://uploading.com/` |
| Host เป้าหมายผ่าน HTTP | ไม่พบ `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy` |

**ผล: WARNING** — ยังไม่ควรเพิ่ม HSTS ก่อนมี SSL ที่ถูกต้อง; ควรเพิ่ม hardening headers หลัง HTTPS ผ่าน

### F.4 Route Cache / Application Health

| รายการ | ผล |
|---|---|
| duplicate route names จาก `route:list --json` | `0` (total routes `172`) |
| `GET /api/v2` | ชื่อ `api.v2.get` |
| `POST /api/v2` | ชื่อ `api.v2` |
| `GET /admin/services/load-forms/{type}` | ชื่อ `admin.providers.forms.get` |
| `POST /admin/services/load-forms/{type}` | ชื่อ `admin.providers.forms` |
| `php artisan route:clear` | PASS |
| `php artisan route:cache` | PASS (`exit=0`) |
| `php artisan route:clear` หลังทดสอบ | PASS |
| `php -l routes/api.php` | PASS |
| `php -l routes/web.php` | PASS |
| Remote route files | SHA256 ตรงกับ local ทั้ง `api.php` และ `web.php` |
| Middleware/Permission | ไม่ได้เปลี่ยน |

**ผล: PASS**

---

## G. Git และ Deployment Audit

| รายการ | ผล |
|---|---|
| `git diff` | ไม่มี source change ในเฟสนี้ |
| `git diff --cached` | ไม่มีไฟล์ source ที่ staged |
| Secret scan | `0` match |
| `.env` / DB / logs / backup / credentials | ไม่ได้ stage, ไม่ได้ commit |
| Source commit ใหม่ | **ไม่มี** ตามข้อกำหนด |
| รายงาน | `docs/NOVASMM_PHASE15_14_DNS_SSL_GO_LIVE_REPORT.md` |
| Force push | ไม่ได้ทำ |
| HEAD ก่อนรายงาน | `c1bdb300f5223f1526f0ea8b6f5dac07238472fd` |
| `origin/main` ก่อนรายงาน | `c1bdb300f5223f1526f0ea8b6f5dac07238472fd` |
| Commit/Push status | เฉพาะรายงานจะถูก commit/push หลังตรวจ secret อีกครั้ง; ไม่มี source deployment ใหม่ |

---

## H. Blockers และสิ่งที่ต้องดำเนินการ

| # | Blocker | ผู้ที่ต้องดำเนินการ | รายละเอียด |
|---:|---|---|---|
| 1 | DNS ยังชี้ `34.174.96.8` | SiteGround / DNS administrator | เปลี่ยน A `@` และ `www` เป็น `122.154.34.166`; คง MX/TXT/SPF/NS เดิม; รอ propagation แล้วตรวจซ้ำหลาย resolver |
| 2 | SSL target ยังเป็น `sticbox.com` | SiteGround / ระบบ SSL ของ host | ออก certificate ใหม่ที่ครอบคลุม `uploading.com` + `www.uploading.com` หลัง DNS ชี้มาที่ host; ต้องทำให้ `curl` exit `0` โดยไม่ใช้ `-k` |
| 3 | ไม่มี HTTP→HTTPS redirect บน target | Host administrator | หลัง SSL ถูกต้อง ให้เพิ่มกฎ redirect ใน `.htaccess`/vhost โดยไม่ทับกฎ rewrite เดิม |
| 4 | ไม่มีบัญชีทดสอบที่ได้รับอนุญาต | ผู้ดูแลแอป | ให้บัญชีทดสอบเพื่อยืนยัน Login/Admin จริง |
| 5 | Security headers ยังไม่ครบ | Host/application administrator | พิจารณาเพิ่ม HSTS, X-Frame-Options, X-Content-Type-Options, CSP หลัง HTTPS เสถียร |

---

## I. เกณฑ์ GO / NO-GO

| เกณฑ์ | ผล |
|---|---|
| 1. DNS A root + www ชี้ `122.154.34.166` จากหลาย resolver | **FAIL** |
| 2. SSL ถูกต้องสอง hostname + `curl` verify ผ่าน | **FAIL** |
| 3. HTTP → HTTPS redirect ทำงาน | **FAIL** บน host เป้าหมาย |
| 4. Root/www + smoke tests ผ่าน | **PASS เฉพาะ host เป้าหมาย**, FAIL สำหรับ public DNS |
| 5. Database + security checks ผ่าน | **PASS** (ยกเว้น headers เป็น warning) |
| 6. ไม่มี critical blocker | **FAIL** |

### การตัดสินใจสุดท้าย: **NO-GO**

ห้ามประกาศ Production Go-Live จนกว่า blocker ข้อ 1–4 จะปิดและ re-verify ผ่านทั้งหมด

---

## J. คำสั่ง/หลักฐานสำคัญ

```powershell
Resolve-DnsName uploading.com -Type A -Server 8.8.8.8
Resolve-DnsName www.uploading.com -Type A -Server 1.1.1.1
Resolve-DnsName uploading.com -Type A -Server 9.9.9.9

curl.exe -sS -o NUL -w '%{http_code}|%{ssl_verify_result}|%{remote_ip}' 'https://uploading.com/'
curl.exe -sS -o NUL -w '%{http_code}|%{ssl_verify_result}|%{remote_ip}' --resolve 'uploading.com:443:122.154.34.166' 'https://uploading.com/'
curl.exe -sS -D - -o NUL 'http://uploading.com/'

C:\xampp\php\php.exe artisan route:list --json
C:\xampp\php\php.exe artisan route:cache
C:\xampp\php\php.exe artisan route:clear
```

**รายงานนี้ไม่มีการเปิดเผย Password, API Key, Token หรือ Host Credentials**
