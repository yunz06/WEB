# NEWS HUB — Bản hoàn thiện luồng ảnh và hướng dẫn triển khai

## 1. Đã xử lý trong source này

- Dùng `ArticleImage` làm nơi duy nhất kiểm tra và tạo URL ảnh cho trang chủ, bài chi tiết, chuyên mục, tìm kiếm và trang quản trị.
- Giao diện chỉ hiển thị file ảnh raster có thật trong `public/uploads/articles/`. Không tải ảnh từ `uploads/editorial/`, không tự thay ảnh bằng JavaScript, không dùng URL ảnh ngoài.
- Có cache-busting theo thời gian sửa file để tránh giữ ảnh cũ trong cache trình duyệt.
- Upload thumbnail và gallery qua trang quản trị; đổi thumbnail, gỡ thumbnail, xóa ảnh gallery; file cũ chỉ bị xóa nếu database không còn bài viết/album nào tham chiếu tới file đó.
- Tên file upload được sinh ngẫu nhiên, kiểm tra MIME thực tế, định dạng, kích thước file và độ phân giải. Không dùng tên file do người dùng nhập làm tên file lưu trữ.
- Database lưu tên file, không lưu nội dung nhị phân của ảnh.
- Thêm thông báo lỗi upload rõ hơn, nhận biết trường hợp request vượt `post_max_size`, kiểm tra quyền ghi thư mục ảnh trong trang quản trị.
- Thêm script migration idempotent `LOCAL_SETUP/05-image-storage-audit.sql` (tự bổ sung cột `articles.thumbnail`, tạo bảng `article_images` nếu thiếu, bổ sung hai index và xuất báo cáo tham chiếu ảnh) và kiểm tra tự động `tests/test_article_image.php`.
- Gỡ bộ ảnh minh họa cũ khỏi source deploy; file JavaScript cũ được giữ dưới dạng no-op để ghi đè bản runtime từng thay thumbnail.
- Bổ sung `meta name="news-hub-build"` để kiểm tra bản source nào đang chạy trên hosting.
- `public/js/editorial-refresh.js` hiện là file tương thích rỗng, dùng để ghi đè bản cũ từng thay thumbnail ở phía trình duyệt. Layout hiện tại không tải script đó.

## 2. Ảnh được lưu ở đâu?

Khi quản trị viên đăng bài và chọn thumbnail:

1. PHP nhận ảnh từ form `multipart/form-data`.
2. Máy chủ xác thực file ảnh, sau đó tạo tên ngẫu nhiên, ví dụ `3a5f...e91c.jpg`.
3. File được lưu tại:

   `upload/public/uploads/articles/3a5f...e91c.jpg`

4. Database lưu tên file đó ở cột `articles.thumbnail`:

   `3a5f...e91c.jpg`

5. Trang web đọc tên file từ database, xác minh file tồn tại và hiển thị từ URL:

   `/public/uploads/articles/3a5f...e91c.jpg`

Ảnh trong nội dung bài viết được lưu tương tự, nhưng tên file tham chiếu nằm trong bảng `article_images`, cột `image_path`. Các trường `caption`, `alt_text` và `sort_order` quản lý chú thích, mô tả và thứ tự ảnh.

**Không đưa ảnh vào cột BLOB và không cần chạy SQL mỗi lần đăng bài.** Sau khi upload qua trang quản trị, PHP tự ghi file và cập nhật database.

## 3. Giới hạn file trong ứng dụng

- Thumbnail: tối đa 5 MB.
- Mỗi ảnh gallery: tối đa 5 MB.
- Tổng ảnh trong một lần gửi: tối đa 18 MB.
- Tối đa 10 ảnh gallery trong một lần gửi.
- Chấp nhận JPG/JPEG, PNG, WEBP và GIF. Không nhận SVG, HTML hay file thực thi.
- Ảnh quá 40 megapixel bị từ chối để hạn chế ảnh có kích thước giải mã quá lớn.

Các giới hạn này thấp hơn giới hạn request PHP và giới hạn từng file mà InfinityFree công bố: request PHP tối đa 20 MB, còn file ảnh/tệp thông thường trên tài khoản tối đa 10 MB. Hạn mức trong ứng dụng là 5 MB mỗi ảnh và tổng 18 MB mỗi lần gửi. citeturn901942search0 Nếu thay hosting, kiểm tra lại `upload_max_filesize`, `post_max_size` và giới hạn kích thước file riêng của nhà cung cấp trước khi thay đổi hằng số trong `ArticleController.php`.

