# NEWS HUB — InfinityFree production package

NEWS HUB là website tin tức PHP MVC sử dụng PHP 8.2+, PDO MySQL, HTML/CSS/JavaScript và Bootstrap 5. Gói này đã tách file chạy website khỏi script tạo database, dữ liệu production và công cụ tạo Admin.

## Cấu trúc gói

- `upload/`: chỉ chứa mã nguồn và tài nguyên cần đặt trong thư mục `htdocs` của InfinityFree.
- `LOCAL_SETUP/01-schema-infinityfree.sql`: tạo sáu bảng và hai role trong database đã tạo từ Control Panel.
- `LOCAL_SETUP/generate-admin-sql.ps1`: tạo SQL bootstrap Admin bằng mật khẩu nhập ẩn, chạy trên Windows + XAMPP PHP CLI.
- `LOCAL_SETUP/02-production-content-infinityfree.sql`: tạo 26 danh mục và tám bài biên tập có nguồn. Không có tài khoản demo, bình luận demo hay 36 bài placeholder.
- `README-INFINITYFREE.md`: hướng dẫn triển khai đầy đủ.

## Lưu ý dữ liệu

Gói này khởi tạo database production mới. Nó không nhập tài khoản/bình luận riêng đang có trên XAMPP. Không import seed local 44 bài vào production. Không chạy lại các file setup sau khi website đã có dữ liệu thật.

## Chức năng

Guest có thể đọc tin, duyệt chuyên mục, tìm kiếm và phân trang. User có thể đăng ký, đăng nhập, quản lý khu vực tài khoản và gửi bình luận. Admin có thể quản trị bài viết, danh mục, người dùng và bình luận; hỗ trợ rich text editor và upload ảnh đại diện/nhiều ảnh nội dung.

## Kiểm tra upload

InfinityFree giới hạn dung lượng một request upload PHP. NEWS HUB giới hạn mỗi ảnh 5 MB và tổng ảnh gửi trong một lần tối đa 18 MB để chừa dung lượng multipart overhead. Nếu hosting thay đổi giới hạn, không tăng giới hạn ứng dụng vượt `post_max_size` thực tế.

## Bảo mật

- Điền thông tin DB trong `upload/config/database.local.php`; file này được chặn truy cập HTTP bằng `.htaccess`.
- Không để file backup, SQL hoặc mật khẩu trong `htdocs`.
- Không dùng mật khẩu demo local hoặc mật khẩu đã chia sẻ công khai.
- Xóa file `admin-bootstrap.generated.sql` sau khi import vào phpMyAdmin.
- Hạn chế chia sẻ tài khoản Admin. Dùng HTTPS/SSL của domain trước khi đăng nhập.


# Hướng dẫn deploy chi tiết

## Bước 1 — Tạo hosting miễn phí

1. Mở https://www.infinityfree.com/ và đăng ký tài khoản.
2. Tạo một hosting account và chọn subdomain miễn phí hoặc domain riêng nếu bạn đã có.
3. Vào Client Area → Accounts → Manage → Control Panel.
4. Trong File Manager hoặc FTP, mở đúng thư mục `htdocs` của domain. Với addon/subdomain, kiểm tra mục Domains để biết chính xác thư mục gắn với domain.

**Chỉ upload nội dung bên trong thư mục `upload/` vào `htdocs`, không upload nguyên thư mục `upload` và không upload `LOCAL_SETUP/`.** Sau khi upload, `htdocs/index.php` phải tồn tại trực tiếp.

## Bước 2 — Tạo database MySQL

1. Trong Control Panel, mở **MySQL Databases**.
2. Nhập tên gợi nhớ như `news_hub`, nhấn Create Database.
3. Sao chép nguyên văn các giá trị trong Current Databases: **MySQL DB Name**, **MySQL User Name**, **MySQL Host Name**.
4. Mật khẩu database là hosting account password hiển thị trong Client Area/Account Password theo hướng dẫn của InfinityFree; không dùng `localhost`, không dùng mật khẩu đăng nhập Client Area nếu khác hosting account password.
5. Mở phpMyAdmin từ Control Panel.

InfinityFree tự thêm tiền tố account vào DB name/user. Vì vậy tên thật có thể giống `if0_12345678_news_hub`, không phải `news_hub`. Database miễn phí chỉ nhận kết nối từ PHP chạy trong hosting hoặc phpMyAdmin; không kết nối trực tiếp từ MySQL Workbench trên laptop.

