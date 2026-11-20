# NEWS HUB — cập nhật hình ảnh biên tập

Source hiện tại dùng một cơ chế thống nhất: cột `articles.thumbnail` chứa tên file trong `public/uploads/articles/`. Các ảnh nội dung được tham chiếu từ `article_images.image_path`. Frontend không dùng bộ ảnh minh họa cũ trong `public/uploads/editorial/` và không thay ảnh bằng JavaScript.

Hướng dẫn triển khai database, upload ảnh, thay ảnh và xử lý website production nằm trong [`README-FINAL-SETUP.md`](README-FINAL-SETUP.md).
