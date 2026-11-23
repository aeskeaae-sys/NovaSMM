# NovaSMM Phase 15.15.3 — Parent Nameserver Delegation & Mail Authentication Recovery

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
| Reset / Force push | ไม่ได้ทำ | PASS |
| ZIP / Repository ใหม่ | ไม่ได้สร้าง | PASS |
| Commit / Push | **ไม่ได้ทำ** | PASS |
| รายงานก่อนหน้า | `docs/NOVASMM_PHASE15_15_2_DNS_MAIL_SAFETY_AUDIT.md` | อ่านแล้ว PASS |

**Repository Safety: PASS**
รายงานฉบับนี้เก็บใน Working Tree เท่านั้น — ไม่ stage/commit/push

---

## 2. Registrar Identification

ตรวจสอบผ่าน Verisign RDAP สำหรับ `.com` เมื่อ `2026-11-24T03:43+07:00`

| รายการ | ค่า |
|---|---|
| Domain | `UPLOADING.COM` |
| Registrar | **Spaceship, Inc.** |
| IANA Registrar ID | `3862` |
| Registration | 2003-03-19 |
| Expiration | 2027-09-15 |
| Last changed | 2026-02-11 |
| RDAP database updated | 2026-11-23T20:43:30Z |
| Status | `client transfer prohibited` |

**สรุป:** ผู้ดูแล Parent Delegation คือ **Registrar: Spaceship, Inc.** ไม่ใช่เฉพาะ SiteGround control panel
การเปลี่ยน Nameserver ต้องทำที่ **Registrar / Domain Owner account ของ Spaceship** หรือผ่านผู้ดูแลที่ได้รับสิทธิ์เท่านั้น

---

## 3. Parent NS Delegation

### 3.1 ค่าจาก Registry/RDAP (แหล่งข้อมูล Parent ที่เชื่อถือได้)

```text
NS1.SITEGROUND.NET
NS2.SITEGROUND.NET
```

### 3.2 ค่าที่ต้องการ

```text
ns3.sogoodhost.com
ns4.sogoodhost.com
```

### 3.3 ผลเปรียบเทียบ

| รายการ | ผล |
|---|---|
| Parent delegation เปลี่ยนแล้วหรือยัง | **ยังไม่เปลี่ยน** |
| Public resolver เห็น NS เก่าหรือใหม่ | เก่า (`ns1/ns2.siteground.net`) |
| ต้องดำเนินการที่ | Spaceship Registrar / Domain Owner |
| สิทธิ์จากระบบนี้ | **ไม่มี** — ไม่ได้เปลี่ยนอัตโนมัติ |

**ผล Parent Delegation: FAIL / BLOCKED**

หมายเหตุ: การ query gTLD server โดยตรงจากเครื่องนี้เคยได้ SERVFAIL ใน Phase 15.15.2 แต่ RDAP ของ Verisign ให้ข้อมูล Parent delegation ชัดเจนกว่าและยังเป็น NS เก่า

---

## 4. DNSSEC / DS Analysis

```text
DNSSEC_TIMESTAMP=2026-11-24T03:43:41.7398939+07:00
```

| แหล่ง | DS | DNSKEY |
|---|---|---|
| Google `8.8.8.8` | NONE | NONE |
| Cloudflare `1.1.1.1` | NONE | NONE |
| Quad9 `9.9.9.9` | NONE | NONE |
| ใหม่ `122.154.34.166` | NONE | NONE |
| เก่า `75.2.77.104` | NONE | NONE |
| เก่า `99.83.229.113` | NONE | NONE |

RDAP ระบุ:

```text
secureDNS.delegationSigned = false
```

**ผล DNSSEC/DS: PASS (ไม่มีปัญหา DS ขัดแย้ง)**
ไม่มีการเซ็น DNSSEC และไม่มี DS record ที่ต้องอัปเดตก่อนเปลี่ยน delegation
แต่หมายความว่า domain นี้ก็ยังไม่ได้รับการป้องกันด้วย DNSSEC ด้วยเช่นกัน

### Glue records

- NS ใหม่คือ `ns3.sogoodhost.com` และ `ns4.sogoodhost.com`
- อยู่นอก zone `uploading.com` → **ไม่จำเป็นต้องมี glue record ใน uploading.com**
- RDAP ไม่แสดง glue IP สำหรับ delegation ปัจจุบัน

---

## 5. Authoritative DNS Status

```text
SOA_TIMESTAMP=2026-11-24T03:43:56.0533733+07:00
```