## Bước 3 — Import schema

1. Trong phpMyAdmin, chọn database vừa tạo ở cột bên trái.
2. Chọn Import, chọn `LOCAL_SETUP/01-schema-infinityfree.sql` trên máy bạn.
3. Giữ UTF-8/utf8mb4 nếu có lựa chọn, chạy import.
4. Kiểm tra có đúng sáu bảng: `roles`, `users`, `categories`, `articles`, `article_images`, `comments`.

File schema dành riêng cho shared hosting: nó **không** chạy `CREATE DATABASE`, `ALTER DATABASE` hoặc `USE`, vì database đã được tạo từ Control Panel và có tên tiền tố.

## Bước 4 — Tạo tài khoản Admin production

Không dùng Admin demo local. Trên Windows có XAMPP:

1. Giải nén gói trên máy tính, chưa upload `LOCAL_SETUP`.
2. Mở PowerShell tại thư mục `LOCAL_SETUP`.
3. Chạy:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\generate-admin-sql.ps1
```

4. Nhập email Admin, tên hiển thị và mật khẩu mới. Ô mật khẩu nhập ẩn; mật khẩu phải dài 12–72 byte, có chữ thường, chữ hoa, số và nên có ký hiệu.
5. Script dùng PHP CLI cục bộ để tạo hash, không kết nối database và tạo file `admin-bootstrap.generated.sql`.
6. Trên phpMyAdmin của hosting, vẫn chọn database production, Import file `admin-bootstrap.generated.sql`.
7. Vào bảng `users`, xác nhận đúng email và role `admin`.
8. Xóa `admin-bootstrap.generated.sql` khỏi máy sau khi import và không upload thư mục `LOCAL_SETUP` lên hosting.

Nếu không có XAMPP PHP CLI, cài PHP CLI hoặc nhờ người quản trị máy chạy script tạo hash; không đặt mật khẩu plaintext trực tiếp vào SQL.

## Bước 5 — Tạo dữ liệu nội dung production

Sau khi có Admin:

1. Chọn đúng database production trong phpMyAdmin.
2. Import `LOCAL_SETUP/02-production-content-infinityfree.sql`.
3. Script tạo 26 categories và tám bài tóm lược có link nguồn; không tạo users demo, comments demo hoặc bài `[MẪU]`.
4. Khi chạy xong, kiểm tra kết quả `published_editorial_articles` bằng 8.
5. Không import file này lần thứ hai; đây là bộ khởi tạo database production mới.

## Bước 6 — Cấu hình kết nối DB trong source

Trên máy bạn, trong thư mục `upload/config`:

1. Sao chép `database.local.example.php` thành `database.local.php`.
2. Mở `database.local.php`, thay các giá trị bằng đúng thông tin MySQL Databases trên InfinityFree:
   - `host`: MySQL Host Name (không phải localhost).
   - `name`: MySQL DB Name đầy đủ có tiền tố.
   - `user`: MySQL User Name đầy đủ.
   - `pass`: hosting account password.
   - `port`: giá trị hosting cấp (thông thường 3306).
   - `charset`: `utf8mb4`.
3. Upload riêng file `database.local.php` vào `htdocs/config/` bằng FTP/File Manager.
4. Không gửi file này cho người khác và không commit lên Git. `.htaccess` chặn toàn bộ thư mục `config` khỏi truy cập HTTP.

Nếu dùng file manager để tạo trực tiếp trên hosting, hãy tạo `config/database.local.php` với nội dung dựa trên example và không chụp/đăng thông tin kết nối.

## Bước 7 — Upload source

Dùng File Manager/FTP để upload **toàn bộ nội dung trong `upload/`** thẳng vào `htdocs`:

```text
htdocs/
  index.php
  .htaccess
  app/
  config/
    database.php
    database.local.example.php
    database.local.php       (file riêng chứa thông tin DB)
  public/
    css/
    js/
    uploads/
      .htaccess
      articles/
      editorial/
```

Đừng để thành `htdocs/upload/index.php` hoặc `htdocs/Webtintuc/index.php`. InfinityFree yêu cầu file website nằm trực tiếp trong `htdocs` của domain.

## Bước 8 — Kiểm tra

Mở domain/subdomain được cấp:

- Trang chủ: `https://TEN-MIEN-CUA-BAN/`
- Đăng nhập Admin: `https://TEN-MIEN-CUA-BAN/AuthController/login?area=admin`
- Đăng ký User: `https://TEN-MIEN-CUA-BAN/AuthController/register`

