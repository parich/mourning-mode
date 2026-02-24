# วิธีทดสอบและใช้งานระบบ Auto-Update

## ภาพรวมการทำงาน

Plugin จะเช็ค GitHub Releases ทุก 6 ชั่วโมง ถ้ามีเวอร์ชันใหม่กว่าที่ติดตั้งอยู่ WordPress จะแจ้งเตือนในหน้า Plugins และ Dashboard > Updates

```
WordPress cron (ทุก 6 ชม.)
  → เรียก GitHub API /releases/latest
  → เปรียบเทียบ tag_name กับ Version ใน plugin header
  → ถ้าใหม่กว่า → แสดงแจ้งเตือนอัปเดต
```

---

## วิธีปล่อยเวอร์ชันใหม่ (สำหรับนักพัฒนา)

### 1. แก้โค้ดและเปลี่ยนเวอร์ชัน

แก้ `Version` ใน plugin header ของ `mourning-mode.php`:
```php
 * Version:           1.4.0
```

### 2. Commit & Push

```bash
cd wp-content/plugins/mourning-mode
git add mourning-mode.php
git commit -m "v1.4.0: รายละเอียดการเปลี่ยนแปลง"
git push origin master
```

### 3. สร้าง ZIP

```bash
bash build-zip.sh
```

ไฟล์ `mourning-mode.zip` จะถูกสร้างในโฟลเดอร์ plugin โดยโฟลเดอร์ข้างในชื่อ `mourning-mode` ถูกต้อง

### 4. สร้าง GitHub Release

```bash
gh release create v1.4.0 mourning-mode.zip --title "v1.4.0" --notes "รายละเอียดการเปลี่ยนแปลง"
```

> **สำคัญ:** tag name ต้องขึ้นต้นด้วย `v` เช่น `v1.4.0` เพื่อให้ตรงกับ `ltrim($tag, 'v')` ในโค้ด

---

## วิธีทดสอบ Auto-Update (ฝั่งเว็บปลายทาง)

### ขั้นตอนที่ 1: ตรวจสอบว่า GitHub API คืนค่าถูกต้อง

เปิด URL นี้ในเบราว์เซอร์:
```
https://api.github.com/repos/parich/mourning-mode/releases/latest
```

ตรวจสอบว่า:
- `tag_name` ตรงกับเวอร์ชันที่ต้องการ (เช่น `v1.4.0`)
- `assets` มีไฟล์ `mourning-mode.zip`

### ขั้นตอนที่ 2: ลบ cache ที่เว็บปลายทาง

Plugin cache ข้อมูล release ไว้ 6 ชั่วโมง ต้องลบ cache เพื่อบังคับให้เช็คใหม่

**วิธี A: ใช้ SQL (phpMyAdmin)**
```sql
DELETE FROM wp_options WHERE option_name = '_transient_mourning_mode_github_update';
DELETE FROM wp_options WHERE option_name = '_transient_timeout_mourning_mode_github_update';
DELETE FROM wp_options WHERE option_name = '_site_transient_update_plugins';
DELETE FROM wp_options WHERE option_name = '_site_transient_timeout_update_plugins';
```

> หมายเหตุ: ถ้า table prefix ไม่ใช่ `wp_` ให้เปลี่ยนเป็น prefix ที่ใช้จริง

**วิธี B: ใช้ WP-CLI**
```bash
wp transient delete mourning_mode_github_update
wp transient delete update_plugins --network
```

**วิธี C: ใส่โค้ดชั่วคราวใน functions.php**

เพิ่มที่ด้านบนสุดของ `functions.php` ของ theme:
```php
delete_transient('mourning_mode_github_update');
delete_site_transient('update_plugins');
```

**โหลดหน้าเว็บ 1 ครั้ง แล้วลบโค้ดออกทันที**

### ขั้นตอนที่ 3: บังคับ WordPress เช็คอัปเดต

1. ไปที่ **Dashboard > Updates**
2. กดปุ่ม **"Check Again"**

### ขั้นตอนที่ 4: ตรวจสอบผลลัพธ์

ถ้าสำเร็จ:
- หน้า **Plugins** จะแสดง "There is a new version of โหมดไว้อาลัย available"
- หน้า **Dashboard > Updates** จะแสดง plugin ในรายการอัปเดต
- กดปุ่ม **"Update Now"** หรือ **"Update"** เพื่ออัปเดต
- หลังอัปเดต เวอร์ชันจะเปลี่ยนเป็นเวอร์ชันใหม่
- Plugin จะถูก activate อัตโนมัติ

---

## Troubleshooting

### ไม่เห็นแจ้งเตือนอัปเดต

1. **ลบ cache แล้วหรือยัง?** — ต้องลบ transient ทั้ง `mourning_mode_github_update` และ `update_plugins`
2. **เวอร์ชันถูกต้องหรือไม่?** — เวอร์ชันใน GitHub Release (`tag_name`) ต้อง**สูงกว่า** `Version` ใน plugin header ที่ติดตั้งอยู่
3. **GitHub API ตอบถูกต้องหรือไม่?** — เปิด `https://api.github.com/repos/parich/mourning-mode/releases/latest` ตรวจสอบ `tag_name`
4. **โฟลเดอร์ plugin ชื่ออะไร?** — `plugin_basename` ต้องตรงกัน ถ้าโฟลเดอร์ชื่อ `mourning-mode-master` แทน `mourning-mode` อาจมีปัญหา

### อัปเดตแล้วแต่ plugin หาย

- ตรวจสอบว่า ZIP ที่แนบใน Release สร้างจาก `build-zip.sh` ซึ่งโฟลเดอร์ข้างในชื่อ `mourning-mode`
- `after_install` จะ rename โฟลเดอร์ให้ถูกต้อง แต่ถ้า ZIP สร้างเองโดยโฟลเดอร์ชื่อผิด อาจมีปัญหา

### GitHub API rate limit

- ไม่ใส่ token จะถูกจำกัด 60 requests/ชั่วโมง/IP
- Plugin cache ไว้ 6 ชั่วโมง ปกติไม่มีปัญหา
- ถ้าโดน rate limit จะเห็น HTTP 403 — รอสักครู่แล้วลองใหม่

---

## โครงสร้างไฟล์

```
mourning-mode/
├── mourning-mode.php          # ไฟล์ plugin หลัก (มี GitHub Updater อยู่ด้านล่าง)
├── black_ribbon_top_right.png # รูปโบว์ดำ
├── README.md                  # คู่มือ plugin
├── AUTO-UPDATE-GUIDE.md       # คู่มือนี้
├── build-zip.sh               # script สร้าง ZIP (ไม่เข้า git)
└── .gitignore                 # ignore *.zip และ build-zip.sh
```