| แหล่ง | Serial | การตีความ |
|---|---:|---|
| Public (Google/Cloudflare/Quad9) | 118 | ยังเป็น zone เก่า |
| NS เก่า `ns1/ns2.siteground.net` | 118 | zone เก่า |
| NS ใหม่ `ns3/ns4.sogoodhost.com` | `2026112300` | zone ใหม่ |

### ความสอดคล้องภายในแต่ละ zone

| รายการ | Zone ใหม่ (sogoodhost) | Zone เก่า (SiteGround) |
|---|---|---|
| `uploading.com A` | `122.154.34.166` | `34.174.96.8` |
| `www A` | `122.154.34.166` | `34.174.96.8` |
| NS | `ns3/ns4.sogoodhost.com` | `ns1/ns2.siteground.net` |
| MX | `10 mail.uploading.com` | `mx10/20/30.antispam...` |
| SPF | `v=spf1 a mx ip4:122.154.34.166 ~all` | SPF เดิม |
| DMARC | **ไม่มี** | มี |
| DKIM `default` | **ไม่มี** | มี (redacted) |

**ผล Authoritative: zone ใหม่สอดคล้องภายในตัวเอง แต่ยังไม่ถูกใช้สาธารณะ; zone เก่ายังตอบอยู่จริง**

---

## 6. DKIM Recovery Status

```text
MAIL_AUTH_TIMESTAMP=2026-11-24T03:43:58.4434567+07:00
```

| แหล่ง | `default._domainkey` |
|---|---|
| Public | พบ (redacted) |
| Zone เก่า | พบ (redacted) |
| Zone ใหม่ | **ERROR / ไม่พบ** |

ข้อกำหนดห้าม:

- ห้ามสร้าง DKIM public key เอง
- ห้ามเดา selector
- ห้ามนำ private key ไปเผยแพร่
- ห้ามก็อปปี้ DKIM จากระบบเก่าโดยไม่ยืนยันว่าผู้ให้บริการใหม่รองรับ

**ผล DKIM Recovery: BLOCKED**

ข้อมูลที่ต้องขอจาก Mail Provider ใหม่:

1. Selector ที่กำหนดจริง (เช่น `default`, หรือ selector อื่นที่ provider ระบุ)
2. Host/Name ที่ต้องใช้ เช่น `<selector>._domainkey.uploading.com`
3. Record Type (TXT หรือ CNAME)
4. Value/Public Key ที่ provider ออกให้
5. เอกสารยืนยันว่ารองรับการส่งจาก `mail.uploading.com`

จนกว่าจะได้ข้อมูลข้างต้น **ห้ามสรุปว่า DKIM PASS**

---

## 7. DMARC Recovery Status

Zone ใหม่ไม่มี `_dmarc.uploading.com`

### Draft record ที่เสนอ (ยังไม่สร้าง)

```text
Host: _dmarc.uploading.com
Type: TXT
Value: v=DMARC1; p=none; aspf=r; adkim=r;
```

เหตุผล: ใช้นโยบาย `p=none` แบบเดิมจาก zone เก่า เพื่อไม่กระทบการส่งเมลก่อนมีข้อมูล report จริง

ยังไม่ใส่ `rua=`/`ruf=` เพราะ:

- ยังไม่มี reporting mailbox ที่ Domain Owner อนุมัติ
- ห้ามเดา mailbox
- ห้ามบังคับ `quarantine` หรือ `reject` โดยไม่ได้รับอนุมัติ

**ผล DMARC Recovery: BLOCKED — รออนุมัติค่าและ mailbox**

---

## 8. MX / SPF Validation

### 8.1 MX

| แหล่ง | MX |
|---|---|
| Public | `mx10/20/30.antispam.mailspamprotection.com` (เก่า) |
| Zone เก่า | เดิม |
| Zone ใหม่ | `10 mail.uploading.com` |

### 8.2 mail A

| แหล่ง | `mail.uploading.com A` |
|---|---|
| Zone ใหม่ | `122.154.34.166` |
| Public | `34.174.96.8` (ยังเป็น zone เก่า) |

### 8.3 SPF

| แหล่ง | SPF |
|---|---|
| Zone ใหม่ | `v=spf1 a mx ip4:122.154.34.166 ~all` |
| Zone เก่า/Public | `v=spf1 +a +mx +ip4:34.174.30.12 include:uploading.com.spf.auto.dnssmarthost.net ~all` |

การประเมิน:

- SPF ใหม่ถูกต้องตามไวยากรณ์
- `a mx ip4:122.154.34.166` สอดคล้องกับ MX ใหม่
- จำนวน DNS lookup อยู่ในขีดจำกัด
- ความเสี่ยงที่ประเมินแล้ว: `include` เดิมถูกถอดออก ต้องยืนยันว่าไม่มี sender ภายนอกที่ยังใช้บริการเดิม

**ผล MX/SPF ใน zone ใหม่: PASS (รอ public ตาม delegation)**

---

## 9. SMTP Connectivity, PTR และ Mail TLS

```text
SMTP_TIMESTAMP=2026-11-24T03:44:34.1956569+07:00
MAIL_TLS_TIMESTAMP=2026-11-24T03:43:59.9275028+07:00
MAIL_TLS_INSPECT_ONLY_TIMESTAMP=2026-11-24T03:44:08.8358041+07:00
```

### 9.1 SMTP ports (ไม่ได้ส่งอีเมลจริง)

| Port | ผล |
|---:|---|
| 25 | OPEN |
| 465 | OPEN |
| 587 | OPEN |

`NO_EMAIL_SENT=true`

### 9.2 Reverse DNS / PTR

| IP | PTR |
|---|---|
| `34.174.96.8` (เก่า) | `8.96.174.34.bc.googleusercontent.com` |
| `122.154.34.166` (ใหม่) | `mdc-122-154-34-166.sticbox.com` |

ความเสี่ยง: PTR ใหม่ยังเป็นชื่อ `sticbox.com` ไม่ใช่ `mail.uploading.com` → เสี่ยงต่อการถูก receiver บางเจ้าปฏิเสธ

### 9.3 Mail TLS certificate

การverify ปกติกับ SNI `mail.uploading.com` ที่ port 465 **ล้มเหลว**:

```text
The remote certificate is invalid according to the validation procedure.
```

การตรวจ certificate แบบข้าม validation **เพื่อดูข้อมูลเท่านั้น ไม่ใช้เป็นหลักฐานว่า SSL ผ่าน**:

```text
Subject=CN=mdc-122-154-34-166.sticbox.com
Issuer=CN=YE2, O=Let's Encrypt, C=US
NotAfter=2027-01-18T10:53:35+07:00
```

Port 587 ใช้ STARTTLS ไม่ใช่ implicit TLS → handshake แบบ implicit ล้มเหลวตามคาด

**ผล Mail TLS: FAIL — certificate ไม่ครอบคลุม `mail.uploading.com`**

---

## 10. Public DNS Matrix

```text
PUBLIC_DNS_TIMESTAMP=2026-11-24T03:43:57.1964142+07:00
TARGET_IP=122.154.34.166
OLD_IP=34.174.96.8
```

| Provider | Resolver | Hostname | A | TTL | ผล |
|---|---|---|---:|---:|---|
| Google | `8.8.8.8` | `uploading.com` | `34.174.96.8` | 21600 | FAIL |
| Google | `8.8.8.8` | `www.uploading.com` | `34.174.96.8` | 21116 | FAIL |
| Cloudflare | `1.1.1.1` | `uploading.com` | `34.174.96.8` | 86175 | FAIL |
| Cloudflare | `1.1.1.1` | `www.uploading.com` | `34.174.96.8` | 86175 | FAIL |
| Quad9 | `9.9.9.9` | `uploading.com` | `34.174.96.8` | 8968 | FAIL |
| Quad9 | `9.9.9.9` | `www.uploading.com` | `34.174.96.8` | 11715 | FAIL |

- `AAAA`: ไม่มี
- `CNAME`: ไม่มี
- Conflicting A records: ไม่มีในระดับ resolver แต่มี zone เก่า/ใหม่ขัดกันที่ระดับ delegation

**Public DNS Gate: 0/6 FAIL**

---

## 11. Blockers

| # | Blocker | รายละเอียด |
|---:|---|---|
| 1 | Parent delegation ยังเป็น NS เก่า | RDAP ระบุ `NS1/NS2.SITEGROUND.NET` |
| 2 | Registrar ต้องเป็นคนเปลี่ยน | Spaceship, Inc. — ไม่มีสิทธิ์จากระบบนี้ |
| 3 | Public DNS ยัง 0/6 | ทุก resolver ตอบ `34.174.96.8` |
| 4 | DKIM หายใน zone ใหม่ | ต้องได้ค่าจาก mail provider |
| 5 | DMARC หายใน zone ใหม่ | ต้องอนุมัติ policy และ mailbox |
| 6 | PTR ไม่ตรง | `sticbox.com` แทน `mail.uploading.com` |
| 7 | Mail TLS ไม่ตรง domain | cert เป็น `sticbox.com` |
| 8 | Phase 15.16 ยังไม่ได้รับอนุญาต | ต้องผ่านทุกเกณฑ์ก่อน |

