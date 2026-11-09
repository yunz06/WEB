
# NEWS HUB

Website đọc và quản lý tin tức trực tuyến.

## 1. Giới thiệu

NEWS HUB là website tin tức được xây dựng trong khuôn khổ bài tập nhóm cuối kỳ môn Lập trình Web.

Hệ thống gồm hai khu vực chính:

- User: Xem tin tức, tìm kiếm, xem danh mục và bình luận.
- Admin: Quản lý bài viết, danh mục, người dùng và bình luận.

## 2. Công nghệ sử dụng

- Backend: PHP 8.x
- Database: MySQL 8.x
- Frontend: HTML5, CSS3, JavaScript
- Framework CSS: Bootstrap 5
- Database Connection: PDO
- Version Control: Git và GitHub

## 3. Cấu trúc thư mục

```text
Webtintuc/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── config/
├── database/
├── public/
│   ├── css/
│   ├── js/
│   └── uploads/
├── index.php
├── .htaccess
├── .gitignore
└── README.md
```

## 4. Chức năng chính

### User
- Xem trang chủ.
- Xem danh sách tin tức.
- Xem tin tức theo danh mục.
- Xem chi tiết bài viết.
- Tìm kiếm bài viết.
- Đăng ký, đăng nhập, đăng xuất.
- Gửi và xem bình luận.

### Admin
- Quản lý bài viết (CRUD).
- Quản lý danh mục (CRUD).
- Quản lý người dùng.
- Quản lý bình luận.
- Upload nhiều hình ảnh.
- Sử dụng trình soạn thảo nội dung.
- Phân trang danh sách.

## 5. Database

Database sử dụng MySQL.

Các bảng chính:

1. roles
2. users
3. categories
4. articles
5. article_images
6. comments

File SQL: `database/news_hub.sql`

## 6. Hướng dẫn cài đặt

### Bước 1: Clone project

```bash
git clone <GITHUB_REPOSITORY_URL>
```

### Bước 2: Đưa project vào thư mục máy chủ

Nếu sử dụng XAMPP, đặt project trong:

```text
C:/xampp/htdocs/Webtintuc
```

### Bước 3: Tạo Database

- Mở phpMyAdmin.
- Tạo database tên `news_hub`.
- Import file `database/news_hub.sql`.

### Bước 4: Cấu hình kết nối Database

Mở file:

```text
config/database.php
```

Cập nhật thông tin MySQL phù hợp với máy của bạn.

### Bước 5: Chạy website

Khởi động Apache và MySQL trong XAMPP.

Truy cập:

```text
http://localhost/Webtintuc/
```

## 7. Thành viên nhóm

- TV1: Frontend & Core Developer.
- TV2: Article Management.
- TV3: Category & User Management.
- TV4: Comment Management.
- TV5: Dashboard & Testing.

## 8. Lưu ý

- Không tự ý thay đổi cấu trúc Database.
- Không tự tạo kết nối Database riêng.
- Sử dụng chung quy ước code của nhóm.
- Không commit thông tin mật khẩu hoặc file cấu hình cá nhân lên GitHub.

---

NEWS HUB - The World, In Focus.