## 4. Database hiện tại

Schema hiện có đã đủ cho luồng thumbnail và thư viện ảnh:

| Bảng | Mục đích |
|---|---|
| `articles` | Lưu bài viết; `thumbnail` lưu tên file ảnh đại diện |
| `article_images` | Lưu danh sách ảnh trong bài; `image_path` lưu tên file |
| `categories` | Chuyên mục bài viết |
| `users` | Tài khoản quản trị/độc giả |
| `roles` | Vai trò `admin` và `user` |
| `comments` | Bình luận và trạng thái duyệt |

Hai bảng ảnh có khóa ngoại/index cần thiết trong `LOCAL_SETUP/01-schema-infinityfree.sql`. Cột ảnh là chuỗi tên file, còn file thật nằm trong thư mục upload.

### Nếu database production đã chạy

1. Export/backup database trước khi thay đổi.
2. Không import lại `01-schema-infinityfree.sql` hoặc `02-production-content-infinityfree.sql` vào database đang có dữ liệu.
3. Có thể chạy `LOCAL_SETUP/05-image-storage-audit.sql` để xem bài nào đang lưu tên ảnh trống, URL ngoài hoặc đường dẫn cũ. Script không ghi đè thumbnail đã chọn.
4. Source database trên website ở ảnh chụp trước đó đang có các tiêu đề mẫu đời đầu (APEC/VNPT). Nếu database hiện vẫn đúng trạng thái đó, hãy backup rồi chạy `03-editorial-refresh-content.sql`: script cập nhật nội dung và thumbnail cho đúng 8 bài mẫu theo filename ảnh thực tế. Script ghi đè nội dung của đúng 8 slug được liệt kê; không chạy nếu bạn đã tự biên tập các bài đó và muốn giữ bản của mình.
5. Chạy `05-image-storage-audit.sql` để tự bổ sung các thành phần schema ảnh còn thiếu (cột thumbnail, bảng gallery, index) và xem báo cáo thumbnail/gallery. Script này không thay đổi thumbnail, nội dung hay trạng thái bài viết.

### Nếu tạo database mới

1. Tạo một MySQL database ở Control Panel của hosting.
2. Import `LOCAL_SETUP/01-schema-infinityfree.sql`.
3. Tạo tài khoản Admin bằng hướng dẫn `generate-admin-sql.ps1` trong `README-INFINITYFREE.md`.
4. Import `LOCAL_SETUP/02-production-content-infinityfree.sql` một lần để tạo danh mục và 8 bài mẫu.
5. Import `03-editorial-refresh-content.sql` để đồng bộ 8 bài mẫu và thumbnail với bộ ảnh chụp thực tế trong `public/uploads/articles/`. Script cập nhật các dòng vừa được seed ở bước 4.
6. Chạy `05-image-storage-audit.sql` để bổ sung các index còn thiếu và kiểm tra tham chiếu ảnh.

## 5. Deploy lên InfinityFree

Project dùng GitHub Actions trong `.github/workflows/deploy-infinityfree.yml`. Workflow hiện deploy thư mục `upload/` vào `/htdocs/`; nó không tự import SQL và không được phép thay đổi database.

1. Mở source sạch này trong repository `Webtintuc` của bạn.
2. Giữ các GitHub Actions Secrets đã thiết lập: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, `NEWS_HUB_DB_HOST`, `NEWS_HUB_DB_NAME`, `NEWS_HUB_DB_USER`, `NEWS_HUB_DB_PASSWORD`; `NEWS_HUB_DB_PORT` là tùy chọn.
3. Kiểm tra `server-dir` trong workflow. Với domain phụ, thư mục có thể khác `/htdocs/`; phải dùng đúng website root đang được domain trỏ tới.
4. Chạy workflow dry-run trước. Sau đó mới chạy deploy thật.
5. Mở website và hard refresh bằng `Ctrl+F5`.
6. Dùng View Source/DevTools kiểm tra có meta:

   `<meta name="news-hub-build" content="20261120-image-pipeline-1">`

   Nếu không thấy build ID này, domain đang trỏ tới thư mục khác, workflow deploy sai đích hoặc source mới chưa được cập nhật.

7. Bộ ảnh minh họa `public/uploads/editorial/` đã được bỏ khỏi source. Nếu thư mục đó vẫn còn trên hosting, xóa thủ công sau khi xác nhận backup và kiểm tra website; không xóa `public/uploads/articles/` vì đây là nơi chứa ảnh mẫu và ảnh người quản trị đã upload.

