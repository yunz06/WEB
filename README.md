# NEWS HUB

NEWS HUB là website tin tức xây dựng bằng PHP MVC, chạy trên Apache/XAMPP và MariaDB. Giao diện sử dụng Bootstrap 5, CSS và JavaScript thuần.

## Công nghệ

- PHP 8.2+
- MariaDB 10.4.32 (tương thích giao thức MySQL)
- PDO MySQL
- HTML5, CSS3, JavaScript
- Bootstrap 5
- Playfair Display và IBM Plex Sans

## Chức năng

### Người đọc
- Xem trang chủ, tin nổi bật và tin mới.
- Duyệt chuyên mục cha/con.
- Xem chi tiết bài viết và bài liên quan.
- Tìm kiếm và phân trang.
- Đăng ký, đăng nhập, đăng xuất.
- Xem thông tin tài khoản và lịch sử bình luận.
- Gửi bình luận; bình luận mới chờ Admin duyệt.

### Admin
- Dashboard với số liệu lấy từ database.
- Quản lý bài viết: tạo, xem, sửa, xóa, lưu nháp, xuất bản, đánh dấu nổi bật.
- Upload ảnh đại diện và nhiều ảnh nội dung.
- Rich Text Editor (TinyMCE) với textarea dự phòng khi CDN không tải.
- Quản lý danh mục cha/con.
- Quản lý tài khoản: sửa thông tin, phân quyền và khóa/mở khóa.
- Duyệt, ẩn và xóa bình luận.

## Cài đặt local

1. Cài XAMPP có Apache, PHP và MariaDB.
2. Bật các PHP extension: `pdo_mysql`, `mbstring`, `fileinfo`, `dom`.
3. Chép thư mục `Webtintuc` vào `D:\xampp\htdocs\Webtintuc`.
4. Khởi động Apache và MariaDB.
5. Tạo database bằng cách import `database/news_hub.sql` trong phpMyAdmin.
6. Với database mới, import tiếp `database/seed.sql`.
7. Mở `http://localhost/Webtintuc/`.

SQL sử dụng `SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci`. Khi dùng MariaDB CLI, đặt `--default-character-set=utf8mb4`.

### Cấu hình database

Mặc định XAMPP local:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `news_hub`
- User: `root`
- Password: rỗng

Có thể ghi đè bằng biến môi trường:

- `NEWS_HUB_DB_HOST`
- `NEWS_HUB_DB_PORT`
- `NEWS_HUB_DB_NAME`
- `NEWS_HUB_DB_USER`
- `NEWS_HUB_DB_PASS`
- `NEWS_HUB_DB_CHARSET`

Không đưa mật khẩu database thật vào source hoặc Git.

## Đăng nhập Admin

- Trang đăng nhập quản trị: `http://localhost/Webtintuc/AuthController/login?area=admin`
- Dashboard sau đăng nhập: `http://localhost/Webtintuc/DashboardController/index`

Để tạo hoặc đặt lại mật khẩu cho tài khoản Admin local, chạy trong PowerShell tại thư mục project:

```powershell
D:\xampp\php\php.exe tools\manage-local-admin.php
```

Công cụ chỉ chạy bằng CLI, không có route HTTP. Nó chỉ cập nhật tài khoản đã có role `admin` hoặc tạo tài khoản Admin mới. Không nhập mật khẩu vào mã nguồn. Không chạy công cụ này trên môi trường production.

Đăng ký công khai chỉ tạo tài khoản `user`, không thể tự cấp quyền Admin.

## Nội dung bài viết và nguồn

Seed có tám bài viết được NEWS HUB biên tập/tóm lược từ nguồn công khai, mỗi bài có liên kết tham khảo ở cuối nội dung. Các bài còn lại được đánh dấu `[MẪU]` để phân biệt nội dung minh họa. Không có bài báo nào được sao chép nguyên văn.

Nếu muốn cập nhật riêng các bài biên tập trên một database đã có dữ liệu, hãy backup database trước rồi mới cân nhắc chạy `database/editorial_content_update.sql`. Script chỉ cập nhật các slug demo được chỉ định và metadata ảnh liên quan; không import lại toàn bộ seed, không đổi schema, không reset tài khoản hoặc bình luận.

**Không chạy `database/seed.sql` trên database đang sử dụng.** Seed có thể cập nhật nội dung demo và tài khoản demo. Không chạy migration trên database hiện tại nếu chưa hiểu rõ thay đổi.

## Đường dẫn chính

- Trang chủ: `/`
- Chi tiết bài viết: `/FrontendArticleController/show/{id}`
- Chuyên mục: `/PublicCategoryController/show/{slug}`
- Tìm kiếm: `/SearchController/index?q=...`
- Đăng nhập: `/AuthController/login`
- Đăng ký: `/AuthController/register`
- Tài khoản độc giả: `/AccountController/index`
- Cổng Admin: `/AuthController/login?area=admin`
- Dashboard Admin: `/DashboardController/index`

## Bảo mật và dữ liệu

- Router dùng allowlist.
- Mọi POST yêu cầu CSRF token.
- Admin được xác minh role từ database.
- PDO prepared statements.
- Mật khẩu lưu bằng `password_hash`.
- Nội dung bài viết qua HTML sanitizer.
- Bình luận và dữ liệu hiển thị được escape.
- Upload kiểm tra MIME/nội dung, giới hạn dung lượng và đổi tên file.
- Thư mục upload chặn thực thi script.
- Draft và bài chưa đến thời gian xuất bản không hiển thị công khai.

Database nghiệp vụ giữ đúng sáu bảng: `roles`, `users`, `categories`, `articles`, `article_images`, `comments`.

## Kiểm thử

Chạy từ thư mục project bằng PHP của XAMPP:

```powershell
D:\xampp\php\php.exe -l index.php
Get-ChildItem app,config,tests,tools -Recurse -Filter *.php | ForEach-Object { D:\xampp\php\php.exe -l $_.FullName }

D:\xampp\php\php.exe tests\ProductJourneyContractTest.php
D:\xampp\php\php.exe tests\DestructiveActionUiTest.php
D:\xampp\php\php.exe tests\TypographyContractTest.php
D:\xampp\php\php.exe tests\HtmlSanitizerTest.php
D:\xampp\php\php.exe tests\AuthRegistrationPolicyTest.php
D:\xampp\php\php.exe tests\Utf8RoundTripTest.php
D:\xampp\php\php.exe tests\ProjectContractTest.php
D:\xampp\php\php.exe tests\ModelIntegrationTest.php
D:\xampp\php\php.exe tests\SeedDataTest.php
D:\xampp\php\php.exe tests\SeedUtf8RepairTest.php

powershell -ExecutionPolicy Bypass -File tests\HttpSmokeTest.ps1
powershell -ExecutionPolicy Bypass -File tests\SensitiveFilesHttpTest.ps1
```

Các test tích hợp và HTTP cần MariaDB, Apache và extension tương ứng. Kiểm thử cú pháp PHP không thay thế cho việc chạy thử toàn bộ luồng trên XAMPP.

## Ghi chú triển khai

- Đây là cấu hình local phục vụ học tập/demo; cần rà soát lại cấu hình session, HTTPS, secrets, backup và logging trước khi đưa lên Internet.
- Google Fonts, Bootstrap CDN và TinyMCE CDN cần kết nối mạng. Nếu CDN không truy cập được, giao diện dùng font fallback và nội dung bài viết vẫn có textarea dự phòng.
- Hãy backup database trước mọi thao tác SQL cập nhật nội dung.
