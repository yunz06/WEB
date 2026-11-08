# BẢN BÀN GIAO — DASHBOARD, KIỂM THỬ VÀ README

**Kiểm thử độc lập cập nhật 08/11/2026.** Xem [TEST_REPORT_2026-11-08.md](TEST_REPORT_2026-11-08.md) để biết trạng thái chính xác từng mức. Nội dung README của nhóm do người dùng gửi được chép nguyên văn tại `README_NHOM_NGUOI_DUNG_DA_GUI.md`.

- Có thêm `compose.yaml`, `deploy/Dockerfile`, `database/demo_schema.sql`, `database/demo_seed.sql`, `public/dashboard.php` và `config/dashboard.demo.php` cho **DEMO ĐỘC LẬP** có MySQL. Đây KHÔNG PHẢI database hoặc mã nguồn nhóm.
- Đọc **[RUN_LOCAL_DEMO.md](RUN_LOCAL_DEMO.md)** để chạy trên Docker Desktop / Windows; thao tác này phải được thực hiện trên máy có Docker vì môi trường tạo gói thiếu MySQL, `pdo_mysql` và Docker.
- Có `tests/dashboard_unit_test.php` kiểm tra model/controller/view bằng PDO giả lập, không cần MySQL; và `tests/demo_database_test.php` để test **MySQL demo thật** khi Docker đang chạy.
- `config/dashboard.php` vẫn giữ `null` cho những tên cột chưa được đối chiếu với source nhóm. Cần nhận code/README/schema nhóm trước khi đưa vào nhánh GitHub nhóm.
- **CHƯA tích hợp dự án nhóm, CHƯA chạy MySQL demo tại môi trường này, CHƯA merge hay khẳng định 4 module khác PASS.**

---

# DASHBOARD ADMIN — PHẦN VIỆC ĐƯỢC GIAO

**Môn:** Lập trình Web — Bài tập nhóm cuối kỳ  
**Phạm vi:** Dashboard Admin đơn giản, thống kê MySQL, bài viết mới, test cases, danh sách lỗi, README.

> **QUAN TRỌNG:** Đây là **bộ mã riêng để tích hợp vào dự án nhóm**, chưa phải dự án hoàn chỉnh. Bảng phân công và nội dung README nhóm do người dùng gửi là các nguồn mô tả cấu trúc/định hướng; không thay thế source thật. Chưa có repository, schema SQL, `Database.php`, `Router.php`, `Auth.php`, layout Admin. Đã có nội dung README được người dùng gửi qua chat, nhưng chưa có source/commit thực tế của nhóm. Vì vậy **chưa được xác nhận chạy cùng dự án và chưa commit/merge**. Những tên cột chưa biết để `null` và có cảnh báo, không dùng số liệu giả.

## 1. Nội dung đã chuẩn bị

| File | Công việc |
|---|---|
| `app/Models/Dashboard.php` | Truy vấn `COUNT(*)` từ MySQL; đếm bài Published khi xác minh cột/trạng thái; lấy tối đa 5 bài mới theo cột sắp xếp đã xác minh |
| `app/Controllers/Admin/DashboardController.php` | Controller cần kiểm tra quyền Admin trước khi trả phần nội dung view |
| `app/Views/admin/dashboard/index.php` | Giao diện dashboard đơn giản, thẻ số liệu và bảng bài mới; escape HTML |
| `public/assets/css/admin.css` | **CSS chỉ cho Dashboard**, có responsive; phải gộp với file của TV1 nếu tồn tại |
| `public/assets/js/admin.js` | **JS chỉ cho Dashboard**, thao tác tải lại trang; phải gộp nếu file có sẵn |
| `config/dashboard.php` | Cấu hình tên bảng từ phân công, tên cột để trống vì chưa có schema |
| `tests/auth_test.php` | Kiểm tra schema users ở mức read-only + điểm chặn kiểm thử luồng login |
| `tests/article_test.php` | Kiểm tra thống kê bài, Published, 5 bài mới ở mức read-only + điểm chặn CRUD |
| `tests/category_test.php` | Kiểm tra bảng/ID danh mục ở mức read-only + điểm chặn nghiệp vụ |
| `tests/comment_test.php` | Kiểm tra bảng/cột bình luận ở mức read-only + điểm chặn nghiệp vụ |
| `tests/run.php` | Runner dùng cơ sở dữ liệu MySQL thật, không có mock hoặc dữ liệu giả |
| `tests/view_test.php` | Kiểm tra tĩnh việc hiển thị và escape HTML; dùng dữ liệu giả **chỉ ở unit test**, không hiển thị cho người dùng |
| `tests/TEST_CASES.md` | Test case thủ công cho Auth/Article/Category/Comment/Dashboard |
| `tests/BUG_LIST.md` | Bug list và các blocker chưa thể xác minh |

