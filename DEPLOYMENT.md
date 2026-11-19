# NEWS HUB — triển khai Internet

## Chọn nền tảng

NEWS HUB là PHP MVC + Apache + PDO/MySQL. Không thể upload trực tiếp project này lên Netlify như static site vì Netlify không chạy PHP/Apache thường trực. Nếu muốn giữ nguyên code, dùng nền tảng hỗ trợ PHP qua Docker hoặc PHP hosting có MySQL/MariaDB.

Cấu hình mẫu đi kèm repo dành cho Render Docker Web Service:
- `Dockerfile`: PHP 8.2 + Apache + `pdo_mysql`, `mbstring`, `dom` và rewrite.
- `render.yaml`: web service, health check `/health.php`, biến môi trường database và persistent disk cho ảnh bài viết.
- `.env.example`: tên biến cấu hình, không chứa mật khẩu thật.

Render chạy PHP trong Docker; database MySQL cần được host riêng hoặc vận hành như custom service có persistent storage. Blueprint hiện dùng plan `starter` cùng disk 1 GB làm cấu hình ví dụ có thể phát sinh chi phí. Kiểm tra giá và plan trong dashboard trước khi tạo service.

## Database trên Internet

Không dùng `127.0.0.1` hoặc XAMPP đang chạy trên laptop làm database production. Trên server, `127.0.0.1` là chính container/server đó, không phải máy cá nhân của bạn. Tạo MySQL/MariaDB database có thể truy cập riêng từ PHP service, import schema `database/news_hub.sql`, rồi seed chỉ khi đó là database production mới hoàn toàn. Với database đang có dữ liệu, hãy dùng quy trình migration/backup đã kiểm tra và không import seed lại.

Cấu hình các biến môi trường tại hosting:

- `NEWS_HUB_DB_HOST`
- `NEWS_HUB_DB_PORT`
- `NEWS_HUB_DB_NAME`
- `NEWS_HUB_DB_USER`
- `NEWS_HUB_DB_PASS`
- `NEWS_HUB_DB_CHARSET=utf8mb4`

Không commit secrets hoặc file dump. Không mở cổng MariaDB trên laptop ra Internet.

## Dữ liệu upload

Ảnh Admin upload phải nằm trên storage bền vững. Render filesystem mặc định là ephemeral; Blueprint gắn persistent disk cho `public/uploads/articles`. Disk-backed services có thể gây downtime ngắn khi deploy và không phù hợp scale nhiều instance. Nếu cần scale, chuyển upload sang object storage phù hợp rồi cấu hình đường dẫn an toàn.

## Dữ liệu hoạt động như thế nào

Local: Trình duyệt → Apache/XAMPP → PHP Router/Controller → Model/PDO → MariaDB `news_hub` trên máy bạn. Tạo tài khoản ghi vào `users`; Admin tạo/sửa/xóa bài ghi vào `articles`; ảnh liên quan lưu metadata trong `article_images` và file trong thư mục upload; bình luận ghi vào `comments`.

Production: Trình duyệt → HTTPS → PHP service trên hosting → PDO → database production từ xa. Mọi khách truy cập website công khai dùng chung database production. Database XAMPP trên laptop không tự đồng bộ với database production; muốn chuyển dữ liệu cần backup/export và import có kiểm soát.

## Quy trình triển khai

1. Đưa source sạch lên Git repository riêng tư. Không đưa `.env`, dump SQL, cookie, password hoặc file log vào Git.
2. Tạo database MySQL/MariaDB production và backup dữ liệu cần chuyển.
3. Import schema vào database production mới; import seed chỉ khi database hoàn toàn mới và bạn muốn dữ liệu demo.
4. Tạo Render Web Service từ repository, chọn Dockerfile.
5. Đặt environment variables database trong Render Dashboard.
6. Xác nhận persistent disk cho upload và health check `/health.php`.
7. Dùng công cụ quản trị an toàn hoặc tài khoản Admin đã chuẩn bị để vào quản trị. Không dùng mật khẩu demo trên Internet.
8. Test đăng ký, đăng nhập, phân quyền, CRUD, upload, editor, bình luận, backup và restore trước khi chia sẻ URL.

**Chưa có dịch vụ nào được deploy bởi repository này.** Cần tài khoản hosting, database production, secrets và thao tác xác nhận của chủ dự án.
