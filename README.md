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

Với database mới vừa import seed, tài khoản demo mặc định là `admin@newshub.local` / `NewsHubDemo!2026`. Tài khoản này chỉ dành cho máy local và có thể không còn đúng nếu mật khẩu đã được đổi trước đó.

Để tạo hoặc đặt lại mật khẩu cho tài khoản Admin local, chạy trong PowerShell tại thư mục project:

```powershell
D:\xampp\php\php.exe tools\manage-local-admin.php
```

Công cụ chỉ chạy bằng CLI, không có route HTTP. Nó có thể tạo Admin mới, đặt lại mật khẩu Admin hiện có, hoặc chuyển một tài khoản User hiện có thành Admin **chỉ khi người chạy nhập chính xác `PROMOTE` để xác nhận**. Việc nâng quyền cấp toàn bộ quyền quản trị, vì vậy chỉ chủ dự án mới được chạy công cụ trên máy tin cậy. Trên Windows, mật khẩu được nhập qua PowerShell SecureString; trên terminal hỗ trợ `stty`, mật khẩu không được echo. Không chạy công cụ này trên production.

Đăng ký công khai chỉ tạo tài khoản `user`, không thể tự cấp quyền Admin. Với database mới vừa import seed, tài khoản demo là `admin@newshub.local` / `NewsHubDemo!2026`; đây chỉ là thông tin mặc định cho database mới và có thể không còn đúng sau khi chủ dự án thay mật khẩu. Nếu dùng email cá nhân đã đăng ký làm User, công cụ sẽ yêu cầu xác nhận `PROMOTE` trước khi cấp role Admin.

## Nội dung bài viết và nguồn

Seed có tám bài viết được NEWS HUB biên tập/tóm lược từ nguồn công khai, mỗi bài có liên kết tham khảo ở cuối nội dung. Các bài còn lại được đánh dấu `[MẪU]` để phân biệt nội dung minh họa. Không có bài báo nào được sao chép nguyên văn.

Nếu muốn cập nhật riêng các bài biên tập trên một database đã có dữ liệu, hãy backup database trước rồi mới cân nhắc chạy `database/editorial_content_update.sql`. Script chỉ cập nhật các slug demo được chỉ định và metadata ảnh liên quan; không import lại toàn bộ seed, không đổi schema, không reset tài khoản hoặc bình luận.

**Lưu ý về SQL Editor trong VS Code:** file này dùng cú pháp MariaDB/MySQL (`SET NAMES`, `START TRANSACTION`, `UPDATE ... JOIN`). Không kết nối hoặc chạy bằng extension/connection có nhãn **MSSQL / Microsoft SQL Server**. Gạch đỏ từ SQL Server IntelliSense không phải kết quả thực thi MariaDB. Cách dễ nhất trên XAMPP: bật Apache + MySQL, mở `http://localhost/phpmyadmin`, chọn database `news_hub`, vào tab **Import**, chọn `editorial_content_update.sql` rồi chạy. Hoặc dùng MariaDB CLI `mysql.exe` từ thư mục XAMPP với `--default-character-set=utf8mb4`. Script cần được thực thi trên MariaDB/MySQL, không phải SQL Server.

**Không chạy `database/seed.sql` trên database đang sử dụng.** Seed có thể cập nhật nội dung demo và tài khoản demo. Không chạy migration trên database hiện tại nếu chưa hiểu rõ thay đổi.

## Đường dẫn chính

- Trang chủ: `/`
- Chi tiết bài viết: `/FrontendArticleController/show/{id}`
- Chuyên mục: `/PublicCategoryController/show/{slug}`
- Tìm kiếm: `/SearchController/index?q=...`
- Đăng nhập: `/AuthController/login`
- Đăng ký: `/AuthController/register`
- Tài khoản độc giả: `/AccountController/index`
- Cổng Admin: `/AuthController/login?area=admin` (được đặt ở header với nhãn “Cổng quản trị” khi chưa đăng nhập).
- Dashboard Admin: `/DashboardController/index`.
- Admin đăng nhập bằng tài khoản có role `admin`; form đăng ký công khai không tạo Admin.

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
D:\xampp\php\php.exe tests\NavigationAndAdminEntryTest.php
D:\xampp\php\php.exe tests\AdminEntryViewTest.php
D:\xampp\php\php.exe tests\DestructiveActionUiTest.php
D:\xampp\php\php.exe tests\TypographyContractTest.php
D:\xampp\php\php.exe tests\HtmlSanitizerTest.php
D:\xampp\php\php.exe tests\AuthRegistrationPolicyTest.php
D:\xampp\php\php.exe tests\Utf8RoundTripTest.php
D:\xampp\php\php.exe tests\ProjectContractTest.php
D:\xampp\php\php.exe tests\ModelIntegrationTest.php
D:\xampp\php\php.exe tests\SeedDataTest.php

