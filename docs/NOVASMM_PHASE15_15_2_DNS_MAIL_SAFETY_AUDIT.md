# NovaSMM Phase 15.15.2 — DNS Cutover Re-verification & Mail DNS Safety Audit

## 0. User Confirmation

ผู้ดูแลยืนยันว่า **ตั้งใจย้ายทั้งระบบ**:

- ย้าย DNS จาก SiteGround (`ns1/ns2.siteground.net`) ไปยัง `ns3.sogoodhost.com` / `ns4.sogoodhost.com`
- เปลี่ยนระบบอีเมลไปใช้ `mail.uploading.com` พร้อม SPF ใหม่

จึงถูกระบุเป็น **MAIL-DNS-CHANGE: CONFIRMED INTENDED**
อย่างไรก็ตาม ยังต้องประเมินความเสี่ยงของค่าที่ตรวจพบจริงก่อนอนุญาต Phase 15.16

---

## 1. Repository Baseline

| รายการ | ค่า | ผล |
|---|---|---|
| Repository | `C:\Users\tee\NovaSMM` | PASS |
| Branch | `main` | PASS |
| Expected HEAD | `4270aa7` | PASS |
| Actual HEAD | `4270aa70b5e03d00a6b254c3318a303742008964` | PASS |
| `origin/main` | `4270aa70b5e03d00a6b254c3318a303742008964` | PASS |
| Ahead/behind | `0 0` | PASS |
| Staged files | 0 | PASS |
| Unstaged source diff | ไม่มี | PASS |
| Reset/checkout ทับ | ไม่ได้ทำ | PASS |
| ZIP/Repository ใหม่ | ไม่ได้สร้าง | PASS |
| Stage/Commit/Push | **ไม่ได้ทำ** | PASS |

**Repository Safety: PASS**

---

## 2. Screenshot Observations

ตามที่ผู้ดูแลแจ้งจากหน้า DNS Management:

| Host | Type | Value | TTL |
|---|---|---:|---:|
| `uploading.com` | A | `122.154.34.166` | 3600 |
| `www` | A | `122.154.34.166` | 3600 |
| `ftp` | A | `122.154.34.166` | 3600 |
| `mail` | A | `122.154.34.166` | 3600 |
| `pop` | A | `122.154.34.166` | 3600 |
| `smtp` | A | `122.154.34.166` | 3600 |

และพบการเปลี่ยนแปลง:

| Record | Baseline เดิม | ค่าในภาพ |
|---|---|---|
| NS | `ns1/ns2.siteground.net` | `ns3/ns4.sogoodhost.com` |
| MX | `mx10/20/30.antispam.mailspamprotection.com` | `10 mail.uploading.com` |
| SPF | `v=spf1 +a +mx +ip4:34.174.30.12 include:uploading.com.spf.auto.dnssmarthost.net ~all` | `v=spf1 a mx ip4:122.154.34.166 ~all` |

**ข้อสังเกตสำคัญ:** ค่าใน Control Panel ไม่เท่ากับ public DNS ที่ตรวจจริงด้านล่าง

---

## 3. Public DNS Resolver Matrix

```text
PUBLIC_DNS_TIMESTAMP=2026-11-24T03:40:11.0570218+07:00
TIMEZONE=Asia/Bangkok (+07:00)
TARGET_IP=122.154.34.166
OLD_IP=34.174.96.8
```

| Provider | Resolver | Hostname | A Record | TTL | ผล |
|---|---|---|---:|---:|---|
| Google | `8.8.8.8` | `uploading.com` | `34.174.96.8` | 21600 | **FAIL** |
| Google | `8.8.8.8` | `www.uploading.com` | `34.174.96.8` | 21185 | **FAIL** |
| Cloudflare | `1.1.1.1` | `uploading.com` | `34.174.96.8` | 86400 | **FAIL** |
| Cloudflare | `1.1.1.1` | `www.uploading.com` | `34.174.96.8` | 86400 | **FAIL** |
| Quad9 | `9.9.9.9` | `uploading.com` | `34.174.96.8` | 9194 | **FAIL** |
| Quad9 | `9.9.9.9` | `www.uploading.com` | `34.174.96.8` | 11941 | **FAIL** |

**Public DNS Gate: 0/6 PASS** — ทุก resolver ยังตอบ IP เก่า

---

## 4. Authoritative Nameserver Results

```text
AUTH_TIMESTAMP=2026-11-24T03:40:13.6401098+07:00
```

### 4.1 Nameserver IP resolution

| Nameserver | A Record |
|---|---|
| `ns1.siteground.net` | `75.2.77.104` |
| `ns2.siteground.net` | `99.83.229.113` |
| `ns3.sogoodhost.com` | `122.154.34.166` |
| `ns4.sogoodhost.com` | `122.154.34.166` |