Kiểm tra theo thứ tự: trang chủ → danh mục → bài viết → đăng ký User → đăng nhập/đăng xuất → Admin login → tạo bài nháp → sửa bài → upload nhiều ảnh (tổng dưới 18 MB/request) → xuất bản → kiểm tra trên trình duyệt ẩn danh → duyệt bình luận.

## Lưu ý giới hạn InfinityFree

- PHP hiện được thông báo là PHP 8.4 trên free hosting; PDO MySQL và MySQLi được hỗ trợ. NEWS HUB cần `pdo_mysql`, `mbstring`, `fileinfo`, `dom`; nếu gặp lỗi extension, kiểm tra PHP configuration/account support trước khi sửa code.
- 20 MB là giới hạn upload qua PHP request được tài liệu hỗ trợ công bố. NEWS HUB giới hạn tổng ảnh mỗi request 18 MB, từng ảnh 5 MB.
- Giới hạn file trên tài khoản: PHP/HTML/JS 1 MB mỗi file; `.htaccess` 10 kB; file khác 10 MB. Gói này được rà soát theo các ngưỡng đó.
- MySQL chỉ truy cập từ PHP trên InfinityFree hoặc phpMyAdmin; không mở kết nối trực tiếp từ laptop.
- Tạo backup SQL định kỳ qua phpMyAdmin. Free hosting không thay thế dịch vụ production có SLA; hãy giữ bản sao database và ảnh ngoài hosting.
- Không upload file backup `.sql`, `.env`, cookie, log hoặc thư mục local setup vào `htdocs`.
- Free subdomain/SSL có thể cần thời gian cấp chứng chỉ. Chỉ dùng form đăng nhập sau khi HTTPS/SSL hoạt động.

## Nếu hiện lỗi

- HTTP 403: kiểm tra `.htaccess`; không xóa quy tắc bảo vệ `app`, `config`, `database` hoặc `public/uploads`.
- HTTP 404: kiểm tra `index.php` có trực tiếp trong `htdocs` và domain trỏ đúng `htdocs`.
- Database connection unavailable: kiểm tra chính xác host, DB name, username, password và chắc chắn file là `config/database.local.php`.
- Lỗi upload 403/413 hoặc form bị trống: giảm tổng ảnh xuống dưới 18 MB; kiểm tra từng ảnh không quá 5 MB.
- Không đưa thông báo lỗi chứa thông tin DB/password lên diễn đàn công khai.

## Nguồn hướng dẫn chính thức

- InfinityFree: https://www.infinityfree.com/
- Tạo database: https://forum.infinityfree.com/t/how-to-setup-a-new-mysql-database/49342/1
- Kết nối PDO/MySQL: https://forum.infinityfree.com/t/how-to-connect-with-mysql/49340
- Thư mục htdocs: https://forum.infinityfree.com/t/why-do-i-see-the-default-welcome-page/49317
- Giới hạn upload: https://forum.infinityfree.com/t/file-size-and-upload-limits/49308
- Kết nối database bên ngoài: https://forum.infinityfree.com/t/connecting-to-mysql-from-an-external-application/49339/1

## Đường dẫn Admin và tài khoản local (chỉ để phân biệt với production)

Trong bản XAMPP local cũ, cổng Admin là `/AuthController/login?area=admin` và Dashboard là `/DashboardController/index`. Tài khoản demo từ local seed (`admin@newshub.local`) chỉ dùng để thử nghiệm local, không được import vào database production của InfinityFree. Trên production, hãy tạo Admin riêng qua `LOCAL_SETUP/generate-admin-sql.ps1` rồi đăng nhập tại `/AuthController/login?area=admin`.

## Dữ liệu local và production

Khi local chạy XAMPP, request đi qua Apache/PHP → Controller/Model → PDO → MariaDB `news_hub` trên chính máy local. Khi production chạy InfinityFree, request đi qua PHP trên hosting → PDO → MySQL database do InfinityFree cấp. Tạo tài khoản, đăng bài, sửa/xóa bài trên website public sẽ thay đổi database production; dữ liệu không tự đồng bộ về XAMPP local. InfinityFree free hosting không cho laptop kết nối trực tiếp tới database từ xa; dùng phpMyAdmin của hosting để quản trị/import.
