-- NEWS HUB editorial content refresh. Original Vietnamese summaries based on cited public sources.
-- DIALECT: MariaDB 10.4 / MySQL. Do not run this file through a Microsoft SQL Server (MSSQL) connection.
-- Run in phpMyAdmin after selecting the MariaDB server, or with the MariaDB mysql client.
-- Safe for the existing sample slugs only; does not insert, delete, or alter schema.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `news_hub`;
START TRANSACTION;

UPDATE articles SET is_featured=0 WHERE slug LIKE 'sample-%';

UPDATE articles SET
 title='Việt Nam tiếp nhận vai trò chủ nhà APEC 2027 tại Hội nghị cấp cao ở Thâm Quyến',
 summary='Tại Hội nghị các nhà lãnh đạo kinh tế APEC lần thứ 33 ngày 19/11/2026, Việt Nam tiếp nhận vai trò chủ nhà APEC 2027. Đây là bước mở đầu cho công tác chuẩn bị chương trình hợp tác khu vực trong năm tới.',
 content='<p>Việt Nam tiếp nhận vai trò chủ nhà Diễn đàn Hợp tác Kinh tế châu Á – Thái Bình Dương (APEC) năm 2027 tại Hội nghị các nhà lãnh đạo kinh tế APEC lần thứ 33 diễn ra ở Thâm Quyến, Trung Quốc, ngày 19/11/2026.</p><p>APEC là diễn đàn kinh tế khu vực quy tụ các nền kinh tế ven Thái Bình Dương. Việc đảm nhiệm vai trò chủ nhà đặt ra yêu cầu điều phối chương trình nghị sự, chuẩn bị các cuộc họp cấp chuyên viên, bộ trưởng và lãnh đạo, đồng thời tạo điều kiện để doanh nghiệp và các tổ chức trong khu vực trao đổi về thương mại, đầu tư và hợp tác.</p><p>Với Việt Nam, công tác chuẩn bị không chỉ nằm ở khâu tổ chức sự kiện. Đây còn là cơ hội thúc đẩy đối thoại về chuỗi cung ứng, chuyển đổi số, đổi mới sáng tạo và tăng trưởng bền vững. Hiệu quả của năm APEC sẽ phụ thuộc vào việc chuyển các cuộc thảo luận thành sáng kiến có thể triển khai và mang lại lợi ích thiết thực cho các nền kinh tế thành viên.</p><p>Thông tin trong bài được NEWS HUB biên tập, tóm lược từ nguồn tham khảo; độc giả nên xem bản tin gốc để nắm đầy đủ phát biểu và bối cảnh ngoại giao.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://www.vietnamplus.vn/tong-bi-thu-chu-tich-nuoc-to-lam-tham-du-hoi-nghi-cac-nha-lanh-dao-kinh-te-apec-lan-thu-33-post1149358.amp">VietnamPlus / Thông tấn xã Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_world.jpg', status='published', is_featured=1,
 published_at='2026-11-19 14:00:00'
 WHERE slug='sample-01-thoi-su';

UPDATE articles SET
 title='Doanh nghiệp APEC được khuyến khích mở rộng hợp tác dài hạn với Việt Nam',
 summary='Tại các hoạt động bên lề APEC 2026 ở Thâm Quyến, thông điệp được nhấn mạnh là mở rộng hợp tác trong những lĩnh vực chiến lược, chia sẻ tri thức và tăng cường năng lực doanh nghiệp.',
 content='<p>Trong khuôn khổ tuần lễ cấp cao APEC 2026 tại Thâm Quyến, lãnh đạo Việt Nam đã có các hoạt động tiếp xúc với đại diện cộng đồng doanh nghiệp và nhà đầu tư trong khu vực. Thông điệp được nêu là khuyến khích hợp tác trong các lĩnh vực chiến lược, chia sẻ tri thức và công nghệ, củng cố năng lực doanh nghiệp và xây dựng quan hệ đối tác dài hạn.</p><p>Đối thoại giữa khu vực công và doanh nghiệp là một phần quan trọng của hợp tác kinh tế khu vực. Doanh nghiệp cần môi trường đầu tư có thể dự đoán, quy định minh bạch và cơ hội kết nối với đối tác; ngược lại, cơ quan quản lý cần phản hồi thực tế từ thị trường để hoàn thiện chính sách.</p><p>Việc Việt Nam chuẩn bị đăng cai APEC 2027 tạo thêm cơ hội duy trì trao đổi với cộng đồng doanh nghiệp quốc tế. Tuy vậy, hiệu quả hợp tác sẽ được đánh giá qua các dự án, liên kết chuỗi cung ứng và kết quả cụ thể sau các cuộc gặp.</p><p>NEWS HUB tóm lược nội dung từ bản tin của Thông tấn xã Việt Nam. Bài viết không thay thế thông cáo chính thức hoặc phát biểu đầy đủ của các bên.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://en.vietnamplus.vn/morning-digest-on-november-19-post355727.vnp">VietnamPlus / Thông tấn xã Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_world.jpg', status='published', is_featured=1,
 published_at='2026-11-19 13:00:00'
 WHERE slug='sample-05-the-gioi';