หมายเหตุ: `ns3/ns4.sogoodhost.com` ชี้ไปที่ IP เดียวกับ target web host

### 4.2 Direct authoritative query — NS ใหม่ (sogoodhost)

การ.query ตรงที่ `122.154.34.166`:

| Query | ค่าที่ตอบ |
|---|---|
| `uploading.com A` | `122.154.34.166` |
| `www.uploading.com A` | `122.154.34.166` |
| `uploading.com NS` | `ns3.sogoodhost.com`, `ns4.sogoodhost.com` |
| `uploading.com MX` | `10 mail.uploading.com` |
| `uploading.com TXT` | `v=spf1 a mx ip4:122.154.34.166 ~all` |

ผลจาก `ns3` และ `ns4` **สอดคล้องกัน**

### 4.3 Direct authoritative query — NS เก่า (SiteGround)

การ query ตรงที่ `75.2.77.104` และ `99.83.229.113`:

| Query | ค่าที่ตอบ |
|---|---|
| `uploading.com A` | `34.174.96.8` |
| `www.uploading.com A` | `34.174.96.8` |
| `uploading.com NS` | `ns1.siteground.net`, `ns2.siteground.net` |
| `uploading.com MX` | `mx10/20/30.antispam.mailspamprotection.com` |
| `uploading.com TXT` | SPF เดิม (`ip4:34.174.30.12` + include เดิม) |

ผลจาก `ns1` และ `ns2` **สอดคล้องกัน**

**สรุป Authoritative:** มีสอง zone ที่ขัดแย้งกัน:
- Zone ใหม่ (sogoodhost) ถูกต้องตามที่ตั้งใจ
- Zone เก่า (SiteGround) ยังตอบค่าเดิม
- Public resolver ยังใช้ zone เก่า

---

## 5. DNS Delegation Results

```text
PARENT_TIMESTAMP=2026-11-24T03:40:33.4629427+07:00
```

- พยายาม query Parent/`.com` zone โดยตรงผ่าน gTLD servers (`a–m.gtld-servers.net`) รวม 5+ IP
- ผลที่ได้จากเครื่องนี้: **SERVFAIL / DNS server failure** ทุกครั้ง
- `nslookup` ไปยัง gTLD ก็ให้ผลไม่ชัดเจน/ไม่มีคำตอบที่ใช้ยืนยันได้

**ผล: ไม่สามารถยืนยัน Parent delegation จากเครื่องนี้ได้ (INCONCLUSIVE)**

อย่างไรก็ตาม หลักฐานทางอ้อมชัดเจน:

- Public resolver ทั้ง 3 แห่งยังตอบ NS เก่า (`ns1/ns2.siteground.net`)
- Public A records ยังตอบ IP เก่า
- TTL ของ public ยังเป็นของ zone เก่า (21600/86400)

จึงยัง **ไม่สามารถกล่าวได้ว่า delegation เปลี่ยนไป sogoodhost แล้ว**

---

## 6. A / AAAA / CNAME Analysis

| รายการ | ผล |
|---|---|
| A `uploading.com` public | ยังเป็น `34.174.96.8` ทุก resolver → FAIL |
| A `www.uploading.com` public | ยังเป็น `34.174.96.8` ทุก resolver → FAIL |
| A ใน zone ใหม่ | `122.154.34.166` ถูกต้อง → PASS (authoritative-only) |
| AAAA | ไม่พบ ทั้งสอง hostname → ไม่มีเส้นทาง IPv6 แทน |
| CNAME | ไม่พบ ทั้งสอง hostname → ไม่มี alias ที่พาไป host อื่น |
| Conflicting A records | ไม่มี (แต่มี zone เก่า/ใหม่ขัดกันที่ระดับ delegation) |

**ผล A/AAAA/CNAME: FAIL สำหรับ public gate**

---

## 7. MX Comparison

```text
MAIL_TIMESTAMP=2026-11-24T03:40:15.9331869+07:00
```

| แหล่ง | MX ที่ตอบ |
|---|---|
| Google public | `mx10/20/30.antispam.mailspamprotection.com` (เก่า) |
| Cloudflare public | `mx10/20/30.antispam.mailspamprotection.com` (เก่า) |
| Quad9 public | `mx10/20/30.antispam.mailspamprotection.com` (เก่า) |
| Zone ใหม่ ns3/ns4 | `10 mail.uploading.com` (ใหม่) |
| Zone เก่า ns1/ns2 | `mx10/20/30.antispam.mailspamprotection.com` (เก่า) |

