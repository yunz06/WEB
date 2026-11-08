# HƯỚNG DẪN CHẠY DASHBOARD DEMO CỤC BỘ TRÊN WINDOWS

**Đây là demo độc lập PHP + MySQL do tôi tạo để kiểm tra phần Dashboard, không phải source code/schema/README đã được nhóm xác nhận.** Đừng ghép SQL demo vào database nhóm.

## 1. Chuẩn bị

- Windows 11, có Docker Desktop đang chạy và hỗ trợ `docker compose`.
- Có mạng lúc tải các image Docker lần đầu.
- Giải nén ZIP này vào một thư mục riêng, không đặt đè lên dự án GitHub nhóm.
- Đối với repo nhóm hiện tại, phải dùng schema và Auth của nhóm, không được dùng cơ chế Basic Auth demo thay thế.

## 2. Chạy trên máy bạn (Windows PowerShell)

Mở PowerShell trong thư mục chứa `compose.yaml`:

```powershell
Copy-Item .env.example .env
notepad .env
```

Đổi ba giá trị mật khẩu trong `.env` thành mật khẩu riêng (không đăng file này lên Git). `DEMO_ADMIN_USER` có thể giữ hoặc đổi tuỳ ý.

```powershell
docker compose up --build -d
docker compose ps
```

Sau khi 2 service `db`, `web` hoạt động, mở trình duyệt:

`http://localhost:8080/dashboard.php`

Trình duyệt hỏi thông tin đăng nhập Basic Auth. Nhập `DEMO_ADMIN_USER` và `DEMO_ADMIN_PASSWORD` từ `.env`. **Đây là xác thực độc lập để bảo vệ bản demo cục bộ**, không phải hệ thống đăng nhập của nhóm. Nếu nhập sai, trang phải trả HTTP 401.

Trong giao diện phải có các thống kê **từ MySQL demo**: 6 bài viết, 5 bài Published, 3 users, 3 categories, 4 comments và danh sách tối đa 5 bài mới nhất. Dữ liệu này lấy từ các file SQL mẫu tự tạo, không phải từ nhóm.

## 3. Chạy kiểm thử

```powershell
# 36 test logic PHP, controller, giao diện, mô phỏng PDO
docker compose exec -T web php tests/dashboard_unit_test.php

# 5 test giao diện độc lập
docker compose exec -T web php tests/view_test.php

# Truy vấn thật tới MySQL demo (bản DB demo mới tinh)
docker compose exec -T web php tests/demo_database_test.php

# Test đọc schema của 4 module — các tính năng CRUD thực tế
# vẫn BLOCKED vì chúng thuộc source code chưa được nhóm gửi
docker compose exec -T web php tests/run.php
```

Nếu sử dụng lại Docker volume đã chỉnh sửa dữ liệu thì `demo_database_test.php` có thể thất bại vì bộ test này kỳ vọng đúng số lượng seed ban đầu. Không tự reset dữ liệu trước khi biết đó là dữ liệu mẫu của bạn.

## 4. Dừng môi trường

```powershell
docker compose down
```

Không dùng `docker compose down -v` nếu muốn giữ lại database demo vì `-v` xoá volume chứa dữ liệu thử nghiệm.

## 5. Phân biệt 3 mức xác minh

1. **Unit test PHP tại môi trường tạo file:** đã chạy `dashboard_unit_test.php` và `view_test.php`, không cần MySQL.
2. **MySQL demo thực tế trên máy bạn:** chỉ xác nhận sau khi thực hiện các câu lệnh Docker ở trên và xem log PASS. Hệ thống tạo file không có Docker/MySQL nên chưa chạy được bước này tại đây.
3. **Tích hợp dự án nhóm:** cần ZIP/repo thực tế, `database/news_hub.sql`, `README.md`, Router/Auth/Admin Layout; chưa thể xác minh chỉ từ bảng phân công.

## 6. Nếu lỗi

- `docker` không nhận: cài và bật Docker Desktop, sau đó mở lại PowerShell.
- `port is already allocated`: cổng 8080 trên máy đang bị sử dụng; đổi `127.0.0.1:8080:80` trong `compose.yaml` thành `127.0.0.1:18080:80`, rồi truy cập `http://localhost:18080/dashboard.php`.
- `Access denied`: kiểm tra đã sao chép `.env`, đặt các mật khẩu, và `docker compose ps` báo DB healthy. Lưu ý đổi mật khẩu sau khi đã tạo volume MySQL **không tự thay đổi mật khẩu trong DB cũ**.
- `503` và màn hình "Không thể tải Dashboard": chạy `docker compose logs web db` để xem lỗi thực, nhưng đừng gửi công khai file `.env` hay log chứa mật khẩu.
- Không tự thêm dữ liệu mẫu vào database thật của dự án nhóm.


## 7. Đã nhận nội dung README nhóm

README xác nhận PHP 8.x, MySQL 8.x, Bootstrap 5, MVC và PDO; hướng dẫn import `database/news_hub.sql` và cấu hình `config/database.php`. Tuy nhiên **chưa nhận được chính các tệp này** nên lệnh Docker ở trên chỉ chạy bộ demo độc lập. Để kiểm thử repo thật, cần source ZIP hoặc URL repository cùng commit/hash (nếu repo riêng tư thì ZIP).
