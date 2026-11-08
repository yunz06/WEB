-- DỮ LIỆU DEMO TỰ TẠO DÙNG THỬ DASHBOARD; KHÔNG PHẢI DỮ LIỆU THẬT.
-- Mật khẩu tài khoản users không được dùng cho đăng nhập; Basic Auth demo là độc lập.
INSERT INTO users (username, password_hash, role) VALUES
('demo_admin', 'not_a_valid_login_hash', 'admin'),
('demo_user_1', 'not_a_valid_login_hash', 'user'),
('demo_user_2', 'not_a_valid_login_hash', 'user');
INSERT INTO categories (name) VALUES ('Thời sự'), ('Công nghệ'), ('Học tập');
INSERT INTO articles (category_id, title, status, created_at) VALUES
(1, 'Tin tức kiểm thử 01', 'published', '2026-11-01 09:00:00'),
(2, 'Công nghệ kiểm thử 02', 'published', '2026-11-02 09:00:00'),
(3, 'Bài nháp kiểm thử 03', 'draft', '2026-11-03 09:00:00'),
(1, 'Tin tức kiểm thử 04', 'published', '2026-11-04 09:00:00'),
(2, 'Công nghệ kiểm thử 05', 'published', '2026-11-05 09:00:00'),
(3, 'Bài mới kiểm thử 06', 'published', '2026-11-06 09:00:00');
INSERT INTO comments (article_id, user_id, content, status) VALUES
(1, 2, 'Bình luận mẫu A', 'approved'),
(2, 3, 'Bình luận mẫu B', 'pending'),
(4, 2, 'Bình luận mẫu C', 'approved'),
(6, 3, 'Bình luận mẫu D', 'approved');