## 2. Yêu cầu môi trường

- PHP phiên bản có hỗ trợ cú pháp typed properties, `str_contains()` (PHP 8.0 trở lên) và PDO.
- Extension `pdo_mysql` để kết nối cơ sở dữ liệu MySQL.
- MySQL và schema do thành viên phụ trách Database cung cấp.
- Router, Auth, Admin Layout và autoload theo quy tắc của dự án nhóm.
- README nhóm ghi rõ Apache/PHP server, PHP 8.x, MySQL 8.x, Bootstrap 5, PDO và MVC. Chưa có cấu hình Apache, router và mã thực tế để xác minh cách khởi chạy chính xác.

## 3. Cấu trúc thư mục (file do phần việc này chuẩn bị)

```text
app/
  Controllers/Admin/DashboardController.php
  Models/Dashboard.php
  Views/admin/dashboard/index.php
config/
  dashboard.php
public/
  assets/css/admin.css
  assets/js/admin.js
tests/
  TestReporter.php
  helpers.php
  run.php
  auth_test.php
  article_test.php
  category_test.php
  comment_test.php
  view_test.php
  TEST_CASES.md
  BUG_LIST.md
README.md
```

## 4. Các bước tích hợp đúng vào repo của nhóm

1. Nhận **commit/nhánh hiện tại** của nhóm hoặc tệp ZIP dự án, tránh chồng đè phần người khác.
2. Đọc `database/news_hub.sql`, `database/seed.sql` (nếu có) và `app/Core/Database.php`.
3. So sánh bảng `articles`, `users`, `categories`, `comments` của schema với `config/dashboard.php`. Chỉ thay `null` bằng tên cột/giá trị **đúng thực tế**. Ví dụ: `article.status_column` là cột nào thể hiện trạng thái bài; `article.published_value` phải là đúng giá trị DB lưu cho Published (không mặc định là chữ `published`). `article.sort_column` phải là cột nhóm chốt để xác định bài mới. `title_column` là cột tiêu đề thực.
4. Tích hợp constructor `Dashboard` nhận **kết nối PDO do TV1 cung cấp**, không tự tạo kết nối/song song hoặc sửa config DB của người khác.
5. Kết nối `DashboardController` với Auth của TV1. Callback `hasAdminPermission` **phải trả chính xác `true` chỉ khi đã xác thực Admin**. Từ chối khi chưa đăng nhập, quyền User hoặc lỗi truy vấn quyền; không thay bằng `fn() => true`.
6. Router thêm route Admin Dashboard theo quy tắc của nhóm, yêu cầu xác thực Admin. **Tên route/URL chưa xác định** nên không đoán `/admin/dashboard` là route đã chạy.
7. Controller trả về **HTML nội dung**; nhúng HTML này vào Admin Layout chung theo cách nhóm đang dùng. Không thay layout của người khác.
8. **GỘP** CSS/JS dành riêng cho Dashboard vào `public/assets/css/admin.css` và `public/assets/js/admin.js` đang có; **KHÔNG ghi đè** file có sẵn của TV1.
9. Sử dụng DB test (không dùng DB chứa dữ liệu thật), kiểm thử bằng `tests/run.php`, sau đó kiểm thử chức năng theo `tests/TEST_CASES.md`.
10. Ghi `BUG_LIST.md`, hoàn thiện tài khoản demo (do TV1 cấp), rồi commit lên nhánh được giao; tạo Pull Request để nhóm merge và test tích hợp.