---

## 12. Evidence

คำสั่ง/แหล่งข้อมูลที่ใช้:

```powershell
Resolve-DnsName uploading.com -Type A -Server 8.8.8.8 -DnsOnly
Resolve-DnsName www.uploading.com -Type A -Server 1.1.1.1 -DnsOnly
Resolve-DnsName uploading.com -Type A -Server 9.9.9.9 -DnsOnly
Resolve-DnsName uploading.com -Type NS -Server 8.8.8.8 -DnsOnly
Resolve-DnsName uploading.com -Type DS -Server 8.8.8.8 -DnsOnly
Resolve-DnsName uploading.com -Type DNSKEY -Server 122.154.34.166 -DnsOnly
Resolve-DnsName uploading.com -Type SOA -Server 122.154.34.166 -DnsOnly
Resolve-DnsName uploading.com -Type SOA -Server 75.2.77.104 -DnsOnly
Resolve-DnsName uploading.com -Type MX -Server 122.154.34.166 -DnsOnly
Resolve-DnsName uploading.com -Type TXT -Server 122.154.34.166 -DnsOnly
Resolve-DnsName _dmarc.uploading.com -Type TXT -Server 122.154.34.166 -DnsOnly
Resolve-DnsName default._domainkey.uploading.com -Type TXT -Server 122.154.34.166 -DnsOnly
Resolve-DnsName 166.34.154.122.in-addr.arpa -Type PTR -Server 8.8.8.8 -DnsOnly
```

แหล่งข้อมูลเพิ่มเติม:

- Verisign RDAP สำหรับ `.com` — ใช้ตรวจ registrar, parent NS, secureDNS
- ไม่ได้ส่งอีเมลจริง
- ไม่ได้ใช้ SSL verification bypass เป็นหลักฐานความถูกต้องของ certificate
- ไม่ได้แก้ DNS Zone, Registrar, MX, SPF, DKIM, DMARC, NS
- ไม่ได้ stage/commit/push

---

## 13. Final Authorization

### ผลการประเมินเกณฑ์

| เกณฑ์ | ผล |
|---|---|
| Parent Delegation ชี้ NS ใหม่ครบ | **FAIL** |
| DNSSEC/DS ไม่มีปัญหา | PASS |
| Authoritative DNS สอดคล้องกัน | PASS เฉพาะในแต่ละ zone |
| A Record ผ่าน 6/6 | **FAIL** |
| MX ถูกต้อง | PASS เฉพาะ zone ใหม่ |
| SPF ถูกต้อง | PASS เฉพาะ zone ใหม่ |
| DKIM Record มีจริง | **FAIL** |
| DMARC Record มีจริง | **FAIL** |
| ไม่มี Mail DNS Blocker ที่ยังไม่ประเมิน | **FAIL** |

### **Phase 15.15.3 Result: BLOCKED**

### สิ่งที่ต้องดำเนินการโดยผู้ดูแลที่มีสิทธิ์

1. ที่ **Spaceship Registrar** เปลี่ยน Nameserver เป็น:
   - `ns3.sogoodhost.com`
   - `ns4.sogoodhost.com`
2. ใน Authoritative Zone ใหม่ เพิ่ม:
   - DKIM record ตามค่าที่ mail provider ออกให้
   - DMARC record ตามที่ Domain Owner อนุมัติ
3. ปรับ PTR ของ `122.154.34.166` เป็น `mail.uploading.com`
4. ติดตั้ง mail certificate ที่ครอบคลุม `mail.uploading.com`
5. แจ้งเพื่อ re-verify ครบ 6/6 และ Mail DNS ทุกเกณฑ์

### **Next Phase Authorization: BLOCKED — ห้ามเริ่ม Phase 15.16**

ห้ามติดตั้ง SSL, ห้ามตั้ง HTTPS Redirect, ห้ามแก้ `.htaccess`, ห้ามเปลี่ยน Virtual Host, ห้าม Deploy Production

**สถานะสุดท้าย: NO-GO** จนกว่า Phase 15.19 จะผ่านทุก Acceptance Criteria ด้วยหลักฐานจริง