UPDATE articles SET
 title='Kinh tế Việt Nam tăng 9,01% trong chín tháng đầu năm 2026',
 summary='Cơ quan Thống kê Quốc gia ước tính GDP chín tháng năm 2026 tăng 9,01% so với cùng kỳ. Công nghiệp và xây dựng tiếp tục là nhóm đóng góp lớn vào tăng trưởng.',
 content='<p>Theo báo cáo tình hình kinh tế – xã hội quý III và chín tháng năm 2026 của Cục Thống kê, tổng sản phẩm trong nước (GDP) chín tháng ước tăng 9,01% so với cùng kỳ năm trước. Riêng quý III, mức tăng được ước tính là 9,95%.</p><p>Xét theo khu vực kinh tế, công nghiệp và xây dựng tăng 11,21% trong chín tháng, dịch vụ tăng 8,69%, còn nông, lâm nghiệp và thủy sản tăng 4,02%. Cơ cấu này cho thấy hoạt động sản xuất, xây dựng và dịch vụ tiếp tục giữ vai trò đáng kể trong tăng trưởng.</p><p>Các số liệu thống kê cần được đọc cùng phương pháp tính, kỳ so sánh và những điều chỉnh có thể xuất hiện trong các lần công bố tiếp theo. Tốc độ tăng GDP là chỉ báo tổng hợp, không phản ánh đồng đều tình hình của mọi ngành nghề hoặc từng hộ gia đình.</p><p>NEWS HUB biên tập lại các số liệu công bố nhằm giúp độc giả tiếp cận nhanh hơn. Khi sử dụng cho nghiên cứu hoặc quyết định kinh doanh, hãy đối chiếu báo cáo gốc của Cục Thống kê.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://www.nso.gov.vn/en/data-and-statistics/2026/11/report-socio-economic-performance-in-the-third-quarter-and-the-nine-months-of-2026/">Cục Thống kê Quốc gia Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_business.jpg', status='published', is_featured=1,
 published_at='2026-11-18 09:00:00'
 WHERE slug='sample-07-tai-chinh';

UPDATE articles SET
 title='Bản đồ doanh nghiệp công nghệ số Việt Nam 2026 bao phủ 11 lĩnh vực',
 summary='Bản đồ do VINASA giới thiệu ngày 18/11/2026 cung cấp dữ liệu tra cứu doanh nghiệp công nghệ số theo 11 lĩnh vực, với 108 vị trí doanh nghiệp được giới thiệu.',
 content='<p>Hiệp hội Phần mềm và Dịch vụ Công nghệ thông tin Việt Nam (VINASA) giới thiệu Bản đồ doanh nghiệp công nghệ số Việt Nam 2026 tại Hà Nội ngày 18/11. Theo thông tin được công bố, bản đồ bao phủ 11 lĩnh vực và có 108 vị trí doanh nghiệp.</p><p>Chương trình Top 10 doanh nghiệp công nghệ số năm 2026 đồng thời trao 51 danh hiệu cho 33 doanh nghiệp trong 11 lĩnh vực. Các tiêu chí đánh giá được giới thiệu tập trung vào tầm nhìn và năng lực thực thi, với phạm vi gồm lĩnh vực công nghệ và lĩnh vực ứng dụng.</p><p>Điểm đáng chú ý của phiên bản năm nay là hồ sơ doanh nghiệp được tiếp nhận, xác minh và cập nhật quanh năm thay vì chỉ cập nhật theo chu kỳ hằng năm. Cách làm này có thể giúp khách hàng, đối tác và nhà đầu tư tra cứu thông tin thường xuyên hơn; chất lượng dữ liệu vẫn phụ thuộc vào quy trình xác minh và mức độ cập nhật của từng hồ sơ.</p><p>Bài viết được NEWS HUB tóm lược từ VietnamPlus. Bản đồ và danh sách doanh nghiệp là nguồn tham khảo; người đọc nên kiểm tra thông tin trực tiếp trước khi đưa ra quyết định hợp tác.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://en.vietnamplus.vn/vietnam-launches-2026-digital-technology-enterprise-map-covering-11-sectors-post355732.vnp">VietnamPlus / Thông tấn xã Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_technology.jpg', status='published', is_featured=1,
 published_at='2026-11-19 11:00:00'
 WHERE slug='sample-09-cong-nghe';

