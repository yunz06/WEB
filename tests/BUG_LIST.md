# BUG LIST — BẢNG THEO DÕI LỖI

**Chưa có lỗi chức năng của hệ thống nhóm nào được xác nhận**, vì chưa nhận được source code tích hợp, database và đường dẫn chạy của nhóm. Những thứ chưa kiểm chứng được được phân loại `BLOCKED`, **không** tự ghi thành BUG.

| ID | Module | Mức độ | Các bước tái hiện | Kết quả mong đợi | Kết quả thực tế | Trạng thái | Người phụ trách |
|---|---|---|---|---|---|---|---|
| Chưa có | — | — | — | — | — | CHƯA KIỂM THỬ TÍCH HỢP | — |

## Điểm chặn cần xử lý trước khi kiểm thử tích hợp

- **BLOCKED-01**: Thiếu `database/news_hub.sql` và `database/seed.sql`; chưa xác định được tên cột và trạng thái Published thực tế.
- **BLOCKED-02**: Chưa nhận `app/Core/Database.php`, `Router.php`, `Auth.php` và cách gắn Admin Layout; không thể gắn Dashboard vào luồng chạy của nhóm một cách xác minh được.
- **BLOCKED-03**: Chưa có URL/route đang chạy và tài khoản test User/Admin; không thể kiểm tra đăng nhập, phân quyền, CRUD, upload, editor và comment.
- **BLOCKED-04 (đã giải quyết một phần)**: Đã nhận nội dung README qua chat: PHP 8.x, MySQL 8.x, Bootstrap 5, MVC/PDO, hướng dẫn Apache. Tuy nhiên chưa có tệp README từ commit, cấu hình server và tài khoản demo thực tế.
- **BLOCKED-05**: Môi trường tạo gói chưa có `pdo_mysql` và không có MySQL test được cung cấp; không thể chạy truy vấn thật.

## Mẫu ghi một lỗi mới

- ID:
- Module:
- Độ nghiêm trọng:
- Commit/phiên bản:
- Môi trường:
- Điều kiện trước khi thử:
- Bước 1, 2, 3:
- Kết quả mong đợi:
- Kết quả thực tế:
- Bằng chứng ảnh/log:
- Người xử lý:
- Trạng thái sau khi sửa:

## Kết quả kiểm tra bổ sung 08/11/2026

- Unit test Dashboard trên PDO giả lập: 36 PASS / 0 FAIL (sau sửa lỗi silent PDO).
- Test view độc lập: 5 PASS / 0 FAIL.
- Kiểm tra cú pháp PHP: 16 PASS / 0 FAIL.
- HTTP: bảo vệ Basic Auth cục bộ đạt trường hợp 401 khi thiếu/sai thông tin. Nếu có quyền nhưng chưa có pdo_mysql, server báo HTTP 503, không tạo số liệu MySQL giả.
- Demo MySQL có SQL/schema mẫu riêng và Docker Compose; **chưa chạy MySQL thật tại đây** nên giữ BLOCKED-05 cho kiểm thử MySQL.
- Những kết quả trên **không chứng minh các module Auth/Article/Category/Comment của nhóm đã hoạt động**.


## Sửa lỗi đã thực hiện ở bản mô-đun độc lập

- **FIX-LOCAL-01 (đã sửa và có test):** Khi PDO ở chế độ `ERRMODE_SILENT` trả `false` từ `fetchColumn()`, mã gốc có thể ép `false` thành `0`, dẫn tới thống kê sai. Đã thêm `readCount()` để phân biệt lỗi với COUNT hợp lệ là `0`. Test M21–M22 PASS.
- **FIX-LOCAL-02 (đã sửa và có test):** Nếu `execute()` của truy vấn Published / latest trả `false`, phải phát sinh lỗi để dashboard giữ số liệu `null` / cảnh báo, không hiển thị sai. Test M23–M24 PASS.
- **Cập nhật 11/11/2026:** đã kiểm thử **giao diện Dashboard độc lập với mock** bằng Chromium tại desktop 1440 px và mobile 390 px, 5 thẻ số liệu và 5 dòng bài, không tràn ngang. Không coi đây là kiểm thử responsive của website nhóm.