**MAIL-DNS-CHANGE: CONFIRMED INTENDED** — ผู้ดูแลยืนยันแล้ว
แต่ public ยังใช้ MX เก่าอยู่ → ยังไม่เกิดผลกับผู้ใช้จริง

---

## 8. SPF Comparison

| แหล่ง | SPF |
|---|---|
| Public + zone เก่า | `v=spf1 +a +mx +ip4:34.174.30.12 include:uploading.com.spf.auto.dnssmarthost.net ~all` |
| Zone ใหม่ | `v=spf1 a mx ip4:122.154.34.166 ~all` |

การประเมิน:

- ไวยากรณ์ SPF ใหม่ **ถูกต้อง**
- Mechanism: `a` (1 lookup), `mx` (≈1 lookup ตาม MX ปัจจุบัน), `ip4` (0), `~all` (0) → อยู่ภายใต้ขีดจำกัด 10 DNS lookups
- `mail.uploading.com A` ใน zone ใหม่ = `122.154.34.166` → สอดคล้องกับ `a mx ip4:122.154.34.166`
- **ความเสี่ยงที่ประเมินแล้ว:** `include:uploading.com.spf.auto.dnssmarthost.net` ถูกถอดออก หากยังมีผู้ส่งอีเมลภายนอกที่พึ่ง include เดิม อาจSPF fail หลัง delegation เปลี่ยน

**ผล SPF: SYNTAX PASS, ความเสี่ยงถูกระบุแล้ว**

---

## 9. DKIM / DMARC Status

### 9.1 Public / zone เก่า

| Record | ผล |
|---|---|
| `_dmarc.uploading.com` | พบ `v=DMARC1; p=none; aspf=r; adkim=r;` |
| `default._domainkey.uploading.com` | พบ (redacted) |
| selector อื่น (`google`, `k1`, `s1`, `selector1`, `selector2`) | ไม่พบ |

### 9.2 Zone ใหม่ (ns3/ns4.sogoodhost.com)

| Record | ผล |
|---|---|
| `_dmarc.uploading.com` | **ไม่พบ / ERROR** |
| `default._domainkey.uploading.com` | **ไม่พบ / ERROR** |
| selector อื่น | **ไม่พบ** |

**ผล: DKIM/DMARC หายไปใน zone ใหม่**
นี่คือความเสี่ยงด้านการรับรองอีเมลหลัง delegation เปลี่ยน

---

## 10. Mail-related CNAME & SMTP Connectivity

| Hostname | ผล |
|---|---|
| `mail.uploading.com` | ไม่มี CNAME (มี A ตรงใน zone ใหม่) |
| `autodiscover.uploading.com` | ไม่มี CNAME / ERROR |
| `ftp.uploading.com` | ไม่มี CNAME |
| `webmail`, `pop`, `imap`, `smtp` | ไม่พบ CNAME |

SMTP TCP connectivity ทดสอบแบบไม่ส่งอีเมลจริง:

| Port | ผลที่ `122.154.34.166` |
|---:|---|
| 25 | OPEN |
| 465 | OPEN |
| 587 | OPEN |

`NO_EMAIL_SENT=true`

**ผล: SMTP ports เปิด แต่ยังไม่ได้ตรวจตัวตน/การรับส่งจริง**

---

## 11. Mail DNS Risk Assessment

| ประเด็น | ระดับ | รายละเอียด |
|---|---|---|
| การย้าย NS/MX/SPF | ข้อมูล | ผู้ดูแลยืนยันว่าตั้งใจ |
| DKIM หายใน zone ใหม่ | **สูง** | หลัง delegation เปลี่ยน จะไม่มี DKIM signing record |
| DMARC หายใน zone ใหม่ | **สูง** | หลัง delegation เปลี่ยน จะไม่มี DMARC policy |
| SPF include เดิมถูกถอด | ปานกลาง | ต้องแน่ใจว่าไม่มี sender ภายนอกที่ยังใช้บริการเดิม |
| mail A ใหม่ | ต่ำ | `122.154.34.166` สอดคล้องกับ SPF/MX |
| SMTP ports | ต่ำ | เปิดครบ แต่ยังไม่ทดสอบส่งจริง |
| Public ยังเป็น zone เก่า | สูง | ยังไม่ cutover จริง |

**สรุป Mail DNS Safety: BLOCKED**
แม้เจตนาการย้ายจะได้รับการยืนยันแล้ว แต่ยังมีความเสี่ยงที่ยังไม่ได้จัดการ: **DKIM/DMARC ไม่มีใน zone ใหม่**

---

## 12. DNS Propagation Status