### Phác thảo kết nối trong mã của TV1 (không phải route đã xác minh)

```php
<?php

// $pdo: Đối tượng PDO do lớp Database hiện hữu của TV1 trả về.
// $canAccessAdmin: callback đã kết nối thật với Auth của nhóm,
// trả true duy nhất khi tài khoản đang đăng nhập có quyền Admin.
// CHƯA được phép dùng đoạn này làm route thật nếu chưa xem Router/Auth.

$config = require 'config/dashboard.php';

$model = new \App\Models\Dashboard($pdo, $config);
$controller = new \App\Controllers\Admin\DashboardController(
    $model,
    $canAccessAdmin
);

$content = $controller->index();

// Gắn $content vào Admin Layout hiện tại của nhóm.
// Không tự đặt đường dẫn layout vì chưa có mã nguồn.
```

**Xử lý lỗi phân quyền:** `DashboardController::index()` ném `RuntimeException` với mã 403 khi callback không trả về `true`. Bộ Router/middleware của TV1 cần chuyển thành HTTP 403/redirect phù hợp, thay vì hiển thị stack trace.

## 5. Cài database và cấu hình chạy — chỉ thực hiện khi có project gốc

1. Cài MySQL và PHP có `pdo_mysql`; kiểm tra bằng `php -m` để thấy `PDO` và `pdo_mysql`.
2. Tạo database theo tên **được nhóm chốt**; import SQL do TV1 cung cấp (ảnh nêu `database/news_hub.sql` và `seed.sql`). Không tự tạo bảng mẫu thay cho schema nhóm.
3. Điền thông tin host, port, database, user, password vào `config/database.php` **theo quy ước của nhóm**; không đưa mật khẩu vào Git.
4. Điền tên cột và trạng thái đúng schema vào `config/dashboard.php`.
5. Chạy dự án bằng cách được nêu trong **README gốc của nhóm** (chưa nhận nên không thể đưa câu lệnh chắc chắn đúng).
6. Dùng tài khoản Admin test do nhóm cung cấp để mở Dashboard và kiểm chứng số liệu bằng SQL.

Ví dụ **cú pháp** lệnh import MySQL, chỉ sử dụng sau khi chốt tên DB và file:

```bash
mysql -u <ten_user> -p <ten_database> < database/news_hub.sql
```

Dấu `<...>` là chỗ điền thông tin thật của nhóm, **không phải tài khoản hoặc DB đã xác minh**. Trên PowerShell, lệnh chuyển hướng `<` có thể không hoạt động như shell Bash/CMD; dùng MySQL Workbench, phpMyAdmin hoặc MySQL CLI qua CMD thay thế.

## 6. Chạy các bài test

### Kiểm tra cú pháp PHP (không cần MySQL)

```bash
php -l app/Models/Dashboard.php
php -l app/Controllers/Admin/DashboardController.php
php -l app/Views/admin/dashboard/index.php
php tests/view_test.php
```

### Kiểm tra SQL read-only bằng DB test (cần MySQL thật)

**Ví dụ Linux / Bash:**

```bash
export TEST_DB_DSN='mysql:host=127.0.0.1;port=3306;dbname=TEN_DB_TEST;charset=utf8mb4'
export TEST_DB_USER='TEN_USER_TEST'
export TEST_DB_PASSWORD='MAT_KHAU_TEST'
php tests/run.php
```

**Ví dụ Windows PowerShell:**

```powershell
$env:TEST_DB_DSN = 'mysql:host=127.0.0.1;port=3306;dbname=TEN_DB_TEST;charset=utf8mb4'
$env:TEST_DB_USER = 'TEN_USER_TEST'
$env:TEST_DB_PASSWORD = 'MAT_KHAU_TEST'
php tests/run.php
```

**Lưu ý:** Trong lệnh trên, `TEN_DB_TEST`, `TEN_USER_TEST`, `MAT_KHAU_TEST` chỉ là chữ gợi chỗ điền, không phải thông tin thật. Không dán mật khẩu ra nhóm chat hoặc commit file môi trường.

