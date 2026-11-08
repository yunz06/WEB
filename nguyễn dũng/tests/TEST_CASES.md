# TEST CASES — BÀI TẬP NHÓM LẬP TRÌNH WEB

**Phạm vi:** kiểm thử module Auth, Article, Category và Comment theo phần phân công.  
**Trạng thái ban đầu của các test nghiệp vụ:** `CHƯA CHẠY`. Không được sửa thành PASS trước khi chạy trên dự án tích hợp và lưu bằng chứng.

## 1. Auth — Đăng nhập, đăng xuất, phân quyền

| Mã | Điều kiện | Thao tác | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| AUTH-F01 | Có tài khoản User hợp lệ | Đăng nhập đúng thông tin | Đăng nhập thành công theo thiết kế nhóm | CHƯA CHẠY |
| AUTH-F02 | Có tài khoản Admin hợp lệ | Đăng nhập đúng thông tin | Đăng nhập thành công và có quyền vào Admin | CHƯA CHẠY |
| AUTH-F03 | Có tài khoản User | Truy cập Dashboard Admin | Bị từ chối truy cập | CHƯA CHẠY |
| AUTH-F04 | Chưa đăng nhập | Truy cập Dashboard Admin | Bị từ chối hoặc được chuyển về đăng nhập theo thiết kế | CHƯA CHẠY |
| AUTH-F05 | Đã đăng nhập | Đăng xuất, sau đó truy cập lại Admin | Phiên không còn quyền Admin | CHƯA CHẠY |
| AUTH-F06 | Có màn hình đăng nhập | Nhập mật khẩu sai | Không cho đăng nhập, không rò rỉ thông tin nhạy cảm | CHƯA CHẠY |

## 2. Article — CRUD, upload, editor, phân trang

| Mã | Điều kiện | Thao tác | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| ART-F01 | Admin đăng nhập | Tạo bài viết hợp lệ | Bài mới được lưu vào MySQL | CHƯA CHẠY |
| ART-F02 | Có bài viết | Mở trang danh sách và chi tiết | Hiện đúng dữ liệu đã lưu | CHƯA CHẠY |
| ART-F03 | Có bài viết | Sửa tiêu đề/nội dung rồi lưu | Dữ liệu mới được cập nhật | CHƯA CHẠY |
| ART-F04 | Có bài test | Xóa theo giao diện được thiết kế | Bài được xử lý xóa đúng quy tắc dự án | CHƯA CHẠY |
| ART-F05 | Có form bài viết | Upload một ảnh và nhiều ảnh hợp lệ | Ảnh được lưu và hiển thị đúng | CHƯA CHẠY |
| ART-F06 | Có trình soạn thảo | Tạo bài có nội dung Rich Text | Nội dung được lưu và hiển thị an toàn | CHƯA CHẠY |
| ART-F07 | Có trạng thái Draft/Published | Chuyển Draft sang Published | Chỉ bài Published xuất hiện bên User theo yêu cầu | CHƯA CHẠY |
| ART-F08 | Có đủ nhiều bài | Sang trang 2 và quay lại trang 1 | Phân trang ổn định, không mất/nhân bản bài | CHƯA CHẠY |
| ART-F09 | User thường hoặc Guest | Truy cập hành động sửa/xóa bài của Admin | Không được thực hiện | CHƯA CHẠY |

## 3. Category / User — Danh mục, quản lý người dùng

| Mã | Điều kiện | Thao tác | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| CAT-F01 | Admin đăng nhập | Tạo danh mục hợp lệ | Danh mục xuất hiện trong MySQL | CHƯA CHẠY |
| CAT-F02 | Có danh mục | Xem/sửa/xóa danh mục theo giao diện | CRUD đúng quy định dự án | CHƯA CHẠY |
| CAT-F03 | Có bài liên kết danh mục | Mở danh sách theo danh mục | Hiện đúng bài liên kết | CHƯA CHẠY |
| CAT-F04 | Có tài khoản User | Admin khóa tài khoản | Tài khoản bị khóa không thể đăng nhập theo thiết kế | CHƯA CHẠY |
| CAT-F05 | Có tài khoản bị khóa | Admin mở khóa | Tài khoản hoạt động lại theo thiết kế | CHƯA CHẠY |
| CAT-F06 | Có danh sách nhiều người dùng | Sử dụng tìm kiếm và phân trang | Kết quả và số trang đúng | CHƯA CHẠY |

## 4. Comment — Bình luận, kiểm duyệt

| Mã | Điều kiện | Thao tác | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| COM-F01 | Guest chưa đăng nhập | Gửi bình luận | Hệ thống từ chối | CHƯA CHẠY |
| COM-F02 | User đăng nhập | Gửi bình luận hợp lệ | Bình luận được lưu theo trạng thái thiết kế | CHƯA CHẠY |
| COM-F03 | Có bình luận chưa duyệt | Admin duyệt | Bình luận đã duyệt hiển thị bên User | CHƯA CHẠY |
| COM-F04 | Có bình luận | Admin ẩn/xóa | Bình luận không còn hiển thị theo quy tắc | CHƯA CHẠY |
| COM-F05 | Có bài viết với nhiều bình luận | Xem danh sách/phân trang | Chỉ các bình luận được phép hiển thị | CHƯA CHẠY |
| COM-F06 | User đăng nhập | Gửi nội dung rỗng hoặc không hợp lệ | Hệ thống kiểm tra dữ liệu, không ghi sai | CHƯA CHẠY |

## 5. Dashboard (phần trực tiếp phụ trách)

| Mã | Điều kiện | Thao tác | Kết quả mong đợi | Trạng thái |
|---|---|---|---|---|
| DASH-F01 | Có database thật | Vào Dashboard | Hiển thị số bài, Published, users, categories, comments đúng SQL | CHƯA CHẠY |
| DASH-F02 | Có danh sách bài | Vào Dashboard | Hiện tối đa 5 bài mới nhất, theo cột sắp xếp đã đối chiếu | CHƯA CHẠY |
| DASH-F03 | Không có quyền Admin | Truy cập URL Dashboard | Bị chặn bởi cơ chế Auth/Router của nhóm | CHƯA CHẠY |
| DASH-F04 | Giao diện desktop và mobile | Thay đổi kích thước cửa sổ | Giao diện dễ đọc, không tràn bố cục | CHƯA CHẠY |
| DASH-F05 | Thiếu cấu hình cột | Mở Dashboard | Hiển thị “Chưa xác minh”, không tự hiện số 0 | CHƯA CHẠY |
| DASH-F06 | Có dữ liệu HTML độc hại trong title | Hiển thị bài mới | Giá trị được escape, không chạy script | Test view độc lập: PASS; tích hợp: CHƯA CHẠY |

## Cách ghi nhận kết quả

1. Ghi ngày giờ, môi trường (PHP, MySQL, URL local, commit), người kiểm thử.
2. Với mỗi test, ghi `PASS`, `FAIL` hoặc `BLOCKED/CHƯA CHẠY`, kèm ảnh hoặc log.
3. Nếu FAIL, tạo dòng trong `BUG_LIST.md` với bước tái hiện, kỳ vọng, thực tế và mức độ ảnh hưởng.
4. Không thực hiện kiểm thử CREATE/UPDATE/DELETE trên dữ liệu người dùng thật. Dùng DB test và bản ghi test.
5. Chạy `php tests/run.php` cho các kiểm tra SQL **chỉ đọc**; không nhầm các kiểm tra này với kiểm thử giao diện/nghiệp vụ trên.
