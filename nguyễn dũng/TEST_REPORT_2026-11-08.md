# BÁO CÁO KIỂM THỬ — NEWS HUB DASHBOARD (MÔ-ĐUN ĐỘC LẬP)

**Ngày:** 08/11/2026 — **PHP CLI kiểm thử:** 8.4.24 (Linux).  
**Phạm vi thực sự có mã để test:** thư mục `NEWS_HUB_Dashboard_KiemThu_DocLap`.  
**Phạm vi chưa nhận source:** ứng dụng NEWS HUB nhóm, `database/news_hub.sql`, `config/database.php`, Router, Auth, Article/Category/Comment Controllers, admin layout.

## A. Các phép thử đã chạy

| Nhóm kiểm thử | Kết quả | Ý nghĩa |
|---|---:|---|
| Kiểm tra cú pháp PHP (`php -l`) | **16 PASS / 0 FAIL** | 16 file PHP cú pháp hợp lệ trong PHP 8.4.24 |
| Unit PHP (`php tests/dashboard_unit_test.php`) | **36 PASS / 0 FAIL** | Logic thống kê, sort 5 bài, bảo vệ quyền theo callback, escape HTML, lỗi silent-mode PDO qua stub; **không chạy SQL MySQL thật** |
| View PHP (`php tests/view_test.php`) | **5 PASS / 0 FAIL** | Giao diện render và escape dữ liệu thử nghiệm |
| HTTP (`bash tests/http_smoke_test.sh`) | **7 PASS / 0 FAIL** | Thiếu/sai Basic Auth = 401; challenge header; CSS/JS = 200; khi chưa có MySQL driver, request có quyền = 503 |
| Demo flag disable | **1 PASS** | `DASHBOARD_STANDALONE_DEMO=false` ⇒ /dashboard.php trả 404 |
| Cấu hình Docker và SQL mẫu (kiểm tra tĩnh bằng Python) | **30 PASS / 0 FAIL** | YAML hợp lệ, tên bảng và cột demo đồng bộ, giới hạn expose port, breakpoint CSS; **chưa chạy Docker/MySQL** |
| JS (`node --check`) | **1 PASS** | `admin.js` đúng cú pháp JS |
| ZIP (test giải nén) | **PASS** | Archive gói trước hợp lệ; gói mới kiểm lại sau đóng |

## B. Kiểm thử BLOCKED — không được đổi thành PASS

| Thử nghiệm | Trạng thái | Nguyên nhân |
|---|---|---|
| Kết nối và truy vấn MySQL 8.x thật | BLOCKED | Không có MySQL server / Docker / PHP PDO MySQL trong container; kho apt không truy cập được |
| `php tests/demo_database_test.php` | BLOCKED | `[BLOCKED] MySQL demo: Chưa có pdo_mysql`; exit 2 |
| `php tests/run.php` | BLOCKED | Thiếu DSN; exit 2. Nếu đặt DSN cũng chưa có driver PDO MySQL |
| Dashboard tích hợp Router/Auth/Admin Layout nhóm | BLOCKED | Chưa có source và commit của nhóm |
| CRUD thực tế, upload nhiều ảnh, editor, phân trang | BLOCKED | Chưa có source module Article để thao tác |
| Auth đăng nhập / đăng xuất / phân quyền User–Admin của nhóm | BLOCKED | Chưa có source Auth và tài khoản thử nghiệm |
| CRUD danh mục, khóa/mở tài khoản, bình luận duyệt/ẩn/xóa | BLOCKED | Chưa có source các module tương ứng |
| Kiểm tra responsive bằng trình duyệt desktop/mobile | CHƯA XÁC MINH | Breakpoint đã kiểm tra tĩnh; chưa có bằng chứng render hình ảnh đáng tin cậy |
| Commit/merge vào GitHub nhóm | CHƯA LÀM | Chưa có repo hoặc quyền truy cập |

## C. Lỗi mã đã phát hiện và khắc phục

1. **FIX-LOCAL-01:** `fetchColumn() === false` trước đây bị ép thành `0`. Đã sửa để ném lỗi và Dashboard hiển thị **Chưa xác minh**. M21–M22 PASS.
2. **FIX-LOCAL-02:** Khi `PDOStatement::execute()` trả `false`, cần đánh dấu Published/latest là không đọc được, không phát số liệu sai. M23–M24 PASS.

Không ghi một lỗi nào ở ứng dụng nhóm khi chưa có bằng chứng trên mã thực tế. Có báo cáo chi tiết trong `tests/BUG_LIST.md`.

## D. Lệnh tự chạy lại

```bash
find . -type f -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/dashboard_unit_test.php
php tests/view_test.php
bash tests/http_smoke_test.sh
php tests/demo_database_test.php   # chỉ khi MySQL/driver và DSN đã được cấp
php tests/run.php                  # chỉ khi có schema và DSN hợp lệ
```

Các bước chạy trên MySQL demo bằng Docker Desktop và gắn project nhóm được mô tả trong `RUN_LOCAL_DEMO.md` và `README.md`. **Để nghiệm thu thật theo bảng phân công, cần source ZIP hoặc URL GitHub repo/commit của NEWS HUB cùng file SQL, rồi thực hiện kiểm thử tích hợp riêng.**