UPDATE articles SET
 title='Năm học 2026–2027: Gần 27 triệu học sinh, sinh viên bước vào năm học mới',
 summary='Báo cáo của Cục Thống kê ghi nhận gần 27 triệu học sinh, sinh viên trên cả nước trong năm học 2026–2027; mạng lưới giáo dục phổ thông có hơn 25 nghìn trường.',
 content='<p>Theo báo cáo kinh tế – xã hội chín tháng năm 2026 của Cục Thống kê, năm học 2026–2027 có gần 27 triệu học sinh và sinh viên trên cả nước bắt đầu năm học mới. Mạng lưới giáo dục phổ thông được thống kê gồm 25.365 trường, với khoảng 18,75 triệu học sinh và 843.966 giáo viên đứng lớp.</p><p>Những con số này cho thấy quy mô lớn của hệ thống giáo dục và nhu cầu bảo đảm điều kiện học tập trên nhiều địa bàn. Bên cạnh số lượng trường lớp, chất lượng giáo viên, cơ sở vật chất, khả năng tiếp cận và mức độ phù hợp của chương trình vẫn là những yếu tố cần được theo dõi.</p><p>Thống kê toàn quốc không phản ánh đầy đủ chênh lệch giữa các vùng hoặc từng cấp học. Khi đánh giá chất lượng giáo dục, cần kết hợp dữ liệu về kết quả học tập, điều kiện trường lớp, đội ngũ giáo viên và cơ hội tiếp cận của nhóm học sinh dễ bị bỏ lại phía sau.</p><p>NEWS HUB tóm lược thông tin từ báo cáo chính thức của Cục Thống kê; các số liệu có thể được cập nhật trong những kỳ báo cáo tiếp theo.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://www.nso.gov.vn/en/data-and-statistics/2026/11/press-release-socio-economic-performance-in-the-q3-and-the-nine-months-of-2026/">Cục Thống kê Quốc gia Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_education.jpg', status='published', is_featured=0,
 published_at='2026-11-17 08:30:00'
 WHERE slug='sample-21-giao-duc';

UPDATE articles SET
 title='Việt Nam đón 19,5 triệu lượt khách quốc tế trong 10 tháng năm 2026',
 summary='Cục Thống kê ước tính lượng khách quốc tế đến Việt Nam trong 10 tháng năm 2026 đạt 19,5 triệu lượt, tăng 13,8% so với cùng kỳ.',
 content='<p>Trong báo cáo tình hình kinh tế – xã hội tháng 10 và 10 tháng năm 2026, Cục Thống kê ước tính Việt Nam đón 19,5 triệu lượt khách quốc tế trong mười tháng, tăng 13,8% so với cùng kỳ năm trước.</p><p>Riêng tháng 10, lượng khách quốc tế đạt khoảng 1,85 triệu lượt, tăng 4,9% so với tháng trước và tăng 7% so với cùng kỳ. Báo cáo cho rằng kết quả có liên quan đến môi trường an ninh, an toàn, chính sách thị thực thuận lợi, hoạt động quảng bá và sự đa dạng của sản phẩm du lịch.</p><p>Lượng khách tăng tạo thêm nhu cầu đối với lưu trú, vận tải, ăn uống và các dịch vụ trải nghiệm. Để chuyển tăng trưởng về số lượt khách thành giá trị bền vững, điểm đến cần quan tâm chất lượng dịch vụ, bảo tồn tài nguyên, hạ tầng công cộng và phân bổ lợi ích tới cộng đồng địa phương.</p><p>NEWS HUB biên tập lại số liệu từ báo cáo của Cục Thống kê. Lượt khách là chỉ tiêu về lượng, không đồng nghĩa trực tiếp với doanh thu hoặc mức chi tiêu bình quân.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://www.nso.gov.vn/en/data-and-statistics/2026/11/report-socio-economic-performance-in-october-and-10-months-of-2026/">Cục Thống kê Quốc gia Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_lifestyle.jpg', status='published', is_featured=0,
 published_at='2026-11-04 12:00:00'
 WHERE slug='sample-33-doi-song';