Lưu ý: file ảnh mới do quản trị viên upload không được commit vào Git. File nằm trên hosting trong `htdocs/public/uploads/articles/` và database lưu tên file. Workflow deploy dùng `dangerous-clean-slate: false`, nhưng vẫn cần backup thư mục ảnh trước mỗi lần cập nhật; không xóa hoặc ghi đè thư mục này khi đồng bộ source. Ảnh do quản trị viên upload được giữ lại trên website sau deploy miễn là đúng website root và hosting không bị reset/xóa file.

## 6. Quy trình đăng bài mới và thay thumbnail

### Đăng bài mới

1. Đăng nhập bằng Admin.
2. Mở **Quản trị → Bài viết**.
3. Nhập tiêu đề, slug (có thể để trống), chuyên mục, tóm tắt và nội dung.
4. Ở **Ảnh đại diện**, chọn ảnh liên quan trực tiếp đến bài báo.
5. Chọn trạng thái `Published` khi sẵn sàng công khai, hoặc `Draft` để lưu bản nháp.
6. Nhấn **Đăng bài viết**.

Khi lưu thành công, trang quản trị chuyển sang màn hình sửa bài và hiện ảnh đã lưu.

### Thay ảnh cũ

1. Trong danh sách bài viết, chọn **Sửa**.
2. Chọn file mới tại **Ảnh đại diện**.
3. Nhấn **Cập nhật bài viết**.
4. Hệ thống đổi `articles.thumbnail` sang tên file mới. File cũ chỉ bị xóa nếu không bài viết hoặc gallery nào còn sử dụng nó.

Muốn gỡ thumbnail mà không thay ảnh, chọn **Gỡ ảnh đại diện hiện tại** rồi lưu.

### Ảnh trong nội dung bài

Chọn nhiều file tại **Thư viện ảnh đính kèm**. Ảnh được ghi vào `public/uploads/articles/` và tham chiếu trong `article_images`. Khi sửa bài, bạn có thể sắp xếp thứ tự hoặc xóa ảnh nội dung.

## 7. Nếu website vẫn hiện ảnh minh họa cũ

Trong source hiện tại, frontend không có logic thay ảnh bằng ảnh AI/vector. Mỗi thẻ ảnh được tạo từ tên file trong database và chỉ được chấp nhận nếu file tồn tại trong `public/uploads/articles/`.

Kiểm tra theo thứ tự:

- Build ID ở HTML có phải `20261120-image-pipeline-1` không.
- DevTools → Network → Img: `src` ảnh có nằm trong `/public/uploads/articles/` không.
- Nếu `src` vẫn chứa `/public/uploads/editorial/`, trang đang chạy layout/JavaScript cũ hoặc domain trỏ tới thư mục deploy khác.
- Nếu `src` là `/public/uploads/articles/<tên-file>` nhưng nội dung ảnh sai, database đang gắn sai `articles.thumbnail`; mở bài trong quản trị và tải đúng ảnh lên lại.
- Nếu ảnh bị mất, trang quản trị sẽ báo `Thiếu file`; tải ảnh mới để thay thế.

## 8. Kiểm tra source trước khi bàn giao

Chạy ở thư mục gốc repository:

```powershell
# Kiểm tra cú pháp PHP
Get-ChildItem .\upload -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

# Kiểm tra bộ phân giải ảnh
php .\tests\test_article_image.php

# Kiểm tra JavaScript
node --check .\upload\public\js\main.js
node --check .\upload\public\js\editorial-refresh.js

# Kiểm tra khoảng trắng trong diff (nếu source đang ở Git repository)
git diff --check
```

Các kiểm tra này xác nhận cú pháp và xử lý đường dẫn ảnh. Chúng không thay thế việc đăng nhập, upload thử và kiểm tra kết nối với database production trên chính hosting.

## 9. Bảo mật và sao lưu

- Không đưa `upload/config/database.local.php`, mật khẩu database, file `.env`, cookie, session hoặc file dump SQL lên GitHub.
- `upload/config/database.local.php` phải được tạo riêng từ `database.local.example.php` hoặc được workflow sinh từ GitHub Secrets.
- Sao lưu database và thư mục `public/uploads/articles/` riêng định kỳ. Bản backup database không bao gồm byte của file ảnh.
- Chỉ upload ảnh có quyền sử dụng và tránh ảnh không liên quan nội dung bài báo.