- `PASS`: kiểm tra cụ thể đã chạy và đạt.
- `FAIL`: có kiểm tra chạy không đạt, cần điều tra.
- `BLOCKED`: thiếu schema, config, server, tài khoản test hoặc driver; **không phải PASS**.
- Mã thoát `0`: không FAIL/BLOCKED; `1`: có FAIL; `2`: có BLOCKED, không có FAIL.
- Các script SQL chỉ **SELECT**, không ghi database. Chúng **không thay thế** bài kiểm thử chức năng CRUD, upload, phân quyền, moderation trong `tests/TEST_CASES.md`.

## 7. Tài khoản demo

**Chưa được nhóm cung cấp; không tự đặt tài khoản hoặc mật khẩu demo.**

Sau khi nhận thông tin thật, điền vào phần này bằng cách nhóm chấp thuận. Nên dùng tài khoản thử nghiệm với mật khẩu có thể đổi và không dùng thông tin người thật.

## 8. Kết quả xác minh tại thời điểm tạo gói

- Kiểm tra cú pháp của các file `.php`: **PASS** trong môi trường tạo gói (PHP 8.4.24).
- `php tests/view_test.php`: **5/5 PASS**, chỉ kiểm tra render với input giả ở test.
- `php tests/run.php`: **BLOCKED** vì không có `TEST_DB_DSN`; môi trường kiểm tra cũng chưa có extension `pdo_mysql`.
- Gắn Router/Admin Layout/Auth: **CHƯA LÀM** (thiếu source code nhóm).
- Kết quả truy vấn MySQL thật, quyền Admin/User, CRUD module, tạo bug có bằng chứng, push/merge GitHub: **CHƯA THỰC HIỆN / CHƯA XÁC NHẬN**.

## 9. Điều kiện bàn giao còn thiếu

- [ ] Nhận repository/ZIP hiện tại; đã đối chiếu nội dung README người dùng gửi.
- [ ] Xác minh schema và kết nối MySQL; điền `config/dashboard.php`.
- [ ] Tích hợp đúng Router, Auth và Admin Layout.
- [ ] Gộp CSS/JS vào asset hiện hữu, không ghi đè của TV1.
- [ ] Kiểm tra Dashboard bằng dữ liệu MySQL thật; đối chiếu SQL độc lập.
- [ ] Kiểm thử 4 module theo checklist, ghi log/ảnh và BUG_LIST khi có lỗi.
- [ ] Điền hướng dẫn cài đặt/chạy thực tế và thông tin tài khoản demo hợp lệ.
- [ ] Commit, Pull Request, merge và test sau merge theo yêu cầu trưởng nhóm.


## 10. Kết quả kiểm thử cập nhật 08/11/2026

- **36/36 unit test đạt** sau khi bổ sung xử lý lỗi `PDOStatement::execute() === false` và `fetchColumn() === false` nhằm tránh hiện số liệu 0 sai khi MySQL lỗi.
- **5/5 kiểm thử view đạt**, XSS escape được kiểm chứng bằng dữ liệu giả trong kiểm thử.
- **16/16 file PHP hợp lệ**, PHP CLI 8.4.24.
- **7/7 HTTP smoke test đạt** trên server PHP cục bộ; HTTP 503 khi thiếu PDO MySQL là hành vi thất bại an toàn, **KHÔNG phải Dashboard chạy được MySQL**.
- **30/30 kiểm tra cấu hình/schema ở mức tĩnh đạt**, không chứng minh truy vấn trên MySQL thật.
- **MySQL 8.x thật: BLOCKED**, vì môi trường không có Docker, MySQL và `pdo_mysql`; máy hiện tại không truy cập được kho phần mềm để cài thêm.
- **Tích hợp và kiểm thử chức năng toàn bộ NEWS HUB: BLOCKED**, vì chưa có ZIP/repository, schema `database/news_hub.sql`, các controller và dữ liệu/route của dự án nhóm.

Mã được thử ở bản PHP 8.4.24; bản Docker dùng PHP 8.3 và MySQL 8.4 nhưng **chưa chạy Docker ở môi trường tạo gói**. Không dùng số liệu của demo làm bằng chứng dự án nhóm.