UPDATE articles SET
 title='Định hướng đô thị thông minh 2026–2030 đặt trọng tâm vào dữ liệu dùng chung',
 summary='Định hướng quốc gia về phát triển đô thị thông minh yêu cầu các thành phố xây dựng cơ sở dữ liệu đô thị, ưu tiên chuẩn hóa dữ liệu nền và hệ thống thông tin địa lý.',
 content='<p>Ngày 6/11/2026, Chính phủ công bố định hướng phát triển đô thị thông minh giai đoạn 2026–2030, tầm nhìn đến năm 2035. Theo nội dung được công bố, các thành phố sẽ xây dựng và triển khai đề án phát triển đô thị thông minh; các địa phương xây dựng lộ trình phù hợp với nhu cầu và điều kiện thực tế.</p><p>Một trọng tâm của định hướng là hình thành cơ sở dữ liệu đô thị dùng chung. Các địa phương được yêu cầu ưu tiên chuẩn hóa và làm sạch dữ liệu nền về địa lý, đất đai, dân cư, quy hoạch và hạ tầng trước khi kết nối, chia sẻ theo quy định.</p><p>Hệ thống dữ liệu dựa trên thông tin địa lý (GIS) về quy hoạch, hạ tầng và không gian đô thị cũng được xác định là thành phần cần xây dựng, cập nhật và kết nối theo kế hoạch. Dữ liệu tốt có thể hỗ trợ quy hoạch và cung cấp dịch vụ công, nhưng hiệu quả phụ thuộc vào chất lượng cập nhật, khả năng liên thông và cơ chế bảo vệ dữ liệu.</p><p>NEWS HUB tóm lược văn bản và thông tin công bố từ Cổng Thông tin điện tử Chính phủ. Khi áp dụng vào từng địa phương, độc giả cần xem quyết định và kế hoạch triển khai chính thức.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://en.baochinhphu.vn/govt-approves-national-smart-urban-development-orientation-for-20262030-towards-2035-111261106102511205.htm">Cổng Thông tin điện tử Chính phủ</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_world.jpg', status='published', is_featured=0,
 published_at='2026-11-06 12:58:00'
 WHERE slug='sample-03-moi-truong';

UPDATE articles SET
 title='Thể thao Việt Nam tại ASIAD 20: Báo cáo thống kê ghi nhận 22 huy chương đầu tháng 10',
 summary='Báo cáo của Cục Thống kê ghi nhận đoàn thể thao Việt Nam có 22 huy chương tại ASIAD 20 tính đến 8 giờ ngày 2/10/2026, trong đó có hai huy chương vàng.',
 content='<p>Báo cáo kinh tế – xã hội chín tháng năm 2026 của Cục Thống kê ghi nhận đoàn thể thao Việt Nam tham dự Đại hội Thể thao châu Á lần thứ 20 tại Nhật Bản với 498 thành viên, trong đó có 348 vận động viên.</p><p>Theo số liệu được nêu trong báo cáo, tính đến 8 giờ ngày 2/10/2026, đoàn Việt Nam giành 22 huy chương, gồm hai huy chương vàng ở nội dung kata đồng đội nữ môn karate và đôi nữ môn sepak takraw, một huy chương bạc wushu và 19 huy chương đồng.</p><p>Đây là mốc thống kê tại thời điểm báo cáo, không phải bảng tổng kết cuối cùng của đại hội. Thành tích thể thao cần được đặt trong bối cảnh số môn tham dự, chất lượng đối thủ, quá trình chuẩn bị và mục tiêu phát triển dài hạn của từng môn.</p><p>NEWS HUB tóm lược số liệu từ báo cáo chính thức của Cục Thống kê. Độc giả nên đối chiếu kết quả cuối cùng với ban tổ chức đại hội hoặc cơ quan thể thao có thẩm quyền.</p><p><strong>Nguồn tham khảo:</strong> <a href="https://www.nso.gov.vn/en/data-and-statistics/2026/11/report-socio-economic-performance-in-the-third-quarter-and-the-nine-months-of-2026/">Cục Thống kê Quốc gia Việt Nam</a>. Nội dung được NEWS HUB biên tập và tóm lược, không sao chép nguyên văn.</p>',
 thumbnail='seed_sports.jpg', status='published', is_featured=0,
 published_at='2026-10-03 08:00:00'
 WHERE slug='sample-13-the-thao';

UPDATE article_images ai JOIN articles a ON a.id=ai.article_id
SET ai.caption='Ảnh minh họa theo chủ đề bài viết; xem nguồn tham khảo trong nội dung.',
    ai.alt_text=CONCAT('Ảnh minh họa: ', a.title)
WHERE a.slug IN ('sample-01-thoi-su', 'sample-05-the-gioi', 'sample-07-tai-chinh', 'sample-09-cong-nghe', 'sample-21-giao-duc', 'sample-33-doi-song', 'sample-03-moi-truong', 'sample-13-the-thao');

COMMIT;