| รายการ | ผล |
|---|---|
| Control panel ตั้งค่าแล้ว | ใช่ ตามภาพที่ผู้ดูแลแจ้ง |
| Authoritative zone ใหม่ถูกต้อง | ใช่ (ns3/ns4) |
| Public resolver ใช้ zone ใหม่ | **ไม่ใช่** |
| Propagation ครบ 6/6 | **ไม่** |
| สถานะ | **BLOCKED** |

การที่ public resolver ยังตอบ NS/MX/A เก่าแสดงว่า **Parent delegation ยังไม่เปลี่ยนหรือยังไม่มีผล** แม้จะ query parent โดยตรงไม่สำเร็จจากเครื่องนี้ก็ตาม

---

## 13. Blockers

| # | Blocker | รายละเอียด |
|---:|---|---|
| 1 | Public A ยังเป็น IP เก่า | 0/6 จาก Google/Cloudflare/Quad9 |
| 2 | Public ยังใช้ NS เก่า | `ns1/ns2.siteground.net` |
| 3 | Parent delegation ไม่สามารถยืนยันได้ | gTLD query SERVFAIL จากเครื่องนี้ |
| 4 | Mail DNS เปลี่ยนแต่ DKIM/DMARC หายใน zone ใหม่ | เสี่ยงต่อ SPF/DKIM/DMARC alignment |
| 5 | SPF include เดิมถูกถอด | ต้องยืนยันว่าไม่มี sender ภายนอกค้าง |
| 6 | Phase 15.16 ยังไม่ได้รับอนุญาต | ต้องผ่าน DNS Gate + Mail Safety ก่อน |

---

## 14. Evidence Commands

```powershell
Resolve-DnsName uploading.com -Type A -Server 8.8.8.8 -DnsOnly
Resolve-DnsName www.uploading.com -Type A -Server 1.1.1.1 -DnsOnly
Resolve-DnsName uploading.com -Type A -Server 9.9.9.9 -DnsOnly
Resolve-DnsName uploading.com -Type NS -Server 8.8.8.8 -DnsOnly
Resolve-DnsName uploading.com -Type MX -Server 8.8.8.8 -DnsOnly
Resolve-DnsName uploading.com -Type TXT -Server 8.8.8.8 -DnsOnly
Resolve-DnsName uploading.com -Type NS -Server 192.5.6.30 -DnsOnly -QuickTimeout
Resolve-DnsName uploading.com -Type A -Server 122.154.34.166 -DnsOnly
Resolve-DnsName uploading.com -Type MX -Server 122.154.34.166 -DnsOnly
Resolve-DnsName _dmarc.uploading.com -Type TXT -Server 122.154.34.166 -DnsOnly
Resolve-DnsName default._domainkey.uploading.com -Type TXT -Server 122.154.34.166 -DnsOnly
```

หมายเหตุ:

- ใช้ query จริง ไม่ใช้ค่าจากภาพเพียงอย่างเดียว
- ไม่ได้ส่งอีเมลจริง
- ไม่ได้แก้ DNS, MX, SPF, DKIM, DMARC, NS หรือ DNS Zone
- ไม่ได้ Stage/Commit/Push

---

## 15. Final Decision

### DNS Gate

- `uploading.com`: **0/3 FAIL**
- `www.uploading.com`: **0/3 FAIL**
- Total: **0/6 FAIL**
- Public ยังตอบ Old IP `34.174.96.8`
- Authoritative ใหม่ถูกต้องแต่ยังไม่ đượcใช้สาธารณะ

**DNS Gate: BLOCKED**

### Mail DNS Safety

- MAIL-DNS-CHANGE: CONFIRMED INTENDED
- MX/SPF ใหม่สอดคล้องกันใน zone ใหม่
- DKIM/DMARC หายใน zone ใหม่ → ความเสี่ยงสูงยังไม่ได้จัดการ

**Mail DNS Safety: BLOCKED**

### Repository

- HEAD: `4270aa70b5e03d00a6b254c3318a303742008964`
- Branch: `main`
- HEAD = origin/main
- ไม่มี source change, ไม่มี staged file

### Report

- `docs/NOVASMM_PHASE15_15_2_DNS_MAIL_SAFETY_AUDIT.md`
- อยู่ใน working tree เท่านั้น

### Final Authorization

**BLOCKED — ห้ามเริ่ม Phase 15.16**

ห้ามติดตั้ง SSL, ห้ามแก้ HTTPS Redirect, ห้ามแก้ `.htaccess`, ห้ามเปลี่ยน Virtual Host, ห้าม Deploy Production

**สถานะสุดท้าย: NO-GO** จนกว่า Phase 15.19 จะผ่านทุก Acceptance Criteria ด้วยหลักฐานจริง