powershell -ExecutionPolicy Bypass -File tests\HttpSmokeTest.ps1
powershell -ExecutionPolicy Bypass -File tests\SensitiveFilesHttpTest.ps1
```

Các test tích hợp và HTTP cần MariaDB, Apache và extension tương ứng. Kiểm thử cú pháp PHP không thay thế cho việc chạy thử toàn bộ luồng trên XAMPP.

## Ghi chú triển khai

- Đây là cấu hình local phục vụ học tập/demo; cần rà soát lại cấu hình session, HTTPS, secrets, backup và logging trước khi đưa lên Internet.
- Google Fonts, Bootstrap CDN và TinyMCE CDN cần kết nối mạng. Nếu CDN không truy cập được, giao diện dùng font fallback và nội dung bài viết vẫn có textarea dự phòng.
- Hãy backup database trước mọi thao tác SQL cập nhật nội dung.

## Database hoạt động như thế nào khi chạy local

Khi bạn chạy XAMPP trên máy mình:

1. Apache nhận request từ trình duyệt (ví dụ đăng ký hoặc mở bài viết).
2. Apache chạy PHP trong `index.php`; Router kiểm tra route, HTTP method, đăng nhập và quyền.
3. Controller gọi Model; Model dùng PDO kết nối tới MariaDB qua `config/database.php`.
4. MariaDB lưu hoặc đọc dữ liệu trong database `news_hub`.
5. PHP nhận kết quả, render HTML rồi trả về trình duyệt. Trình duyệt không kết nối trực tiếp tới database.

Ví dụ: đăng ký tạo một dòng trong `users`; tạo/sửa/xóa bài viết thay đổi bảng `articles` và các ảnh liên quan trong `article_images`; bình luận tạo dòng trong `comments`. Các thao tác Admin cũng đi qua PHP và PDO. Dữ liệu vẫn còn sau khi tắt trình duyệt/Apache vì được lưu trong MariaDB, miễn là không xóa database.

`127.0.0.1` hoặc `localhost` chỉ trỏ về chính máy đang chạy PHP. Khi deploy lên Internet, không thể để `NEWS_HUB_DB_HOST=127.0.0.1` để trỏ tới XAMPP ở máy cá nhân. PHP trên máy chủ phải kết nối tới một database MySQL/MariaDB được host từ xa, với host, port, database, user và password riêng. Không mở cổng MariaDB ở máy cá nhân ra Internet để làm database production.

## Triển khai Internet

### Netlify

Netlify phù hợp để host frontend tĩnh và Functions, nhưng không cung cấp runtime PHP thường trực cho ứng dụng PHP MVC này. Vì vậy không thể chỉ upload ZIP PHP MVC lên Netlify rồi mong `index.php`, session, CRUD và PDO hoạt động. Nếu nhất định dùng Netlify, cần viết lại frontend thành ứng dụng tĩnh và chuyển toàn bộ backend PHP hiện tại thành API được host riêng; đó là một dự án chuyển kiến trúc khác, không phải cách deploy trực tiếp source này.

### Render bằng Docker (giữ PHP MVC)

Project có `Dockerfile`, `render.yaml` và health endpoint để triển khai dạng Docker Web Service. `render.yaml` tạo web service và persistent disk cho ảnh upload; database vẫn phải được tạo riêng và cung cấp thông tin kết nối qua Environment Variables. Quy trình tổng quát:

1. Đưa source lên GitHub riêng tư, không commit backup, `.env`, mật khẩu hoặc dữ liệu production.
2. Tạo MySQL/MariaDB database ở nhà cung cấp có kết nối từ ứng dụng Render (có thể dùng database service riêng hoặc nhà cung cấp database bên ngoài). Import `database/news_hub.sql`, sau đó `database/seed.sql` chỉ khi tạo database mới hoàn toàn.
3. Tạo Render Web Service, chọn runtime **Docker** và dùng `Dockerfile` trong repository.
4. Cấu hình Environment Variables theo `.env.example`: `NEWS_HUB_DB_HOST`, `NEWS_HUB_DB_PORT`, `NEWS_HUB_DB_NAME`, `NEWS_HUB_DB_USER`, `NEWS_HUB_DB_PASS`, `NEWS_HUB_DB_CHARSET=utf8mb4`. Nhập password trong mục secret/environment của nền tảng, không ghi vào source.
5. Cấu hình persistent disk cho `public/uploads/articles` nếu Admin cần upload ảnh lâu dài; filesystem container thông thường có thể không giữ file upload qua lần deploy/recreate nếu không có persistent storage. Docker entrypoint sẽ chép các ảnh mẫu mặc định vào thư mục upload persistent nếu disk đang rỗng; file người dùng đã upload không bị ghi đè.
6. Đặt health check path là `/health.php`, kiểm tra HTTPS, backup database, quyền ghi upload và email/password Admin trước khi công khai website. Health check chạy `SELECT 1` và chỉ trả `ok` hoặc `unavailable`, không tiết lộ thông tin kết nối.

Render hỗ trợ deploy web service bằng Docker, còn MySQL có thể chạy dưới dạng service riêng hoặc dùng database bên ngoài. Blueprint hiện chọn plan `starter` và disk 1 GB làm ví dụ; đây là cấu hình có thể phát sinh chi phí. Kiểm tra lại giá, loại disk, region và giới hạn plan hiện hành trong dashboard trước khi deploy. Deployment guide không có nghĩa website đã được deploy: cần tài khoản, database production và secrets của chủ dự án.

Không dùng database XAMPP local làm production database. Khi người dùng tạo tài khoản, đăng bài, sửa hoặc xóa bài trên website đã deploy, request sẽ được xử lý bởi PHP đang chạy trên server và PHP sẽ ghi vào database production từ xa; các máy người dùng đều nhìn thấy cùng dữ liệu đó.
